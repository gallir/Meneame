#! /usr/bin/env python

# follow.py
#
# Follow a file like tail -f.

import time
import operator
import argparse
import sys
import os

import dbconf
from utils import *

import getpass
import smtplib
from email.mime.text import MIMEText


def follow(thefile):
	while True:
		line = thefile.readline()
		if not line:
			#time.sleep(0.00001)
			#continue
			yield None
		else:
			fields = line.split()
			if len(fields) >= 10 and fields[4] == "meneame_accesslog:":
				yield fields
			else:
				print >> sys.stderr, "BAD:", line


def openfile(filename):
	logfile = open(filename,"rU")
	logfile.seek(0,2)
	return logfile
	
def analyze(logfile):
	global configuration


	loglines = follow(logfile)
	total = counter = empties = 0

	ip_scripts = {}
	ip_users = {}
	ip_counter = {}
	ip_warned = set()

	for fields in loglines:
		if fields:
			empties = 0
			counter += 1
			total += 1

			log_ip = fields[5]
			log_user = fields[6]
			log_time = fields[7]
			log_server = fields[8]
			log_script = fields[9]

			if log_ip in ip_counter:
				ip_counter[log_ip] += 1
			else:
				ip_counter[log_ip] = 1
				ip_users[log_ip] = set()
			ip_users[log_ip].add(log_user)

			
		else:
			rate = counter/configuration.period
			if total > 0:
				print rate, "c/sec"
			if empties > 4:
				return total
			if counter == 0:
				empties += 1
			else:
				#sorted_ips = sorted(ip_counter.iteritems(), key=operator.itemgetter(1), reverse=True)
				sorted_ips = sorted(ip_counter.items(), key=lambda x:x[1], reverse=True)
				ip_exceeded = set()
				i = 0
				max_count = configuration.rate * configuration.period
				while sorted_ips[i][1] >= max_count:
					ip = sorted_ips[i][0]
					ip_exceeded.add(ip)
					i += 1
					
				if not configuration.q:
					print "Top IPs"
					print "    %5s %4s %s" % ("Conn.", "c/s", "IP")
					r = min(len(sorted_ips), 10)
					for i in range(r):
						ip, conns = sorted_ips[i]
						
						if ip in ip_exceeded: print "+",
						else: print " ",
						if ip in ip_warned: print "*",
						else: print " ",
						print "%5d %4d %s" % (conns, conns/configuration.period, ip)
					print

				if configuration.ban:
					for ip in ip_exceeded:
						if ip in ip_warned:
							rate = str(ip_counter[ip]/configuration.period)
							if len(ip_users[ip]) > 1 or "-" not in ip_users[ip]:
								seconds = 1800
							else:
								seconds = 86400
							reason = "Automatic (" + ','.join([x for x in ip_users[ip]]) + ") " + rate + " conns/second for " + str(seconds) + " seconds"
							ban_ip(ip, reason, seconds)

				ip_warned = ip_exceeded;

			
			ip_scripts = {}
			ip_users = {}
			ip_counter = {}
			counter = 0
			time.sleep(configuration.period)
	return total

def ban_ip(ip, reason, time):
	global configuration

	print "BAN:", ip, reason
	
	if configuration.mail:
		msg = MIMEText("BANNED IP: " + ip +"\nReason: " + reason)
		msg['Subject'] = "Automatic DoS ban"
		msg['From'] = getpass.getuser()
		msg['To'] = "gallir@gmail.com"
		s = smtplib.SMTP('localhost')
		s.sendmail(getpass.getuser(), "gallir@gmail.com", msg.as_string())
		s.quit()

	c = DBM.cursor('update')
	c.execute("REPLACE INTO bans (ban_type, ban_text, ban_comment, ban_expire) VALUES (%s, %s, %s, date_add(now(), interval %s second))", ("noaccess", ip, reason, time))
	c.close()
	DBM.commit()


if __name__ == '__main__':
	parser = argparse.ArgumentParser()
	parser.add_argument("--period", "-p", type=int, default=10, help="Seconds between analysis, default 10")
	parser.add_argument("--ban", "-b", action="store_true", help="Ban IPs")
	parser.add_argument("-q", action="store_true", help="Quiet mode")
	parser.add_argument("--rate", "-r", type=int, default=15, help="Set the max number of connections per second, default 15")
	parser.add_argument("--logfile", "-l", default="/var/log/meneame_access.log", help="Logfile pathname, default /var/log/meneame_access.log")
	parser.add_argument("--mail", "-m", help="Send email to this address when an IP is banned")
	configuration = parser.parse_args()

	if configuration.q:
		f = open(os.devnull, 'w')
		sys.stdout = f

	counter = 0
	while True:
		try:
			try:
				logfile = openfile(configuration.logfile)
			except (IOError), e:
				print >> sys.stderr, e
				exit(1)

			counter += 1
			lines = analyze(logfile)
			print "End", counter, lines
		except (KeyboardInterrupt), e:
			print
			exit(0)
	
