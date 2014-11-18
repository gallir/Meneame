#! /usr/bin/env python
# -*- coding: utf-8 -*-

import time
import operator
import argparse
import sys
import os

import dbconf
import utils
import re

def analize(what, data, logfile):
	global configuration

	regex = False

	""" It supports "*" as wildcard in data """
	if re.search(r'\*', data):
		data = re.escape(data)
		regex = re.sub(r'\\\*', r'.*', data)

	summary = {}
	total_lines = 0
	total = 0
	first_read = False

	for line in logfile:
		total_lines += 1
		log = utils.parse_logline(line)
		if not first_read and log:
			print "Reading from %s (UTC)..." % (log['_date'])
			sys.stdout.flush()
			first_read = True
		if not log or (not regex and log[what] != data) or (regex and not re.match(regex, log[what])):
			continue
		total += 1
		utils.add_log2dict(log, summary)

	print "TOTAL LINES:    %d" % (total_lines,)
	print "FILTERED LINES: %d (%.2f%%)" % (total, 100 * total/float(total_lines))

	if total == 0: return

	for k in [what] + [x for x in summary if x != what]:
		print "%ss (%d): " % (k.upper(),len(summary[k]))
		sorted_vals = sorted(summary[k].items(), key=lambda x:x[1], reverse=True)
		if configuration.maxitems > 0:
			sorted_vals = sorted_vals[:configuration.maxitems]
		for v in sorted_vals:
			print "%8d %6.2f%% %s" % (v[1], 100 * v[1]/float(total), v[0].decode('utf8', 'ignore')) 


if __name__ == '__main__':
	parser = argparse.ArgumentParser()
	parser.set_defaults(what="ip")
	parser.add_argument('data', help='Keyword to analyze (IP, username, etc.)')

	group = parser.add_mutually_exclusive_group()
	group.add_argument("-i", dest="what", action="store_const", const="ip", help="Show IP summary (default)")
	group.add_argument("-u", dest="what", action="store_const", const="user", help="Show user summary")
	group.add_argument("-s", dest="what", action="store_const", const="script", help="Show script summary [fullname required]")
	group.add_argument("-n", dest="what", action="store_const", const="server", help="Show server/hostname summary")
	group.add_argument("-w", dest="what", help="Show a given log category")

	group = parser.add_mutually_exclusive_group()
	group.add_argument("--hours", "-H", type=int, default=1, help="Hours to analyze since the current time")
	group.add_argument("--minutes", "-M", type=int, help="Minutes to analyze since the current time")
	group.add_argument("--megabytes", "-m", type=int, default=-1, help="The number of megabytes to analyze from the end, default 100, 0 for the whole file")


	parser.add_argument("--maxitems", "-x", type=int, default=20, help="Max number per each displayed item, default 20, 0 for all")
	parser.add_argument("--logfile", "-l", default="/var/log/meneame_access.log", help="Logfile pathname, default /var/log/meneame_access.log")
	configuration = parser.parse_args()

	try:
		logfile = open(configuration.logfile,"rU")

		if configuration.megabytes >= 0:
			if configuration.megabytes > 0:
				fsize = os.path.getsize(configuration.logfile);
				nbytes = configuration.megabytes * 1024 * 1024
				if fsize > nbytes:
					logfile.seek(-nbytes, 2)
					logfile.readline() # Clean the first line
		elif configuration.minutes > 0:
			utils.time_position_log(logfile, configuration.minutes)
		elif configuration.hours > 0:
			utils.time_position_log(logfile, configuration.hours*60)
 	except (IOError), e:
		print >> sys.stderr, e
		exit(1)

	analize(configuration.what, configuration.data, logfile)
	exit(0)


