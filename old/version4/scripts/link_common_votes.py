#! /usr/bin/env python
"""
Link common votes.
"""
from __future__ import division

import sys
import gettext
import itertools
_ = gettext.gettext
from utils import DBM

def main():
	""" Main loop, processing the top 20 published links"""
	if len(sys.argv) == 2:
		link_id = int(sys.argv[1])
		print get_link_average(link_id)
	else:
		total = 0
		average = 0
		cursor = DBM.cursor()
		query = """
			select link_id
				from links
				where link_status = 'published'
				order by link_date desc
				limit 20"
		"""
		cursor.execute(query)
		for total, link_id in enumerate(cursor, start= 1):
			average += get_link_average(link_id)

		assert total > 0, "No published links."

		print average/total


def get_link_average(link_id):
	""" Get the average weight of a link """
	votes = {}
	values_sum = 0
	values_count = 0

	cursor = DBM.cursor()
	query = """
		select vote_user_id, vote_value
			from votes, links
			where vote_type = 'links'
				and vote_link_id = %s
				and vote_user_id > 0
				and vote_value > 0
				and link_id = vote_link_id
				and ( (link_status = 'published'
					and vote_date < link_date)
				OR link_status != 'published')
	"""
	cursor.execute(query, (link_id, ))
	for user_id, vote_value in cursor:
		votes[user_id] = int(vote_value / abs(vote_value))

	sorted_users = [(minor, major) for (minor, major)
									in itertools.product(votes, repeat= 2)
										if major > minor]

	for values_count, (minor, major) in enumerate(sorted_users, start = 1):
		query = """
			select value, UNIX_TIMESTAMP(date)
				from users_similarities
				where minor = %s
					and major = %s
		"""
		cursor.execute(query, (minor, major))
		row = cursor.fetchone()
		values_sum += 0 if row is None else row[0]

	print values_sum, values_count
	average = values_sum/values_count
	return average

if __name__ == "__main__":
	main()
