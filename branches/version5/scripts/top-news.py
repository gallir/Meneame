#! /usr/bin/env python
from __future__ import division

import gettext
_ = gettext.gettext
import dbconf
from utils import DBM

def main():
	""" Main loop of top-news """
	cursor = DBM.cursor()
	cursor.execute("select id, name from subs where enabled = 1")
	for row in cursor:
		do_site(row[0], row[1])

def do_site(site_id, site):
	""" Process a given site """
	links = {}
	cursor = DBM.cursor()
	query = """
		select link_id, link_uri,
			unix_timestamp(now()) - unix_timestamp(link_date)
		from links, sub_statuses
		where sub_statuses.id = %s
			and status = 'published'
			and date > date_sub(now(), interval 24 hour)
			and link = link_id
			and link_votes/20 > link_negatives
		order by link_date desc
	"""
	cursor.execute(query, (site_id,))
	links_total = 0
	for link_id, link_uri, old in cursor:
		links_total += 1
		values = {}
		values['uri'] = link_uri
		# How old in seconds
		values['old'] = old
		values['w'] = 0
		values['c'] = 0
		values['v'] = 0
		values['links_order'] = links_total
		links[link_id] = values

	if not links_total:
		return

	links_format = ','.join(['%s'] * len(links))
	query = """
		select vote_link_id,
			sum((1-(unix_timestamp(now())
					- unix_timestamp(vote_date))/36000)) as x,
			count(*)
		from votes
		where vote_link_id in (%s)
			and vote_type='links'
			and vote_date > date_sub(now(), interval 12 hour)
			and vote_user_id > 0
			and vote_value > 6.1
		group by vote_link_id
		order by x desc
	"""  % links_format
	cursor.execute(query, tuple(links))
	votes_total = 0
	votes_links = 0
	v_total = 0
	v_list = {}
	for link_id, old, votes in cursor:
		votes_links += 1
		votes_old = float(old)
		links[link_id]['v'] = votes_old
		v_total += votes_old
		v_list[link_id] = votes_old
		links[link_id]['votes'] = votes
		votes_total += votes
		links[link_id]['votes_order'] = votes_links

	if not votes_links:
		return

	v_average = v_total/votes_links
	votes_average = votes_total/votes_links

	query = """
		select comment_link_id,
			sum(1.5*(1-(unix_timestamp(now())
						- unix_timestamp(comment_date))/36000)),
			count(*)
		from comments
		where comment_link_id in (%s)
			and comment_date > date_sub(now(), interval 12 hour)
		group by comment_link_id
	""" % links_format
	cursor.execute(query, tuple(links))
	comments_total = 0
	comments_links = 0
	c_total = 0
	c_list = {}
	for link_id, old, count in cursor:
		comment_old = float(old)
		comments_links += 1
		links[link_id]['c'] = comment_old
		c_total += comment_old
		c_list[link_id] = comment_old
		links[link_id]['comments'] = count
		comments_total += count

	if not comments_links:
		return

	c_average = c_total/comments_links
	comments_average = comments_total/comments_links
	query = """
		select id, counter from link_clicks where id in (%s)
	""" % links_format

	cursor.execute(query, tuple(links))
	for link_id, clicks in cursor:
		links[link_id]['clicks'] = clicks

	cursor.close()

	print "Site:", site, "Votes average:", votes_average, v_average, \
			"Comments average:", comments_average, c_average

	for link_id, link_value in links.items():
		if link_value['c'] > 0 \
				and link_value['v'] > 0 \
				and 'clicks' in link_value:
			links[link_id]['w'] = (1 - link_value['old']/(1.5*86400)) * (link_value['v'] \
						   + link_value['c'] \
						   + link_value['clicks'] * (1 - link_value['old']/86400) * 0.03)

	sorted_ids = sorted(links, cmp=lambda x, y:
											cmp(links[y]['w'], links[x]['w']))

	if sorted_ids:
		annotations = ','.join([unicode(x) for x in sorted_ids[:10]])
		print "top-actives:", annotations
		cursor_update = DBM.cursor('update')
		query = """
			replace into annotations
				(annotation_key, annotation_expire, annotation_text)
				values (%s, date_add(now(), interval 15 minute), %s)
		"""
		cursor_update.execute(query, ('top-actives-'+site, annotations))
		cursor_update.close()
		DBM.commit()

	i = 0
	for key in sorted_ids:
		if links[key]['w'] > 0 and i < 10:
			i += 1


	# Select the top stories
	annotations = ','.join([unicode(x) for x in sorted_ids
						if links[x]['w'] > dbconf.tops['min-weight']
							and (links[x]['links_order'] > 1
							or links[x]['old'] > 3600)
							and links[x]['c'] > c_avrg(c_list, x) * 4
							and links[x]['v'] > c_avrg(v_list, x) * 4
							and links[x]['votes_order'] <= 10 ])


	if annotations:
		print "top-link: ", annotations
		cursor_update = DBM.cursor('update')
		query = """
			replace into annotations
				(annotation_key, annotation_expire, annotation_text)
				values (%s, date_add(now(), interval 10 minute), %s)
		"""
		cursor_update.execute(query, ('top-link-'+site, annotations))
		cursor_update.close()
		DBM.commit()
		print "Stored:", annotations
	else:
		print "No one selected"

def c_avrg(the_dict, exclude):
	""" Calculate the average excluding the given element"""
	index = 0
	total = 0
	for key in the_dict:
		if key != exclude:
			index += 1
			total += the_dict[key]
	if index > 0:
		return float(total/index)
	else: return 0

if __name__ == "__main__":
	main()
