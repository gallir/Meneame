#! /usr/bin/env python

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

import subprocess

import time
import syslog

class MySysLogger():
	def __init__(self, seconds, quiet = False):
		self.seconds = seconds
		self.total = 0
		self.partial = 0
		self.time = self.start = time.time()
		self.quiet = quiet
		if not self.quiet:
			print "Starting syslogger"

	def run(self):
		now = time.time()
		elapsed = now - self.time
		if (elapsed < self.seconds):
			return

		self.time = time.time()
		line = "partial %0.1f conn/sec %d (%.2f seconds), total %0.1f conn/sec %ld" % (self.partial/elapsed, self.partial, elapsed, self.total/(now-self.start), self.total)
		self.partial = 0
		if not self.quiet:
			print line
		syslog.syslog(syslog.LOG_INFO, line)

	def increment(self):
		self.partial += 1
		self.total += 1


def openfile(filename):
	logfile = open(filename,"rU")
	logfile.seek(0,2)
	return logfile
	
def analyze(logfile):
	global configuration, syslogger


	loglines = follow_log(logfile, configuration.showbad)
	total = counter = empties = 0

	ip_scripts = {}
	ip_users = {}
	ip_counter = {}
	ip_warned = set()
	ip_banned = set()
	

	for log in loglines:
		if log:
			empties = 0
			counter += 1
			total += 1

			if syslogger:
				syslogger.increment()

			if log['ip'] in ip_counter:
				ip_counter[log['ip']] += 1
			else:
				ip_counter[log['ip']] = 1
				ip_users[log['ip']] = set()
			ip_users[log['ip']].add(log['user'])

			if log['_blocked']:
				ip_banned.add(log['ip'])

			
		else:
			ip_exceeded = set()
			banned = set()
			rate = counter/configuration.period
			if total > 0:
				print rate, "c/sec"
			if empties > 2:
				return total
			if counter == 0:
				empties += 1
			else:
				#sorted_ips = sorted(ip_counter.iteritems(), key=operator.itemgetter(1), reverse=True)
				sorted_ips = sorted(ip_counter.items(), key=lambda x:x[1], reverse=True)
				i = 0
				max_count = configuration.rate * configuration.period
				""" Give one aditional connection per second to different users """
				while sorted_ips[i][1] >= max_count + (len(ip_users[sorted_ips[i][0]])-1)*configuration.period:
					ip = sorted_ips[i][0]
					if ip not in ip_banned:
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
						print "%5d %4d %s" % (conns, conns/configuration.period, ip),
						print ','.join([x for x in sorted(ip_users[ip], key=str.lower)])
					print

				if configuration.ban:
					for ip in ip_exceeded:
						if ip in ip_warned and ip not in ip_banned:
							rate = str(ip_counter[ip]/configuration.period)

							if len(ip_users[ip]) > 1 or "-" not in ip_users[ip]:
								seconds = 3600
							else:
								seconds = 86400
							""" Increase de seconds according to how much it exceeded """
							seconds = int(seconds * ip_counter[ip]/float(max_count))

							reason = "Automatic (" + ','.join([x for x in sorted(ip_users[ip], key=str.lower)]) + ") " + rate + " conn/sec, banned for " + str(seconds) + " seconds"
							ban_ip(ip, reason, seconds)
							banned.add(ip)

			ip_warned = ip_exceeded;
			ip_banned = banned;

			
			ip_scripts = {}
			ip_users = {}
			ip_counter = {}
			counter = 0
			if syslogger:
				syslogger.run()
			time.sleep(configuration.period)
	return total

def ban_ip(ip, reason, time):
	global configuration

	
	if not configuration.dry:
		c = DBM.cursor('update')
		c.execute("REPLACE INTO bans (ban_type, ban_text, ban_comment, ban_expire) VALUES (%s, %s, %s, date_add(now(), interval %s second))", ("noaccess", ip, reason, time))
		c.close()
		DBM.commit()
		DBM.close()

	print "BAN:", ip, reason
	syslog.syslog(syslog.LOG_INFO, "Accesses ban: " + reason)

	if configuration.mail:
		""" Generate a report """
		try:
			p = subprocess.Popen(["summary_access.py", ip, "-M", "1"], stdout=subprocess.PIPE)
			(report, err) = p.communicate()
		except Exception as e:
			report = unicode(e)

		msg = MIMEText("BANNED IP: " + ip +"\nReason: " + reason + "\n\nSUMMARY REPORT LAST MINUTE:\n" + unicode(report))
		msg['Subject'] = "Automatic DoS ban"
		msg['From'] = getpass.getuser()
		msg['To'] = configuration.mail
		s = smtplib.SMTP('localhost')
		s.sendmail(getpass.getuser(), configuration.mail, msg.as_string())
		s.quit()



if __name__ == '__main__':
	parser = argparse.ArgumentParser()
	parser.add_argument("--period", "-p", type=int, default=10, help="Seconds between analysis, default 10")
	parser.add_argument("--ban", "-b", action="store_true", help="Ban IPs")
	parser.add_argument("-q", action="store_true", help="Quiet mode")
	parser.add_argument("--syslog", "-s", type=int, help="Write a summary every x seconds")
	parser.add_argument("--dry", "-d", action="store_true", help="Do not store the ban in the DB")
	parser.add_argument("--rate", "-r", type=int, default=15, help="Set the max number of connections per second, default 15")
	parser.add_argument("--logfile", "-l", default="/var/log/meneame_access.log", help="Logfile pathname, default /var/log/meneame_access.log")
	parser.add_argument("--mail", "-m", help="Send email to this address when an IP is banned")
	parser.add_argument("--showbad", action="store_true", help="Report bad format lines")
	configuration = parser.parse_args()

	if configuration.q:
		f = open(os.devnull, 'w')
		sys.stdout = f

	syslog.openlog("meneame", syslog.LOG_NDELAY, syslog.LOG_USER)
	counter = 0
	if configuration.syslog > 0:
		syslogger = MySysLogger(configuration.syslog, configuration.q)
	else:
		syslogger = None

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
	
