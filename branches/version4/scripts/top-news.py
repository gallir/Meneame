#! /usr/bin/env python

import MySQLdb
import time
import gettext
_ = gettext.gettext
import dbconf
from utils import *



def main():

	links = {}
	cursor = DBM.cursor()
	cursor.execute("select link_id, link_uri, sum((1-(unix_timestamp(now())-unix_timestamp(vote_date))/43200)) as x, unix_timestamp(now()) - unix_timestamp(link_date) from votes, links where link_status = 'published' and link_date > date_sub(now(), interval 24 hour) and link_votes > link_negatives/20 and vote_type='links' and vote_link_id = link_id and vote_date > date_sub(now(), interval 12 hour) and vote_user_id > 0 and vote_value > 7 group by vote_link_id order by x desc limit 15")
	for row in cursor:
		values = {}
		values['uri'] = row[1]
		values['w'] = 0
		values['v'] = float(row[2])
		""" How old in seconds"""
		values['old'] = row[3] 
		links[row[0]] = values

	links_format = ','.join(['%s'] * len(links))
		
	cursor.execute("select comment_link_id, sum((1-(unix_timestamp(now())-unix_timestamp(comment_date))/43200)) as x from comments where comment_link_id in (%s) and comment_date > date_sub(now(), interval 12 hour) group by comment_link_id" % links_format, tuple(links))
	for row in cursor:
		if row[0] in links:
			links[row[0]]['c'] = float(row[1])

	cursor.execute("select id, counter from link_clicks where id in (%s)" % links_format, tuple(links))
	for row in cursor:
		if row[0] in links:
			links[row[0]]['clicks'] = row[1]

	cursor.close()

	for id in links:
			links[id]['w'] = links[id]['v'] + 2 * links[id]['c'] + links[id]['clicks'] * (1 - links[id]['old']/86400) * 0.005;

	sorted_ids = sorted(links, cmp=lambda x,y: cmp(links[y]['w'], links[x]['w']))
	for id in sorted_ids:
		if links[id]['w'] > 0:
			print links[id]['uri'], links[id]['w'], links[id]['old'], links[id]['clicks'], links[id]['clicks'] * (1 - links[id]['old']/86400) * 0.005

	str = ','.join([unicode(x) for x in sorted_ids])
	print str

	c = DBM.cursor('update')
	c.execute("replace into annotations (annotation_key, annotation_expire, annotation_text) values('top-links', date_add(now(), interval 1 hour), %s)", (str,))
	c.close()
	DBM.commit()


if __name__ == "__main__":
	main()
