import urllib2
from BeautifulSoup import BeautifulSoup,  SoupStrainer
import re
import MySQLdb
import _mysql_exceptions
import dbconf
import feedparser
import time
from urlparse import urlparse

re_link = re.compile(r'<link ([^>]+(?:text\/xml|application\/atom\+xml|application\/rss\+xml)[^>]+[^>]+)/*>',re.I)
re_href = re.compile(r'''href=['"]*([^"']+)["']''', re.I)

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

			if hasattr(e, 'published_parsed'):
				timestamp = time.mktime(e.published_parsed)
			elif hasattr(e, 'updated_parsed'):
				timestamp = time.mktime(e.updated_parsed)
			else:
				timestamp = now

			if timestamp > now: timestamp = now
			if timestamp < time.time() - dbconf.blogs['min_hours']*3600 or (self.read and timestamp <  self.read):
				#print "Old entry:", e.link, e.updated, e.updated_parsed, time.time() - timestamp
				pass
			else:
				try:
					c.execute("insert into rss (blog_id, user_id, date, date_parsed, title, url) values (%s, %s, FROM_UNIXTIME(%s), FROM_UNIXTIME(%s), %s, %s)", (self.id, self.user_id, now, timestamp, e.title, e.link))
				except _mysql_exceptions.IntegrityError, e:
					""" Duplicated url, ignore it"""
					print "insert failed (%s)" % (e,)
					pass
				else:
					print "Added: ", e.link
					self.links.add(e.link)
					entries += 1

		DBM.commit()
		c.close()
		return entries


	def get_feed_info(self):
		""" Get feed url by analysing the HTML """
		print "Reading blog info ", self.url
		self.feed = None
		self.title = None
		try:
			doc = urllib2.urlopen(url=self.url, timeout=10).read()
			soup = BeautifulSoup(doc, parseOnlyThese=SoupStrainer('head'))
			if not soup.head:
				""" Buggy blogs without <head> :( """
				print "Parsing all"
				soup = BeautifulSoup(doc)

			if soup.title and soup.title.string:
				self.title = soup.title.string.strip()
		except (urllib2.URLError, urllib2.HTTPError, UnicodeEncodeError), e:
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
		c.execute("update blogs set blog_feed = %s, blog_title = %s, blog_feed_checked = now() where blog_id = %s", (self.feed, self.title, self.id))
		c.close()
		DBM.commit()


	def is_banned(self):
		c = DBM.cursor()
		hostname = re.sub('^www\.', '', re.sub(':[0-9]+$', '', urlparse(self.url)[1]))
		c.execute("select count(*) from bans where ban_text in (%s, %s, %s, %s) AND ban_type in ('hostname','punished_hostname') AND (ban_expire IS null OR ban_expire > now())", (self.base_url, 'www.'+self.base_url, hostname, 'www.'+hostname));
		r = c.fetchone()
		c.close()
		if r[0] > 0:
			#print "Banned ", domain
			return True
		else:
			return False
