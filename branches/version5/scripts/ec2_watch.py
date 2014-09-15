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

from ec2_watchdata import WatchData


def main():
	global configuration

	now = int(time.time())
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
	if configuration.history:
		WatchData.history_size = configuration.history



	data.connect(configuration.group)
	data.get_CPU_loads()

	prev_data = WatchData.from_file()

	""" Retrieve and calculate previous values in the current instance """
	data.action_ts = int(prev_data.action_ts)
	data.action = prev_data.action
	data.up_ts = int(prev_data.up_ts)
	data.down_ts = int(prev_data.down_ts)
	data.history = prev_data.history

	""" Calculate the trend, increasing or decreasing CPU usage """
	alpha = min((data.ts - prev_data.ts) / 60.0 * 0.3, 1)
	data.exponential_average = alpha * data.avg_load + (1 - alpha) * prev_data.exponential_average
	data.trend =  2 * data.exponential_average - prev_data.exponential_average
	print prev_data.exponential_average,  data.exponential_average, data.trend



	if data.instances != prev_data.instances:
		data.previous_instances = prev_data.instances
		if data.instances > prev_data.instances: data.up_ts = int(time.time())
		else: data.down_ts = int(time.time())
	else:
		data.previous_instances = prev_data.previous_instances
		

	if data.instances != prev_data.instances or data.desired != prev_data.desired:
		data.changed_ts = int(time.time())
	else:
		data.changed_ts = int(prev_data.changed_ts)



	print "%s values: instances: %d min: %d max: %d desired: %d" % (configuration.group, data.instances, data.group.min_size, data.group.max_size, data.group.desired_capacity)
	print "Average load: %5.2f%% Trend: %5.2f" % (data.avg_load,data.trend)
	if data.instances > 1:
		print "Average load with %d instances: %5.2f%%" % (data.instances-1, data.total_load/(data.instances-1))

	print "Last change: %s last action: %s (%s)" % (time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(data.changed_ts)), time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(data.action_ts)), data.action)
	print "Last up: %s last down: %s" % (time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(data.up_ts)), time.strftime('%Y-%m-%d %H:%M:%S', time.localtime(data.down_ts)))


	if now - data.changed_ts > 600 and now - data.action_ts > 600:
		if not data.check_too_low():
			data.check_too_high()

	if now - data.changed_ts > 300 and now - data.action_ts > 300:
		data.check_avg_high()

	if now - data.changed_ts > 300 and now - data.action_ts > 300 and now - data.up_ts > 1800:
			data.check_avg_low()

	data.store(configuration.annotation)

	if configuration.mail and data.emergency:
		sendmail(data, configuration.mail)


def sendmail(data, to):
		print "Sending email to", to

		""" Generate a report """
		try:
			p = subprocess.Popen([os.path.join(os.path.dirname(os.path.realpath(__file__)),"ec2_instances.py")], stdout=subprocess.PIPE)
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
	parser.add_argument("--annotation", "-a", action="store_true", help="Store data in Meneame database as annotation")
	parser.add_argument("--history", "-H", type=int, default=1500, help="History size of CPU load")
	parser.add_argument("--mail", "-m", help="Send email to this address when took an emergency action")
	parser.add_argument("--dry", "-d", action="store_true", help="Do not take actions")
	parser.add_argument("--low", "-low", type=int, default=70, help="Low limit for CPU average")
	parser.add_argument("--high", "-high", type=int, default=85, help="High limit for CPU average")
	parser.add_argument("--high_urgent", "-u", type=int, default=95, help="Kill overloaded instance, or increase instances at this individual CPU load")
	configuration = parser.parse_args()
	main()
