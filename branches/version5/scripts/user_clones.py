#! /usr/bin/env python
# -*- coding: utf-8 -*-

import dbconf
from utils import DBM
import argparse

def main():
	global configuration
	user = configuration.user

	cursor = DBM.cursor()

	query = """select distinct clon.user_login, clon.user_level, clon_ip, clon_date from users, users as clon, clones where users.user_login = %s and clon_from = users.user_id and clon_to = clon.user_id and clon_date > date_sub(now(), interval 60 day)"""

	cursor.execute(query, (user,))
	for clon, level, ip, date in cursor:
		print("%-16s\t%s\t%s\t%s" % (clon, ip, level, date))

if __name__ == "__main__":
	parser = argparse.ArgumentParser()
	parser.add_argument("user", help="Shows the user's clones")
	configuration = parser.parse_args()
	main()



