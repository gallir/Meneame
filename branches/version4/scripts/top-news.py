#! /usr/bin/env python

import MySQLdb
import time
import gettext
_ = gettext.gettext
import dbconf
from utils import *



def main():

	"""
	link_max_age = 24 
	period = 8
	"""

	links = {}
	cursor = DBM.cursor()
	cursor.execute("select link_id, link_uri, unix_timestamp(now()) - unix_timestamp(link_date) from links where link_status = 'published' and link_date > date_sub(now(), interval 24 hour) and link_votes > link_negatives/20 order by link_date desc")
	links_total = 0
	for row in cursor:
		links_total += 1
		values = {}
		values['uri'] = row[1]
		""" How old in seconds"""
		values['old'] = row[2] 
		values['w'] = 0
		values['c'] = 0
		values['v'] = 0
		values['links_order'] = links_total
		links[row[0]] = values

	links_format = ','.join(['%s'] * len(links))

	cursor.execute("select vote_link_id, sum((1-(unix_timestamp(now())-unix_timestamp(vote_date))/28800)) as x, count(*) from votes where vote_link_id in (%s) and vote_type='links' and vote_date > date_sub(now(), interval 8 hour) and vote_user_id > 0 and vote_value > 7 group by vote_link_id order by x desc" % links_format, tuple(links))
	votes_total = 0;
	votes_links = 0
	v_total = 0
	for row in cursor:
		votes_links += 1
		links[row[0]]['v'] = float(row[1])
		v_total += float(row[1])
		links[row[0]]['votes'] = row[2]
		votes_total += row[2]
		links[row[0]]['votes_order'] = votes_links
	v_average = v_total/votes_links
	votes_average = votes_total/votes_links

		
	cursor.execute("select comment_link_id, sum(2*(1-(unix_timestamp(now())-unix_timestamp(comment_date))/28800)), count(*)  from comments where comment_link_id in (%s) and comment_date > date_sub(now(), interval 8 hour) group by comment_link_id" % links_format, tuple(links))
	comments_total = 0
	comments_links = 0
	c_total = 0
	for row in cursor:
		comments_links += 1
		links[row[0]]['c'] = float(row[1])
		c_total += float(row[1])
		links[row[0]]['comments'] = row[2]
		comments_total += row[2]
	c_average = c_total/comments_links
	comments_average = comments_total/comments_links

	cursor.execute("select id, counter from link_clicks where id in (%s)" % links_format, tuple(links))
	for row in cursor:
		links[row[0]]['clicks'] = row[1]

	cursor.close()

	print "Votes average:", votes_average, v_average, "Comments average:", comments_average, c_average
	for id in links:
		if links[id]['c'] > 0 and links[id]['v'] > 0:
			links[id]['w'] = links[id]['v'] + links[id]['c'] + links[id]['clicks'] * (1 - links[id]['old']/86400) * 0.005

	sorted_ids = sorted(links, cmp=lambda x,y: cmp(links[y]['w'], links[x]['w']))
	i = 0
	for id in sorted_ids:
		if links[id]['w'] > 0:
			i += 1
			print i, links[id]['links_order'], links[id]['uri'], links[id]['w'], "votes:", links[id]['votes'], links[id]['votes_order'], links[id]['v'], "comments:", links[id]['comments'], links[id]['c'], "clicks:", links[id]['clicks'], links[id]['clicks'] * (1 - links[id]['old']/86400) * 0.005

	# Select the top stories
	str = ','.join([unicode(x) for x in sorted_ids if links[x]['w'] > dbconf.tops['min-weight'] and links[x]['links_order'] > 2 and links[x]['c'] > c_average * 4 and links[x]['v'] > v_average * 4 and links[x]['votes_order'] <= 5])

	if str:
		c = DBM.cursor('update')
		c.execute("replace into annotations (annotation_key, annotation_expire, annotation_text) values('top-links', date_add(now(), interval 10 minute), %s)", (str,))
		c.close()
		DBM.commit()
		print "Stored:", str
	else:
		print "No one selected"


if __name__ == "__main__":
	main()
