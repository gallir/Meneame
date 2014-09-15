#!/usr/bin/python

import sys
import time
import datetime
import pickle
import json
import syslog

import boto
from boto.ec2.autoscale import AutoScaleConnection
from boto.ec2.cloudwatch import CloudWatchConnection


class WatchData:
	datafile = "/var/tmp/watchdata.p"
	dry = False
	low_limit = 72
	high_limit = 90
	high_urgent = 95
	stats_period = 60
	history_size = 0

	def __init__(self):
		self.name = ''
		self.instances = 0
		self.new_desired = 0
		self.desired = 0
		self.instances_info = None
		self.previous_instances = 0
		self.action = ""
		self.action_ts = 0
		self.changed_ts = 0
		self.total_load = 0
		self.avg_load = 0
		self.max_load = 0
		self.up_ts = 0
		self.down_ts= 0
		self.max_loaded = None
		self.loads = {}
		self.measures = {}
		self.emergency = False
		self.history = None
		self.trend = 0
		self.exponential_average = 0
		self.ts = 0

	def __getstate__(self):
		""" Don't store these objets """
		d = self.__dict__.copy()
		del d['ec2']
		del d['cw']
		del d['autoscale']
		del d['group']
		del d['instances_info']
		return d

	def connect(self, groupname):
		self.ec2 = boto.connect_ec2()
		self.cw = CloudWatchConnection()
		self.autoscale = AutoScaleConnection()
		self.group = self.autoscale.get_all_groups(names=[groupname])[0]
		self.instances = len(self.group.instances)
		self.desired = self.group.desired_capacity
		self.name = groupname
		self.ts = int(time.time())

	def get_instances_info(self):
		ids = [i.instance_id for i in self.group.instances]
		self.instances_info = self.ec2.get_only_instances(instance_ids = ids)
	
	def get_CPU_loads(self):
		""" Read instances load and store in data """
		measures = 0
		for instance in self.group.instances:
			load = self.get_instance_CPU_load(instance.instance_id)
			if load is None:
				continue
			measures += 1
			self.total_load += load
			self.loads[instance.instance_id] = load
			if load > self.max_load:
				self.max_load = load
				self.max_loaded = instance.instance_id

		if measures > 0:
			self.avg_load = self.total_load/measures

	def get_instance_CPU_load(self, instance):
		end = datetime.datetime.now()
		start = end - datetime.timedelta(seconds=int(self.stats_period*3))

		m = self.cw.get_metric_statistics(self.stats_period, start, end, "CPUUtilization", "AWS/EC2", ["Average"], {"InstanceId": instance})
		if len(m) > 0:
			measures = self.measures[instance] = len(m)
			ordered = sorted(m, key=lambda x: x['Timestamp'])
			averages = [ x['Average'] for x in ordered]
			average = reduce(lambda x, y: 0.4*x + 0.6*y, averages[-2:])
			return average

		return None

	@classmethod
	def from_file(cls):
		try:
  			data = pickle.load( open(cls.datafile, "rb" ))
		except:
			data = WatchData()

		return data

	def store(self, annotation = False):
		if self.history_size > 0:
			if not self.history: self.history = []
			self.history.append([int(time.time()), len(self.group.instances), int(round(self.total_load)), int(round(self.avg_load))])
			self.history = self.history[-self.history_size:]

		pickle.dump(self, open(self.datafile, "wb" ))

		if annotation:
			import utils
			text = json.dumps(self.__getstate__(), skipkeys=True)
			utils.store_annotation("ec2_watch", text)

	def check_too_low(self):
		for instance, load in self.loads.iteritems():
			if load is not None and self.measures[instance] > 1 and self.instances > 1 and load < self.avg_load * 0.2 and load < 4:
				self.emergency = True
				self.check_avg_low() # Check if the desired instanes can be decreased
				self.action = "EMERGENCY LOW (%s %5.2f%%) " % (instance, load)
				self.kill_instance(instance)
				return True
		return self.emergency

	def check_too_high(self):
		for instance, load in self.loads.iteritems():
			if load is not None and self.measures[instance] > 1 and load > self.high_urgent:
				self.emergency = True
				self.action = "EMERGENCY HIGH (%s %5.2f%%) " % (instance, load)
				if self.instances > 1 and load > self.avg_load * 1.5:
					self.action += " killing bad instance"
					self.kill_instance(instance)
				else:
					self.action += " increasing instances to %d" % (self.instances+1,)
					self.set_desired(self.instances+1)
				return True

		return self.emergency

	def check_avg_high(self):
		threshold = self.high_limit
		if self.instances == 1:
			threshold = threshold * 0.9 # Increase faster if there is just one instance
		
		if self.avg_load > threshold:
			self.action = "WARN, high load: %d -> %d " % (self.instances, self.instances + 1)
			self.set_desired(self.instances + 1)
			return True

	def check_avg_low(self):
		if self.instances <= self.group.min_size:
			return False
		
		if self.total_load/(self.instances-1) < self.low_limit:
			self.action = "low load: %d -> %d " % (self.instances, self.instances - 1)
			self.set_desired(self.instances - 1)

	def kill_instance(self, id):
		if self.action:
			print(self.action)
		print("Kill instance", id)
		syslog.syslog(syslog.LOG_INFO, "ec2_watch kill_instance: %s instances: %d (%s)" % (id, self.instances, self.action))
		if self.dry:
			return
		self.ec2.terminate_instances(instance_ids=[id])
		self.action_ts = time.time()

	def set_desired(self, desired):
		if self.action:
			print(self.action)
		print("Setting instances from %d to %d" % (self.instances, desired))
		syslog.syslog(syslog.LOG_INFO, "ec2_watch set_desired: %d -> %d (%s)" % (self.instances, desired, self.action))
		if self.dry:
			return
		if desired >= self.group.min_size:
			self.group.set_capacity(desired)
		self.action_ts = time.time()
		self.new_desired = desired
		

