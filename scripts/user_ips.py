#! /usr/bin/env python
# -*- coding: utf-8 -*-

import dbconf
from utils import DBM
import argparse
import ipaddr

def main():
	global configuration
	user = configuration.user

	cursor = DBM.cursor()

	seen = set()
	query = """select vote_ip_int, vote_date from users, votes where user_login=%s and vote_type in ('links', 'comments', 'posts') and vote_user_id=user_id order by vote_date desc"""

	cursor.execute(query, (user,))
	c = 0
	for ip_int, date in cursor:
		if ip_int not in seen and ip_int > 0:
			print("%s\t%s" % (ipaddr.IPAddress(long(ip_int)), date))
			seen.add(ip_int)
			c += 1
		if c > 20:
			break

if __name__ == "__main__":
	parser = argparse.ArgumentParser()
	parser.add_argument("user", help="Shows the user's IPs")
	configuration = parser.parse_args()
	main()



