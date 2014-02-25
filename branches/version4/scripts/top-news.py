#! /usr/bin/env python
from __future__ import division

import MySQLdb
import gettext
_ = gettext.gettext
import dbconf
from utils import *

def main():
    """ Main loop of top-news """
    cursor = DBM.cursor()
    cursor.execute("select id, name from subs where enabled = 1")
    for row in cursor:
        do_site(row[1])

def do_site(site):
    """ Process a given site """
    links = {}
    cursor = DBM.cursor()
    cursor.execute("select link_id, link_uri, unix_timestamp(now()) - unix_timestamp(link_date) from links, subs, sub_statuses where subs.name = '%s' and subs.id = sub_statuses.id and status = 'published' and date > date_sub(now(), interval 24 hour) and link = link_id and link_votes/20 > link_negatives order by link_date desc" % (site,))
    links_total = 0
    for row in cursor:
        links_total += 1
        values = {}
        values['uri'] = row[1]
        # How old in seconds
        values['old'] = row[2]
        values['w'] = 0
        values['c'] = 0
        values['v'] = 0
        values['links_order'] = links_total
        links[row[0]] = values

    if not links_total:
        return

    links_format = ','.join(['%s'] * len(links))

    cursor.execute("select vote_link_id, sum((1-(unix_timestamp(now())-unix_timestamp(vote_date))/36000)) as x, count(*) from votes where vote_link_id in (%s) and vote_type='links' and vote_date > date_sub(now(), interval 12 hour) and vote_user_id > 0 and vote_value > 6.1 group by vote_link_id order by x desc" % links_format, tuple(links))
    votes_total = 0;
    votes_links = 0
    v_total = 0
    v_list = {}
    for row in cursor:
        votes_links += 1
        links[row[0]]['v'] = float(row[1])
        v_total += float(row[1])
        v_list[row[0]] = float(row[1])
        links[row[0]]['votes'] = row[2]
        votes_total += row[2]
        links[row[0]]['votes_order'] = votes_links

    if not votes_links:
        return

    v_average = v_total/votes_links
    votes_average = votes_total/votes_links


    cursor.execute("select comment_link_id, sum(1.5*(1-(unix_timestamp(now())-unix_timestamp(comment_date))/36000)), count(*)  from comments where comment_link_id in (%s) and comment_date > date_sub(now(), interval 12 hour) group by comment_link_id" % links_format, tuple(links))
    comments_total = 0
    comments_links = 0
    c_total = 0
    c_list = {}
    for row in cursor:
        comments_links += 1
        links[row[0]]['c'] = float(row[1])
        c_total += float(row[1])
        c_list[row[0]] = float(row[1])
        links[row[0]]['comments'] = row[2]
        comments_total += row[2]

    if not comments_links:
        return

    c_average = c_total/comments_links
    comments_average = comments_total/comments_links

    cursor.execute("select id, counter from link_clicks where id in (%s)" % links_format, tuple(links))
    for row in cursor:
        links[row[0]]['clicks'] = row[1]

    cursor.close()

    print "Site:", site, "Votes average:", votes_average, v_average, "Comments average:", comments_average, c_average
    for id in links:
        if links[id]['c'] > 0 and links[id]['v'] > 0 and 'clicks' in links[id]:
            links[id]['w'] = (1 - links[id]['old']/(1.5*86400)) * (links[id]['v'] + links[id]['c'] + links[id]['clicks'] * (1 - links[id]['old']/86400) * 0.02)

    sorted_ids = sorted(links, cmp=lambda x,y: cmp(links[y]['w'], links[x]['w']))

    if sorted_ids:
        str = ','.join([unicode(x) for x in sorted_ids[:10]])
        c = DBM.cursor('update')
        c.execute("replace into annotations (annotation_key, annotation_expire, annotation_text) values (%s, date_add(now(), interval 15 minute), %s)", ('top-actives-'+site, str))
        c.close()
        DBM.commit()

    i = 0
    for id in sorted_ids:
        if links[id]['w'] > 0 and i < 10:
            i += 1
            # print i, links[id]['links_order'], links[id]['old'], id, links[id]['uri'], links[id]['w'], "votes:", links[id]['votes'], links[id]['votes_order'], links[id]['v'], "comments:", links[id]['comments'], links[id]['c'], "clicks:", links[id]['clicks'], links[id]['clicks'] * (1 - links[id]['old']/86400) * 0.004

    # Select the top stories
    str = ','.join([unicode(x) for x in sorted_ids if links[x]['w'] > dbconf.tops['min-weight'] and (links[x]['links_order'] > 1 or links[x]['old'] > 3600) and links[x]['c'] > c_avrg(c_list, x) * 4 and links[x]['v'] > c_avrg(v_list, x) * 4 and links[x]['votes_order'] <= 10 ])
    print "SELECT: ", site, str

    if str:
        c = DBM.cursor('update')
        c.execute("replace into annotations (annotation_key, annotation_expire, annotation_text) values (%s, date_add(now(), interval 10 minute), %s)", ('top-link-'+site, str))
        c.close()
        DBM.commit()
        print "Stored:", str
    else:
        print "No one selected"

def c_avrg(the_dict, exclude):
    """ Calculate the average excluding the given element"""
    i = 0
    total = 0
    for e in the_dict:
        if e != exclude:
            i += 1
            total += the_dict[e]
    if i>0:
        return float(total/i)
    else: return 0

if __name__ == "__main__":
    main()
