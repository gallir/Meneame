#! /usr/bin/env python
# -*- coding: utf-8 -*-

import time
import gettext
_ = gettext.gettext
import dbconf
from utils import DBM, BaseBlogs
import urllib
import urllib2
import socket


"""
ALTER TABLE  `meneame`.`users` ADD INDEX (  `user_url` );

ALTER TABLE  `blogs` ADD  `blog_feed` CHAR( 128 )
	NULL DEFAULT NULL AFTER  `blog_url` ,
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
	"""
	Main loop of the process
	"""
	# timeout in seconds
	timeout = 10
	socket.setdefaulttimeout(timeout)

	# Delete old entries
	update_cursor = DBM.cursor('update')
	query = """
		DELETE FROM rss
			WHERE date < date_sub(now(), interval %s day)
	"""
	update_cursor.execute(query, (dbconf.blogs['days_to_keep'],))
	DBM.commit()
	update_cursor.close()

	users = set()
	news = set()
	blogs = get_candidate_blogs(dbconf.blogs['days_published'],
								dbconf.blogs['min_karma'])
	for blog in blogs:
		entries = blog.read_feed()
		time.sleep(3)
		if entries > 0:
			users.add(blog.user)
			news.add(blog)


	if dbconf.blogs['post_user'] and dbconf.blogs['post_key'] and users:
		post = _('Nuevo apunte en el blog de: ')
		for note in news:
			post += "@" + note.user
			for link in note.links:
				post += " " + link
			post += "\n"

		post += '\nhttp://'+dbconf.domain+dbconf.blogs['viewer']+" #blogs"
		print post
		try:
			url = """
				http://{d}{newpost}?user={post_user}&key={post_key}&text={t}
			""".format(d= dbconf.domain,
						t= urllib.quote_plus(post),
						**dbconf.blogs)
			## TODO: Use timeout parameter instead of
			##       socket.setdefaulttimeout(timeout)
			urlpost = urllib2.urlopen(url)
			print urlpost.read(100)
			urlpost.close()
		except KeyError:
			print "Error posting", url
			pass


def get_candidate_blogs(days, min_karma):
	"""
	Get the possible blog we can read
	"""
	now = time.time()
	blogs = set()
	results = set()
	blogs_ids = set()
	users_ids = set()
	cursor = DBM.cursor()
	inner_cursor = DBM.cursor()

	# Select users that have at least one published

	query = """
		SELECT link_blog, blog_url, blog_feed,
				UNIX_TIMESTAMP(blog_feed_checked),
				UNIX_TIMESTAMP(blog_feed_read)
			FROM links, blogs
			WHERE link_status in ('published')
				AND link_date > date_sub(now(), interval %s day)
				AND blog_id = link_blog
				AND blog_type in ('blog', 'noiframe')
				AND (blog_feed_read is null
						OR blog_feed_read < date_sub(now(), interval 1 hour))
			GROUP BY blog_id
			HAVING count(*) < %s
	"""
	cursor.execute(query, (days, days))
	for row in cursor:
		blog = BaseBlogs()
		blog.id, blog.url, blog.feed, blog.checked, blog.read = row
		blog.base_url = blog.url.replace('http://', '').\
							replace('https://', '').replace('www.', '')
		if blog.is_banned():
			continue

		query = """
			SELECT user_login, user_id, user_karma
				FROM users
				WHERE user_url in (%s, %s, %s, %s, %s, %s)
					AND user_karma > %s
					AND user_level not in ('disabled', 'autodisabled')
				ORDER BY user_karma desc limit 1
		"""
		inner_cursor.execute(query,('http://'+blog.base_url,
						 'http://www.'+blog.base_url,
						 'http://'+blog.base_url+'/',
						 'http://www.'+blog.base_url+'/',
						 blog.base_url,
						 'www.'+blog.base_url,
						 min_karma))

		result = inner_cursor.fetchone()
		if result:
			blog.user, blog.user_id, blog.karma = result
			blogs.add(blog)
			blogs_ids.add(blog.id)
			users_ids.add(blog.user_id)



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
		blog = BaseBlogs()
		blog.id, blog.url, blog.feed, \
		blog.checked, blog.read, blog.user, blog.user_id, blog.karma = row
		blog.base_url = blog.url.replace('http://', '').\
							replace('https://', '').replace('www.', '')
		if blog.id not in blogs_ids and blog.user_id not in users_ids:
			blogs.add(blog)
			users_ids.add(blog.user_id)
			blogs_ids.add(blog.id)


	feeds_read = 0
	# Sort the set of blogs by date of read
	## TODO: This sort should be changed with rich comparators in BaseBlog
	sorted_blogs = sorted(blogs, key=lambda x: x.read)
	for blog in sorted_blogs:
		if feeds_read >= dbconf.blogs['max_feeds']:
			break
		## TODO: Solve this with a list comprehension
		if not blog.is_banned():
				# Check the number of remaining entries
				query = """
				SELECT count(*)
					FROM rss
					WHERE user_id = %s
						AND date > date_sub(now(), interval 1 day)
				"""
				inner_cursor.execute(query, (blog.user_id,))
				n_entries, = inner_cursor.fetchone()
				# Calculate the number of remaining entries
				blog.max = int(round(blog.karma/dbconf.blogs['karma_divisor'])) \
							- n_entries
				if not blog.max > 0:
					print "Max entries <= 0:", n_entries, blog.karma, blog.url
					continue

				if (not blog.feed and (not blog.checked or
									 blog.checked < now - 86400)) \
						or (blog.checked and blog.checked < now - 86400*7):
					blog.get_feed_info()

				if blog.feed and (not blog.read or blog.read < now - 3600):
					results.add(blog)
					print "Added ", blog.id, blog.user, blog.url
					feeds_read += 1
	cursor.close()
	return results


if __name__ == "__main__":
	main()
