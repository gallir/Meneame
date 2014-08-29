#! /usr/bin/env python
# -*- coding: utf-8 -*-

import dbconf
from utils import DBM
import argparse

def main():
	global configuration
	ip = configuration.IP

	cursor = DBM.cursor()

	query = """ SELECT distinct user_login, user_email, user_level, clon_ip FROM users, clones WHERE (clon_ip LIKE %s OR clon_ip LIKE %s) AND (clon_from = user_id OR clon_to = user_id)"""

	cursor.execute(query, ("%s%%" % ip, "COOK:%s%%" % ip))
	for user, email, level, ip in cursor:
		print("%-16s\t%s\t%s\t%s" % (user, email, ip, level))

if __name__ == "__main__":
	parser = argparse.ArgumentParser()
	parser.add_argument("IP", help="Shows the clones with the same IP or sub IP")
	configuration = parser.parse_args()
	main()



