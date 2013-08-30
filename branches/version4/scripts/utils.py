import sys
import socket
import urllib2
import httplib
from BeautifulSoup import BeautifulSoup,  SoupStrainer
import re
import MySQLdb
import _mysql_exceptions
import dbconf
import feedparser
import time
from urlparse import urlparse
import datetime

re_link = re.compile(r'<link ([^>]+(?:text\/xml|application\/atom\+xml|application\/rss\+xml)[^>]+[^>]+)/*>',re.I)
re_href = re.compile(r'''href=['"]*([^"']+)["']''', re.I)

def clean_url(string):
	string = re.sub(r'&amp;', '&', string)
	string = re.sub(r'[<>\r\n\t]|utm_\w+?=[^&]*', '', string) #  Delete common variables  for Analitycs and illegal chars
	string = re.sub(r'&{2,}', '&', string) # Delete duplicates &
	string = re.sub(r'&+$', '', string) # Delete useless & at the end
	string = re.sub(r'\?&+', '?', string) # Delete useless & after ?
	string = re.sub(r'\?&*$', '', string) # Delete empty queries
	string = re.sub(r'&', '&amp;', string)
	return string

def follow_log(thefile, show_bad=False):
	prev = ""
	while True:
		line = thefile.readline()
		if not line:
			#time.sleep(0.00001)
			#continue
			yield None
		else:
			log = parse_logline(line)
			if log:
				yield log
			else:
				if show_bad:
					print >> sys.stderr, "BAD:", line

def parse_logline(line):
	""" This works with the following rsyslog format template 
	$template ReducedLog,"%timereported%%msg%\n"
	and used as:
	if $programname == 'meneame_accesslog' then /mnt/meneame_access.log;ReducedLog
	& ~
	"""

	fields = line.split()
	if len(fields) == 8:
		log = dict()
		log['_date'] = fields[0] + " " + fields[1] + " " + fields[2]
		log['ip'] = fields[3]
		log['user'] = fields[4]
		log['time'] = float(fields[5])
		log['server'] = fields[6]
		log['script'] = fields[7]
		return log
	else:
		return None

def add_log2dict(log, d):
	for k in [x for x in log if x != 'time' and x[0] != "_"]:
		if k not in d:
			d[k] = {}
		if log[k] not in d[k]:
			d[k][log[k]] = 1
		else:
			d[k][log[k]] += 1

def time_position_log(logfile, minutes):
	now = datetime.datetime.now()
	goal = now - datetime.timedelta(minutes=minutes)

	base = 0
	logfile.seek(0, 2)
	top = logfile.tell()
	while top - base > 1000:
		pos = base + (top - base) / 2
		logfile.seek(pos, 0)
		logfile.readline() #Clean first line
		line = logfile.readline()
		if not line: 
			top = pos
			break
		log = parse_logline(line)
		log_date = datetime.datetime.strptime(log["_date"], "%b %d %H:%M:%S")
		if log_date.year < 2000:
			if log_date.month <= now.month:
				log_date = log_date.replace(year=now.year)
			else:
				log_date = log_date.replace(year=now.year-1)

		if log_date < goal:
			base = pos
		else:
			top = pos
	return
		
		
	
			
		

class DBM(object):
	""" Helper class to hold select and update connections """

	connections = {"select": None, "update": None}

	@classmethod
	def cursor(cls, c_type="select"):
		if not cls.connections[c_type]:
			cls.connections[c_type] = MySQLdb.connect(host = dbconf.dbserver[c_type], user = dbconf.dbserver['user'], passwd = dbconf.dbserver['pass'], db = dbconf.dbserver['db'], charset = "utf8", use_unicode = True)
		return cls.connections[c_type].cursor()

	@classmethod
	def close(cls, c_type="select"):
		if cls.connections[c_type]:
			cls.connections[c_type].close()
			cls.connections[c_type] = None

	@classmethod
	def commit(cls, c_type="update"):
		if cls.connections[c_type]:
			cls.connections[c_type].commit()


