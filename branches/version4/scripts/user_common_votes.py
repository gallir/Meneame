#! /usr/bin/env python
from __future__ import division

import MySQLdb
import sys
import time
import datetime
import gettext
_ = gettext.gettext
import dbconf
from utils import *


def main():
	links = {}
	commons = {}
	now = datetime.datetime.fromtimestamp(0)

	

	if len(sys.argv) < 2:
		print "Usage: %s hours [top_limit_in_days]" % (sys.argv[0],)
		exit()

	if len(sys.argv) > 2:
		time_to = int(sys.argv[2]) * 24
	else:
		time_to = 0
	time_from = int(sys.argv[1]) + time_to

	cursor = DBM.cursor()
	cursor.execute("select link_id, link_date, link_status, vote_user_id, vote_date, vote_value FROM votes, links WHERE vote_type = 'links' and vote_user_id > 0 and vote_date > date_sub(now(), interval %s hour) and vote_date < date_sub(now(), interval %s hour) and link_id = vote_link_id" % (time_from, time_to))

	for row in cursor:
		if row[4] > now:
			now = row[4]
		if row[2] == 'published' and row[4] > row[1]:
			continue
		
		if row[0] not in links:
			links[row[0]] = set()

		e = int(row[3] * (row[5] / abs(row[5])))
		links[row[0]].add(e)


	for l in links:
		for x in links[l]:
			for y in links[l]:
				ax = abs(x)
				ay = abs(y)
				if ax < ay:
					if ax not in commons: commons[ax] = {}
					if ay not in commons[ax]: commons[ax][ay] = 0
					if x*y > 0: 
						commons[ax][ay] += 1
					else:
						commons[ax][ay] -= 1

	c = DBM.cursor("update")
	c.execute("delete from users_similarities where date < date_sub(now(), interval 30 day)")
	DBM.commit()


	alpha = 0.9**(int(sys.argv[1])/24)
	print "Alpha: ", alpha, "Rows: ", len(commons)
	ops = 0
	#now = str(now)
	for k in sorted(commons):
		for l in sorted(commons[k]):
			ops += 1
			c.execute("INSERT INTO users_similarities (minor, major, value, date) VALUES (%s, %s, %s, '%s') ON DUPLICATE KEY UPDATE value = %s * value + (1-%s) * %s, date = '%s'" % (k, l, commons[k][l], now, alpha, alpha, commons[k][l], now))
			#print k, l, distances[k][l]
			if ops % 1000 == 0:
				DBM.commit()
				time.sleep(0.05)
	c.close()
	DBM.commit()
	


if __name__ == "__main__":
	main()
