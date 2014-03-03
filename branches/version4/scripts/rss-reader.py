#! /usr/bin/env python
# -*- coding: utf-8 -*-

import time
import gettext
_ = gettext.gettext
import dbconf
from utils import DBM
import urllib
import urllib2
import socket


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

	# timeout in seconds
	timeout = 10
	socket.setdefaulttimeout(timeout)

	# Delete old entries
	c = DBM.cursor('update')
	query = """
		DELETE FROM rss
			WHERE date < date_sub(now(), interval %s day)
	"""
	c.execute(query, (dbconf.blogs['days_to_keep'],))
	DBM.commit()
	c.close()

	users = set()
	news = set()
	blogs = get_candidate_blogs(dbconf.blogs['days_published'], dbconf.blogs['min_karma'])
	for o in blogs:
		entries = o.read_feed()
		time.sleep(3)
		if entries > 0:
			users.add(o.user)
			news.add(o)


	if dbconf.blogs['post_user'] and dbconf.blogs['post_key'] and users:
		post = _('Nuevo apunte en el blog de: ')
		for o in news:
			post += "@" + o.user
			for l in o.links:
				post += " " + l
			post += "\n"

		post += '\nhttp://'+dbconf.domain+dbconf.blogs['viewer']+" #blogs"
		print post
		try:
			f = urllib2.urlopen('http://'+dbconf.domain+dbconf.blogs['newpost']+'?user='+dbconf.blogs['post_user']+'&key='+dbconf.blogs['post_key']+'&text='+urllib.quote_plus(post))
			print f.read(100)
			f.close()
		except KeyError:
			pass


def get_candidate_blogs(days, min_karma):
	now = time.time()
	blogs = set()
	results = set()
	blogs_ids = set()
	users_ids = set()
	cursor = DBM.cursor()
	c = DBM.cursor()

	# Select users that have at least one published
	query = """
		SELECT link_blog, blog_url, blog_feed,
				UNIX_TIMESTAMP(blog_feed_checked),
				UNIX_TIMESTAMP(blog_feed_read),
				count(*) as n
			FROM links, blogs
			WHERE link_status in ('published')
				AND link_date > date_sub(now(), interval %s day)
				AND blog_id = link_blog
				AND blog_type='blog'
				AND (blog_feed_read is null
						OR blog_feed_read < date_sub(now(), interval 1 hour))
			GROUP BY blog_id
	"""
	cursor.execute(query, (days,))
	for row in cursor:
		o = BaseBlogs()
		o.id, o.url, o.feed, o.checked, o.read, o.counter = row
		o.base_url = o.url.replace('http://', '').replace('https://', '').replace('www.', '')
		if o.is_banned():
			continue

		if o.counter < days:
			query = """
				SELECT user_login, user_id, user_karma
					FROM users
					WHERE user_url in (%s, %s, %s, %s, %s, %s)
						AND user_karma > %s
						AND user_level not in ('disabled', 'autodisabled')
					ORDER BY user_karma desc limit 1"
			"""
			c.execute(query,('http://'+o.base_url,
							 'http://www.'+o.base_url,
							 'http://'+o.base_url+'/',
							 'http://www.'+o.base_url+'/',
							 o.base_url, 'www.'+o.base_url, min_karma))
			r = c.fetchone()
			if r is not None:
				o.user, o.user_id, o.karma = r
				blogs.add(o)
				blogs_ids.add(o.id)
				users_ids.add(o.user_id)

	# Select active users that have no published posts
	query = """
	SELECT blog_id, blog_url, blog_feed, UNIX_TIMESTAMP(blog_feed_checked),
			UNIX_TIMESTAMP(blog_feed_read), user_login, user_id, user_karma
		FROM users, blogs
		WHERE user_karma >= %s
			AND user_url like 'http://%%'
			AND user_level not in ('disabled', 'autodisabled')
			AND user_modification > date_sub(now(), interval %s day)
			AND user_date < date_sub(now(), interval %s day)
			AND blog_url in (
				concat('http://www.',replace(replace(user_url, 'http://', ''), 'www.', '')),
				concat('http://',replace(replace(user_url, 'http://', ''), 'www.', '')),
				concat('http://www.',replace(replace(user_url, 'http://', ''), 'www.', ''), '/'),
				concat('http://',replace(replace(user_url, 'http://', ''), 'www.', ''), '/')
			)
			AND (blog_feed_read is null or blog_feed_read < date_sub(now(), interval 1 hour))
			order by blog_id desc, user_karma desc
	"""
	cursor.execute(query, (dbconf.blogs['active_min_karma'],
							dbconf.blogs['active_min_activity'],
							dbconf.blogs['active_min_age']) )
	for row in cursor:
		o = BaseBlogs()
		o.id, o.url, o.feed, o.checked, o.read, o.user, o.user_id, o.karma = row
		o.base_url = o.url.replace('http://', '').replace('https://', '').replace('www.', '')
		if o.id not in blogs_ids and o.user_id not in users_ids:
				blogs.add(o)
				users_ids.add(o.user_id)
				blogs_ids.add(o.id)

	feeds_read = 0
	sorted_blogs = sorted(blogs, cmp=lambda x,y: cmp(x.read, y.read))
	for o in sorted_blogs:
		if feeds_read >= dbconf.blogs['max_feeds']: break
		if not o.is_banned():
				# Check the number of remaining entries
				query = """
				SELECT count(*)
					FROM rss
					WHERE user_id = %s
						AND date > date_sub(now(), interval 1 day)
				"""
				c.execute(query, (o.user_id,))
				n_entries, = c.fetchone()
				# Calculate the number of remaining entries
				o.max = int(round(o.karma/dbconf.blogs['karma_divisor'])) - n_entries
				if not o.max > 0:
					print "Max entries <= 0:", n_entries, o.karma, o.url
					continue

				if (not o.feed and (not o.checked or o.checked < now - 86400)) or (o.checked and o.checked < now - 86400*7):
					o.get_feed_info()

				if o.feed and (not o.read or o.read < now - 3600):
					results.add(o)
					print "Added ", o.id, o.user, o.url
					feeds_read += 1
	cursor.close()
	return results


if __name__ == "__main__":
	main()