class BaseBlogs(object):

	def __init__(self):
		self.links = set()

	def read_feed(self):
		entries = 0

		""" Get last rss read """
		d = DBM.cursor()
		d.execute("select unix_timestamp(max(date)) from rss where blog_id = %s", (self.id,))
		self.last_read, = d.fetchone()
		d.close()

		c = DBM.cursor('update')
		c.execute("update blogs set blog_feed_read = now() where blog_id = %s", (self.id))
		DBM.commit()
		now = time.time()

		print "Reading ", self.url, self.feed
		try:
			if self.last_read:
				modified = time.gmtime(self.last_read)
			else:
				modified = time.gmtime(now - dbconf.blogs['min_hours']*3600)
			doc = feedparser.parse(self.feed, modified=modified)
		except (urllib2.URLError, urllib2.HTTPError, UnicodeEncodeError), e:
			print "connection failed (%s) %s" % (e, self.feed)
			return False

		if not doc.entries or doc.status == 304:
			print "Not modified"
			return entries

		for e in doc.entries:
			if entries >= self.max: break

			if hasattr(e, 'published_parsed') and e.published_parsed:
				timestamp = time.mktime(e.published_parsed)
			elif hasattr(e, 'updated_parsed') and e.updated_parsed:
				timestamp = time.mktime(e.updated_parsed)
			else:
				continue
				#timestamp = now

			if timestamp > now: timestamp = now
			try:
				if timestamp < time.time() - dbconf.blogs['min_hours']*3600 or (self.read and timestamp <  self.read) or len(e.title.strip()) < 2:
					#print "Old entry:", e.link, e.updated, e.updated_parsed, time.time() - timestamp
					pass
				else:
					try:
						link_clean = clean_url(e.link)
						c.execute("insert into rss (blog_id, user_id, date, date_parsed, title, url) values (%s, %s, FROM_UNIXTIME(%s), FROM_UNIXTIME(%s), %s, %s)", (self.id, self.user_id, now, timestamp, e.title, link_clean))
					except _mysql_exceptions.IntegrityError, e:
						""" Duplicated url, ignore it"""
						print "insert failed (%s)" % (e,)
						pass
					else:
						print "Added: ", e.link
						self.links.add(e.link)
						entries += 1
			except AttributeError, e:
					print "not existing attribute (%s)" % (e,)
					pass

		DBM.commit()
		c.close()
		return entries


	def get_feed_info(self):
		""" Get feed url by analysing the HTML """
		print "Reading blog info ", self.url
		self.feed = None
		self.title = None
		try:
			doc = urllib2.urlopen(url=self.url, timeout=20).read()
			soup = BeautifulSoup(doc, parseOnlyThese=SoupStrainer('head'))
			if not soup.head:
				""" Buggy blogs without <head> :( """
				print "Parsing all"
				soup = BeautifulSoup(doc)

			if soup.title and soup.title.string:
				self.title = soup.title.string.strip()
		except (socket.timeout, urllib2.URLError, urllib2.HTTPError, UnicodeEncodeError, httplib.BadStatusLine, TypeError), e:
			pass
		else:
			""" Search for feed urls """
			all_res = re_link.findall(unicode(soup))
			t_url = None
			for line in all_res:
				g = re_href.search(line)
				if g and g.group(1).find('comment') < 0:
					t_url = g.group(1)
					if t_url[0:5] != 'http:':
						t_url = self.url + '/' + t_url
					if not self.feed or len(t_url) < len(self.feed):
						self.feed = t_url

		if self.id:
			self.save_feed_info()

		return self.feed

	def save_feed_info(self):
		""" Save feed_url, title and last checked time in blogs table """
		c = DBM.cursor('update')
		print "Updating to blog: %s -%s-" % (self.base_url, self.feed)
		if self.title: self.title = self.title[0:125]
		else: self.title = ""
		c.execute("update blogs set blog_feed = %s, blog_title = %s, blog_feed_checked = now() where blog_id = %s", (self.feed, self.title, self.id))
		c.close()
		DBM.commit()


	def is_banned(self):
		local_domain = dbconf.domain.replace('http://', '').replace('www.', '')
		hostname = re.sub('^www\.', '', re.sub(':[0-9]+$', '', urlparse(self.url)[1]))
		if re.search(re.escape(local_domain)+r'$', hostname):
			print "Url is the same as local domain: ", local_domain, hostname
			return True


		c = DBM.cursor()
		c.execute("select count(*) from bans where ban_text in (%s, %s, %s, %s) AND ban_type in ('hostname','punished_hostname') AND (ban_expire IS null OR ban_expire > now())", (self.base_url, 'www.'+self.base_url, hostname, 'www.'+hostname));
		# print("select count(*) from bans where ban_text in (%s, %s, %s, %s) AND ban_type in ('hostname','punished_hostname') AND (ban_expire IS null OR ban_expire > now())" % (self.base_url, 'www.'+self.base_url, hostname, 'www.'+hostname));
		r = c.fetchone()
		c.close()
		if r[0] > 0:
			print "Banned ", hostname
			return True
		else:
			return False
