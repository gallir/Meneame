#! /usr/bin/env python
# -*- coding: utf-8 -*-

import time
import operator
import argparse
import sys
import os
import pickle
import json

import dbconf
from utils import *
import ipaddr

import getpass
import smtplib
from email.mime.text import MIMEText

import subprocess

import time
import syslog

import codecs

class MySysLogger():
	def __init__(self, seconds, annotation, quiet = False):
		self.seconds = seconds
		self.annotation = annotation
		self.total = 0
		self.partial = 0
		self.time = self.start = time.time()
		self.quiet = quiet
		print_message("Starting syslogger")

	def run(self):
		now = time.time()
		elapsed = now - self.time
		if (elapsed < self.seconds):
			return

		self.time = time.time()
		if self.seconds > 0:
			line = "partial %0.1f conn/sec %d (%.2f seconds), total %0.1f conn/sec %ld" % (self.partial/elapsed, self.partial, elapsed, self.total/(now-self.start), self.total)
			print_message(line)

		if self.annotation > 0:
			try:
				history = pickle.load(open("/var/tmp/access_history.p", "rb" ))
			except:
				 history = []
			history.append([int(now), int(self.partial/elapsed)])
			history = history[-self.annotation:]

			pickle.dump(history, open("/var/tmp/access_history.p", "wb" ))

			current = json.dumps(history)
			store_annotation('access_history', current)
				

		self.partial = 0

	def increment(self):
		self.partial += 1
		self.total += 1


def openfile(filename, reopen = False):
	logfile = codecs.open(filename,"rU", "utf-8")
	if reopen:
		""" Begining of file """
		logfile.seek(0,0)
	else:
		""" End of file """
		logfile.seek(0,2)
	return logfile
	
