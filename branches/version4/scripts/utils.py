import urllib2
import re
import MySQLdb
import _mysql_exceptions
import dbconf
import feedparser
import time

re_link = re.compile(r'<link[^>]+(text\/xml|application\/atom\+xml|application\/rss\+xml)[^>]+[^>]+>',re.I)
re_body = re.compile(r'< *body.*>', re.I)
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



def get_feed_url(url, blog_id = None):
	""" Get feed url by analysing the HTML """
	feed_url = None
	try:
		doc = urllib2.urlopen(url=url, timeout=10)
	except:
		pass
	else:
		lines = 0
		for l in doc:
			if lines > 1 and re_body.search(l): break
			res = re_link.search(l)
			if res:
				g = re_href.search(res.group(0))
				if g and g.group(1).find('comment') < 0:
					feed_url = g.group(1)
					if feed_url[0:5] != 'http:':
						feed_url = url + '/' + feed_url
			lines += 1
	if blog_id:
		save_feed_url(blog_id, feed_url)
	return feed_url

def save_feed_url(blog_id, url):
	""" Save feed_url and last checked time in blogs table """
	c = DBM.cursor('update')
	print "Updating to blog: %d -%s-" % (blog_id, url)
	c.execute("update blogs set blog_feed = %s, blog_feed_checked = now() where blog_id = %s", (url, blog_id))
	c.close()
	DBM.commit()

def read_feed(blog_id, data):
	entries = 0

	c = DBM.cursor('update')
	c.execute("update blogs set blog_feed_read = now() where blog_id = %s", (blog_id))
	DBM.commit()
	now = time.time()

	print "Reading ", data['url'], data['feed']
	try:
		if data['read']:
			modified = time.gmtime(data['read'])
			doc = feedparser.parse(data['feed'], modified=modified)
		else:
			doc = feedparser.parse(data['feed'])
	except Exception, e:
		print "connection failed (%s) %s" % (e, data['feed'])
		return False

	if not doc.entries or doc.status == 304:
		print "Not modified"
		return entries

	for e in doc.entries:
		if entries >= data['max']: break

		timestamp = time.mktime(e.updated_parsed)
		if timestamp > now: timestamp = now
		if timestamp < time.time() - dbconf.blogs['min_hours']*3600 or (data['read'] and timestamp <  data['read']):
			#print "Old entry:", e.link, e.updated, e.updated_parsed, time.time() - timestamp
			pass
		else:
			try:
				c.execute("insert into rss (blog_id, user_id, date, date_parsed, title, url) values (%s, %s, FROM_UNIXTIME(%s), FROM_UNIXTIME(%s), %s, %s)", (blog_id, data['user_id'], now, timestamp, e.title, e.link))
			except _mysql_exceptions.IntegrityError, e:
				""" Duplicated url, ignore it"""
				print "insert failed (%s)" % (e,)
				pass
			else:
				print "Added: ", e.link
				entries += 1

	DBM.commit()
	c.close()
	return entries

def is_site_banned(domain):
	c = DBM.cursor()
	c.execute("select count(*) from bans where ban_text in (%s, %s) AND ban_type in ('hostname','punished_hostname') AND (ban_expire IS null OR ban_expire > now())", (domain, 'www.'));
	r = c.fetchone()
	c.close()
	if r[0] > 0:
		#print "Banned ", domain
		return True
	else:
		return False
