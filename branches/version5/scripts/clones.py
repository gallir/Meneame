#! /usr/bin/env python
# -*- coding: utf-8 -*-


import dbconf
from utils import DBM
import argparse
import ipaddr
from decimal import *


def main():
	global configurations
	activity = {}
	seen_ips = {}

	# Delete old entries
	update_cursor = DBM.cursor('update')
	query = """ DELETE FROM clones WHERE clon_date < date_sub(now(), interval 120 day) """
	update_cursor.execute(query)
	DBM.commit()


	if configuration.hours:
		minutes = configuration.hours * 60
	elif configuration.minutes:
		minutes = configuration.minutes

	print "Analyzing IPs for %d minutes" % minutes
	cursor = DBM.cursor()
	
	queries = (
		"""select distinct vote_user_id, vote_ip_int from votes where vote_type in ('links', 'comments', 'posts') and vote_user_id != 0 and vote_date > date_sub(now(), interval %s minute)""", 
		"""select distinct comment_user_id, comment_ip_int from comments where comment_date > date_sub(now(), interval %s minute)"""
	)
		
	for query in queries:
		cursor.execute(query, (minutes,))
		for uid, ip_int in cursor:
			ip = IPAddress(ip_int)
			add_user_ip(uid, ip, activity)
			#print uid, ip_int, ip

	search_from = int(30*24 + (minutes*60));
	print "Analyzing history for %d hours" % search_from

	clones = set()
	ips_counter = {}
	for u, ips in activity.iteritems():
		# To avoid warning of truncated DOUBLE, the list of decimals is passed directly to the mysql driver
		format_strings = ','.join(['%s'] * len(ips))
		query = """select distinct vote_user_id, vote_ip_int from votes where vote_ip_int in (%s) """ % format_strings
		query += """and vote_user_id != %d and vote_user_id > 0 and vote_date > date_sub(now(), interval %d hour)""" % (u, search_from)
		cursor.execute(query, tuple(ips))

		for clon, ip_int in cursor:
			ip = IPAddress(ip_int)
			# print u, clon, ip
			clones.add((u, clon, str(ip)))

			if str(ip) not in ips_counter:
				ips_counter[str(ip)] = 1
			else:
				ips_counter[str(ip)] += 1

	#print clones, ips_counter

	c = 0
	for u, clon, ip in clones:
		if ips_counter[ip] < 20:
			print "Clon:", u, clon, ip, ips_counter[ip]
			insert = """REPLACE INTO clones (clon_from, clon_to, clon_ip) VALUES (%s, %s, %s)"""
			update_cursor.execute(insert, (u, clon, ip))
			insert = """INSERT IGNORE INTO clones (clon_to, clon_from, clon_ip) VALUES (%s, %s,	%s)"""
			update_cursor.execute(insert, (u, clon, ip))
			c += 1
			if c % 10 == 0:
				DBM.commit()
	DBM.commit()
	


def IPAddress(ip_int):
	try:
		return ipaddr.IPAddress(long(ip_int))
	except:
		return False

def add_user_ip(user, ip, dictionary):
	if not ip or ip.is_private:
		return

	if user not in dictionary:
		dictionary[user] = set()
	if int(ip) not in dictionary[user]:
		dictionary[user].add(int(ip))


if __name__ == "__main__":
	parser = argparse.ArgumentParser()
	parser.add_argument("--minutes", "-m", type=int, default=15, help="Minutes to analyze")
	parser.add_argument("--hours", "-H", type=int, help="Hours to analyze")
	configuration = parser.parse_args()
	main()