def analyze(logfile):
	global configuration, syslogger


	loglines = follow_log(logfile, configuration.showbad)
	total = counter = empties = 0

	""" Number of previous periods to store """
	low_history = 4 

	ip_scripts = {}
	ip_users = {}
	ip_counter = {}
	ip_warned = set()
	ip_banned = set()
	ip_periods = []
	

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
			ip_low_exceeded = set()
			ip_high_exceeded = set()
			to_ban = set()
			rate = counter/configuration.period
			ip_periods_seen = {}

			if not configuration.q and total > 0:
				print rate, "c/sec"
			if empties > 1:
				return total
			if counter == 0:
				empties += 1
			else:
				#sorted_ips = sorted(ip_counter.iteritems(), key=operator.itemgetter(1), reverse=True)
				sorted_ips = sorted(ip_counter.items(), key=lambda x:x[1], reverse=True)
				i = 0
				max_len = len(sorted_ips)
				max_count = configuration.rate * configuration.period
				low_count = max_count * 0.6
				high_count = max_count * 2
				""" Give one aditional connection per second to different users """
				while i < max_len and sorted_ips[i][1] >= low_count + (len(ip_users[sorted_ips[i][0]])-1)*configuration.period:
					ip = sorted_ips[i][0]

					""" Never block private IPs """
					ip_addr = ipaddr.IPNetwork(ip)
					if ip_addr.is_private or ip_addr.is_loopback or ip_addr.is_link_local:
						i += 1
						continue


					count = sorted_ips[i][1]
					additional = (len(ip_users[sorted_ips[i][0]])-1)*configuration.period
					if ip not in ip_banned:
						ip_low_exceeded.add(ip)
						if count > max_count + additional:
							ip_exceeded.add(ip)
						if count > high_count + additional:
							ip_high_exceeded.add(ip)
						
					i += 1
					
				if not configuration.q:
					print "Top IPs"
					print "    %5s %4s %s" % ("Conn.", "c/s", "IP")
					r = min(len(sorted_ips), 10)
					for i in range(r):
						ip, conns = sorted_ips[i]
						
						if ip in ip_high_exceeded: print "H",
						elif ip in ip_exceeded: print "+",
						elif ip in ip_low_exceeded: print "-",
						else: print " ",

						if ip in ip_warned: print "*",
						else: print " ",

						print "%5d %4d %s" % (conns, conns/configuration.period, ip),
						print ','.join([x for x in sorted(ip_users[ip], key=lambda user:user.lower())])
					print

				if configuration.ban:
					to_ban = ip_high_exceeded
					for ip in ip_high_exceeded:
						ip_periods_seen[ip] = 1

					for ip in [x for x in ip_exceeded if x in ip_warned and x not in ip_banned]:
						to_ban.add(ip)
						ip_periods_seen[ip] = 2

					if ip_periods:
						low_intersection = set.intersection(*ip_periods)
						for ip in [x for x in ip_low_exceeded if x in low_intersection and x not in ip_banned]:
							to_ban.add(ip)
							ip_periods_seen[ip] = low_history + 1
				
					for ip in to_ban.copy():
						rate = ip_counter[ip]/configuration.period

						if len(ip_users[ip]) > 1 or "-" not in ip_users[ip]:
							seconds = 3600 * 2
						else:
							seconds = 86400 * 2
						""" Increase de seconds according to how much it exceeded """
						seconds = int(seconds * ip_counter[ip]/float(max_count))

						users = ','.join([x for x in sorted(ip_users[ip], key=lambda user:user.lower())])
						reason = "Automatic (%s) %d conn/sec during %d seconds, blocked for %02d:%02d:%02d hs" % \
								(users, rate, ip_periods_seen[ip]*configuration.period, seconds/3600, (seconds%3600)/60, seconds%60)
						# reason = "Automatic (" + ','.join([x for x in sorted(ip_users[ip], key=str.lower)]) + ") " + rate + " conn/sec during " + str(ip_periods_seen[ip]*configuration.period) + " seconds, blocked for " + str(seconds/3600) + ":" + str((seconds%3600)/60) + str(seconds%60)
						if not ban_ip(ip, reason, seconds):
							to_ban.remove(ip) # Don't consider it as banned

			ip_warned = ip_exceeded;
			ip_banned = to_ban;

			ip_periods.append(ip_low_exceeded)
			if len(ip_periods) > low_history:
				del(ip_periods[0])

			
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
		try:
			c = DBM.cursor('update')
			c.execute("select ban_text from bans where ban_type = %s AND ban_text = %s AND (ban_expire IS null OR ban_expire > now())", ('noaccess', ip))
			exists = c.rowcount

			if not exists:
				c.execute("REPLACE INTO bans (ban_type, ban_text, ban_comment, ban_expire) VALUES (%s, %s, %s, date_add(now(), interval %s second))", ("noaccess", ip, reason, time))

			c.close()
			DBM.commit()
			DBM.close('update')

			if exists: 
				return True

		except Exception as e:
			DBM.close('update')
			print_message("Error in DB blocking IP: " + ip + " " + unicode(e))
			return False
			

	print_message("Block IP: " + ip + " " + reason)

	if configuration.mail:
		""" Generate a report """
		try:
			summary = os.path.dirname(os.path.abspath(__file__)) + "/" + "summary_access.py"
			p = subprocess.Popen([summary, ip, "-M", "1"], stdout=subprocess.PIPE)
			(report, err) = p.communicate()
		except Exception as e:
			report = unicode(e)

		try:
			msg = "BANNED IP: " + ip +"\nReason: " + reason + "\n\nSUMMARY REPORT LAST MINUTE:\n" + unicode(report)
			msg = MIMEText(msg.encode('utf-8'), 'plain', 'utf-8')
			msg['Subject'] = "Automatic DoS ban"
			msg['From'] = getpass.getuser()
			msg['To'] = configuration.mail
			s = smtplib.SMTP('localhost')
			s.sendmail(getpass.getuser(), configuration.mail, msg.as_string())
			s.quit()
		except Exception as e:
			print_message("Error sending email:  (" + unicode(e) + ")")

	return True

def print_message(mess):
	global configuration

	if not configuration.q:
		print mess
	else:
		syslog.syslog(syslog.LOG_INFO, mess)


if __name__ == '__main__':
	parser = argparse.ArgumentParser()
	parser.add_argument("--period", "-p", type=int, default=10, help="Seconds between analysis, default 10")
	parser.add_argument("--ban", "-b", action="store_true", help="Ban IPs")
	parser.add_argument("-q", action="store_true", help="Quiet mode")
	parser.add_argument("--syslog", "-s", type=int, help="Write a summary every x seconds")
	parser.add_argument("--annotation", "-A", type=int, help="If syslog enabled, store in meneame.annotation the last X values")
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
		syslogger = MySysLogger(configuration.syslog, configuration.annotation, configuration.q)
	else:
		syslogger = None

	restart = from_begining = False
	fails = 0
	while True:
		try:
			try:
				""" If the file is large start from the end """
				statinfo = os.stat(configuration.logfile)
				if restart and statinfo.st_size < 100000:
					from_begining = True
				else:
					from_begining = False

				logfile = openfile(configuration.logfile, from_begining)
				fails = 0
			except (IOError), e:
				fails += 1
				print_message("check_access IOError (%d): %s" % (fails, unicode(e)))
				if fails > 10:
					print_message("check_access exiting")
					exit(1)

				time.sleep(5)
				continue

			counter += 1
			lines = analyze(logfile)
			restart = True
			mess = "check_access, end: %d, %d, restarting in 5 seconds" % (counter, lines)
			print_message(mess)
			time.sleep(5)
		except (KeyboardInterrupt), e:
			print_message("Interrupted")
			exit(0)
	
