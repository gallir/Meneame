#! /usr/bin/env python
from __future__ import division

import MySQLdb
import sys
import time
import gettext
_ = gettext.gettext
import dbconf
from utils import *

def main():
    if len(sys.argv) == 2:
        link_id = int(sys.argv[1])
    else:
        link_id = 0

    if link_id > 0:
        print get_link_average(link_id)
    else:
        total = 0
        average = 0
        cursor = DBM.cursor()
        cursor.execute("select link_id from links where link_status = 'published' order by link_date desc limit 20")
        for row in cursor:
            average += get_link_average(row[0])
            total += 1

        print average/total




def get_link_average(link_id):
    votes = {}
    values_sum = 0
    values_count = 0

    cursor = DBM.cursor()
    cursor.execute("select vote_user_id, vote_value from votes, links where vote_type = 'links' and vote_link_id = %s and vote_user_id > 0 and vote_value > 0 and link_id = vote_link_id and ( (link_status = 'published' and vote_date < link_date) OR link_status != 'published')" % (link_id, ))
    for row in cursor:
        votes[row[0]] = int(row[1] / abs(row[1]))


    sorted_users = sorted(votes)
    for minor in sorted_users:
        for major in sorted_users:
            if major <= minor:
                continue
            cursor.execute("select value, UNIX_TIMESTAMP(date) from users_similarities where minor = %s and major = %s" % (minor, major))
            row = cursor.fetchone()
            if row:
                value = row[0] ### *votes[major]*votes[minor]
            else:
                value = 0
            values_sum += value
            values_count += 1

    print values_sum, values_count
    average = values_sum/values_count
    return average

if __name__ == "__main__":
    main()
