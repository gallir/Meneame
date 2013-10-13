#!/usr/bin/python

import argparse
import sys
import time
import datetime
import getpass
import smtplib
from email.mime.text import MIMEText
import subprocess
import os

from watchdata import WatchData


def main():
	global configuration

	now = time.time()
	data = WatchData()

	""" Set default class values """
	if configuration.dry:
		WatchData.dry = True
	if configuration.low:
		 WatchData.low_limit = configuration.low
	if configuration.high:
		WatchData.high_limit = configuration.high
	if configuration.high_urgent:
		WatchData.high_urgent = configuration.high_urgent



	data.connect(configuration.group)
	data.get_CPU_loads()

	prev_data = WatchData.from_file()

	""" Retrieve and calculate previous values in the current instance """
	data.action_ts = prev_data.action_ts
	data.action = prev_data.action
	data.previous_instances = prev_data.instances
	if now - prev_data.changed_ts > 90 and (data.instances != data.previous_instances or data.desired != prev_data.desired):
		data.changed_ts = time.time()
	else:
		data.changed_ts = prev_data.changed_ts


	print data.__dict__

	if now - data.changed_ts > 600 and now - data.action_ts > 600:
		if not data.check_too_low():
			data.check_too_high()

	if	now - data.changed_ts > 300 and now - data.action_ts > 300:
		data.check_avg_high()

	if	now - data.changed_ts > 300 and now - data.action_ts > 300:
		data.check_avg_low()

	data.store(configuration.db)

	if configuration.mail and data.emergency:
		sendmail(data, configuration.mail)


def sendmail(data, to):
		print "Sending email to", to

		""" Generate a report """
		try:
			p = subprocess.Popen([os.path.join(os.path.dirname(os.path.realpath(__file__)),"instances.py")], stdout=subprocess.PIPE)
			(report, err) = p.communicate()
		except Exception as e:
			report = unicode(e)

		msg = MIMEText("Action: " + data.action + "\n\nINSTANCES SUMMARY:\n" + unicode(report))
		msg['Subject'] = "Watch warning"
		msg['From'] = getpass.getuser()
		msg['To'] = configuration.mail
		s = smtplib.SMTP('localhost')
		s.sendmail(getpass.getuser(), configuration.mail, msg.as_string())
		s.quit()



if __name__ == '__main__':
	parser = argparse.ArgumentParser()
	parser.add_argument("--group", "-g", default="web", help="AutoScaler group")
	parser.add_argument("--db", "-db", action="store_true", help="Store data in Meneame database (as annotation)")
	parser.add_argument("--mail", "-m", help="Send email to this address when took an emergency action")
	parser.add_argument("--dry", "-d", action="store_true", help="Do not take actions")
	parser.add_argument("--low", "-low", type=int, default=70, help="Low limit for CPU average")
	parser.add_argument("--high", "-high", type=int, default=85, help="High limit for CPU average")
	parser.add_argument("--high_urgent", "-u", type=int, default=95, help="Kill overloaded instance, or increase instances at this individual CPU load")
	configuration = parser.parse_args()
	main()
