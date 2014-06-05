#! /usr/bin/env python
# -*- coding: utf-8 -*-

import time
import feedparser
import dbconf
from utils import DBM, clean_url, read_annotation, store_annotation, post_note
import re
import json


URLS = { u"Vídeo Arde Internet": "https://gdata.youtube.com/feeds/base/users/ArdeInternet/uploads" }

KEY = "analysis_"

def main():
	annotation = read_annotation(KEY+"checked")
	if not annotation:
		data = {}
	else:
		data = json.loads(annotation)
	modified = False

	for site, rss in URLS.iteritems():
		print(site, rss)
		if site in data and data[site]['ts'] > 0:
			last_checked = data[site]['ts']
		else:
			if not site in data:
				data[site] = dict()
			data[site]['ts'] = last_checked = time.time() - 48*3600

		try:
			doc = feedparser.parse(rss, modified=time.gmtime(last_checked))
		except (urllib2.URLError, urllib2.HTTPError, UnicodeEncodeError), e:
			print "connection failed (%s) %s" % (e, rss)
			return False

		if not doc.entries or doc.status == 304:
			return False

		for e in doc.entries:
			ts = analyze_entry(site, e)
			if ts and ts > last_checked:
				modified = True
				last_checked = data[site]['ts'] = ts

	if modified:
		data = json.dumps(data)
		store_annotation(KEY+"checked", data)


def analyze_entry(site, e):
		if hasattr(e, 'published_parsed') and e.published_parsed:
			timestamp = time.mktime(e.published_parsed)
		elif hasattr(e, 'updated_parsed') and e.updated_parsed:
			timestamp = time.mktime(e.updated_parsed)
		else:
			return False

		if timestamp > time.time(): timestamp = time.time()

		if hasattr(e, "content"): content = e.summary
		else: content = e.description

		try:
			g = re.search(r'Noticia en Men&eacute;ame: (http://menea.me/(\w+)) ', content)
		except:
			return False

		if g:
			id = int(g.group(2), 36)
			original_url = g.group(1)
			entry = dict()
			entry["site"] = site.encode('ascii', 'xmlcharrefreplace')
			entry["title"] = e.title.encode('ascii', 'xmlcharrefreplace')
			entry['url'] =  clean_url(e.link)
			entry['ts'] = int(timestamp)
			entry['id'] = id
			res = store(site, entry)
			if res:
				post = "%s: &laquo;%s&raquo; %s (%s)" % (entry["site"], entry["title"], entry['url'], original_url)
				print "Posting", post
				if not post_note(post):
					print "Error posting"
				return res

		return False

def store(site, entry):
	id = entry['id']
	cursor = DBM.cursor()
	cursor.execute("select link_id from links where link_id = %s", (id,))
	result = cursor.fetchone()
	if not result:
		return False

	annotation = read_annotation(KEY+str(id))
	if not annotation:
		data = {}
	else:
		data = json.loads(annotation)
		if data[site]:
			if data[site]['ts'] >= entry['ts']:
				return False

			del(data[site])

	data[site] = entry
	data = json.dumps(data)
	if data:
		store_annotation(KEY+str(id), data)
		print data
	cursor.close()
	return entry['ts']



if __name__ == "__main__":
	main()

