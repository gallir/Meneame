#! /usr/bin/env python

import MySQLdb
import time
import gettext
_ = gettext.gettext
import dbconf
from utils import *
import urllib
import urllib2

"""
ALTER TABLE  `meneame`.`users` ADD INDEX (  `user_url` );

ALTER TABLE  `blogs` ADD  `blog_feed` CHAR( 128 ) NULL DEFAULT NULL AFTER  `blog_url` ,
ADD  `blog_feed_checked` TIMESTAMP NULL AFTER  `blog_feed`,
ADD  `blog_feed_read` TIMESTAMP NULL AFTER  `blog_feed_checked`;

ALTER TABLE  `blogs` ADD  `blog_title` CHAR( 128 ) NULL DEFAULT NULL;

CREATE TABLE  `meneame`.`rss` (
`blog_id` INT UNSIGNED NOT NULL ,
`user_id` INT UNSIGNED NOT NULL DEFAULT  '0',
`link_id` INT UNSIGNED NULL ,
`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`date_parsed` TIMESTAMP NULL ,
`url` CHAR( 250 ) NOT NULL ,
`title` CHAR( 250 ) NOT NULL
) ENGINE = INNODB;

ALTER TABLE  `meneame`.`rss` ADD INDEX (  `date` );
ALTER TABLE  `meneame`.`rss` ADD INDEX (  `blog_id` ,  `date` );
ALTER TABLE  `meneame`.`rss` ADD INDEX (  `user_id` ,  `date` );
ALTER TABLE  `meneame`.`rss` ADD UNIQUE ( `url` );
"""

def main():

	""" Delete old entries """
	c = DBM.cursor('update')
	c.execute("delete from rss where date < date_sub(now(), interval %s day)", (dbconf.blogs['days_to_keep'],))
	c.close()
	DBM.commit()

	users = set()
	blogs = get_candidate_blogs(dbconf.blogs['days_published'], dbconf.blogs['min_karma'])
	for id in blogs:
		#print blogs[id]
		entries = read_feed(id, blogs[id])
		if entries > 0:
			users.add(blogs[id]['user'])

	if dbconf.blogs['post_user'] and dbconf.blogs['post_key'] and users:
		post = _('Nuevo apunte en el blog de: ')
		for u in users:
			post += "@" + u + " "
		post += '\nhttp://'+dbconf.domain+dbconf.blogs['viewer']+" #blogs"
		print post
		f = urllib2.urlopen('http://'+dbconf.domain+dbconf.blogs['newpost']+'?user='+dbconf.blogs['post_user']+'&key='+dbconf.blogs['post_key']+'&text='+urllib.quote_plus(post))
		print f.read(100)
		f.close()


def get_candidate_blogs(days, min_karma):
	now = time.time();
	cursor = DBM.cursor()
	cursor.execute ("SELECT link_blog, blog_url, blog_feed, UNIX_TIMESTAMP(blog_feed_checked), UNIX_TIMESTAMP(blog_feed_read), count(*) as n  from links, blogs where link_status in ('published') and link_date > date_sub(now(), interval %s day) and blog_id = link_blog and blog_type='blog' group by blog_id", (days,))
	blogs= {}
	for row in cursor:
		blog_id, blog_url, blog_feed, blog_checked, blog_feed_read, counter = row
		base_url = blog_url.replace('http://', '').replace('www.', '')
		if counter < days and not is_site_banned(base_url):
			c = DBM.cursor()
			c.execute("select user_login, user_id, user_karma from users where user_url in (%s, %s, %s, %s, %s, %s) and user_karma > %s order by user_karma desc limit 1",
					('http://'+base_url, 'http://www.'+base_url, 'http://'+base_url+'/', 'http://www.'+base_url+'/', base_url, 'www.'+base_url, min_karma))
			r = c.fetchone()
			if r is not None:
				user_login, user_id, user_karma = r

				""" Check the number of remaining entries """
				c.execute("select count(*) from rss where blog_id = %s and date > date_sub(now(), interval 1 day)", (blog_id,))
				n_entries, = c.fetchone()
				""" Calculate the number of remaining entries """
				max_entries = int(round(user_karma/dbconf.blogs['karma_divisor'])) - n_entries
				if not max_entries > 0:
					#print "Max entries <= 0:", n_entries, user_karma, blog_url
					continue

				if (not blog_feed and (not blog_checked or blog_checked < now - 86400)) or (blog_feed and blog_checked < now - 86400*7):
					blog_feed = get_feed_info(blog_url, blog_id)

				if blog_feed and (not blog_feed_read or blog_feed_read < now - 3600):
					blogs[blog_id] = {"url":blog_url, "feed":blog_feed, "user": user_login, "user_id": user_id, "karma": user_karma, "read": blog_feed_read, "max": max_entries}
					#print "Added ", blog_id, blogs[blog_id]
	cursor.close()
	DBM.close()
	return blogs

if __name__ == "__main__":
	main()
