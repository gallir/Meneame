#! /usr/bin/env python
from __future__ import division

import MySQLdb
import sys
import time
import datetime
import gettext
_ = gettext.gettext
import argparse

import dbconf
from utils import *


def main():
	links = {}
	status = {}
	commons = {}
	queued_avoided = set()
	ages = {}

	parser = argparse.ArgumentParser(description='Store the number of common votes among users')
	parser.add_argument("hours", help="Hours to analyze", type=int)	
	parser.add_argument("-d", "--days", help="How many day in the past show be the top_date", type=int, default=0)	
	parser.add_argument("-m", "--max", help="store only if the new value is greater", action="store_true")

	args = parser.parse_args()

	now = datetime.datetime.fromtimestamp(0)

	time_to = args.days * 24
	time_from = args.hours + time_to


	cursor = DBM.cursor()
	cursor.execute("select link_id, link_date, link_status, vote_user_id, vote_date, vote_value, UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(link_date)  FROM votes, links WHERE vote_type = 'links' and vote_user_id > 0 and vote_date > date_sub(now(), interval %s hour) and vote_date < date_sub(now(), interval %s hour) and link_id = vote_link_id" % (time_from, time_to))

	for row in cursor:
		if row[4] > now:
			now = row[4]
		if row[2] == 'published' and row[4] > row[1]:
			continue
		
		if row[0] not in links:
			links[row[0]] = set()
			if row[0] not in status:
				status[row[0]] = row[2]
				ages[row[0]] = row[6]

		e = int(row[3] * (row[5] / abs(row[5])))
		links[row[0]].add(e)


	sorted_links = sorted(links, reverse=True)

	for l in sorted_links:
		for x in links[l]:
			for y in links[l]:
				ax = abs(x)
				ay = abs(y)
				if ax < ay:

					if status[l] != "published" and ages[l] < 3600*12:
						""" Avoid counting one vote of new queued links """
						key = "%d:%d" % (ax, ay)
						if key not in queued_avoided:
							queued_avoided.add(key)
							continue

					if ax not in commons: commons[ax] = {}
					if ay not in commons[ax]: commons[ax][ay] = 0
					if x*y > 0:
						commons[ax][ay] += 1
					else:
						commons[ax][ay] -= 1

	c = DBM.cursor("update")
	c.execute("delete from users_similarities where date < date_sub(now(), interval 60 day)")
	DBM.commit()

	alpha = 0.9**(int(sys.argv[1])/24)
	print "Alpha: ", alpha, "Rows: ", len(commons)
	ops = 0
	#now = str(now)
	for k in sorted(commons):
		for l in sorted(commons[k]):

			if commons[k][l] == 0: continue

			ops += 1
			sql = "INSERT INTO users_similarities (minor, major, value, date) VALUES (%s, %s, %s, '%s') ON DUPLICATE KEY UPDATE value = GREATEST(%s, %s * value * (1 - LEAST(1, timestampdiff(day, date, '%s')/60) ) + (1-%s) * %s), date = '%s'" % (k, l, commons[k][l], now, commons[k][l], alpha, now, alpha, commons[k][l], now)
			#print sql
			c.execute(sql)
			#print k, l, distances[k][l]
			if ops % 100 == 0:
				DBM.commit()
				print "Inserted ", ops, " rows"
				time.sleep(0.02)
	DBM.commit()
	c.close()
	print "END: ", ops
	


if __name__ == "__main__":
	main()
