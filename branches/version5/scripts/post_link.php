#! /usr/bin/env php
<?php
// This file post the indicated link to ever twitter o facebook account
// Argument required: hostname, link_id


if (count($argv) < 3) {
	syslog(LOG_INFO, "Usage: ".basename(__FILE__)." site_id hostname link_id");
	echo "Usage: ".basename(__FILE__)." site_id hostname link_id status\n";
	die;
}

$site_name = $argv[1];
$link_id = (int) $argv[2];
$status = $argv[3];

include(dirname(__FILE__).'/../config.php');
include(mnminclude.'external_post.php');

$my_id = SitesMgr::get_id($site_name);

if (! $my_id > 0) {
	syslog(LOG_INFO, "Meneame, post_link.php, site not found $site_name");
	echo "No site id found\n";
	die;
}

SitesMgr::__init($my_id);

$link = Link::from_db($link_id);
if (! $link) {
	syslog(LOG_INFO, "Meneame, post_link.php, link not found $link_id");
	echo "Link $link_id not found\n";
	die;
}
if (! $link->sub_status || (!empty($status) && $link->sub_status != $status) ) { // Don't post 
	syslog(LOG_INFO, "Status check ($status, $link->sub_status) didn't pass, exiting");
	die;
}


do_posts($link);


function do_posts($link) {
	global $globals;

	$info = SitesMgr::get_info();
	$properties = SitesMgr::get_extended_properties();

	syslog(LOG_INFO, "Meneame, posting $link->uri");

	$url = $link->get_permalink(true);
	echo "Posting $url: ".$globals['server_name']."\n"; 

	$image = $link->try_thumb('thumb_medium');
	if ($image && ! file_exists($image)) {
		$image = false;
	}

	if ($globals['url_shortener']) {
		$short_url = $link->get_short_permalink();
	} else {
		$short_url = $url;
	}

	if (! empty($properties['twitter_token']) && ! empty($properties['twitter_token_secret']) && ! empty($properties['twitter_consumer_key']) && ! empty($properties['twitter_consumer_secret']) ) {
		$r = false;
		$tries = 0;
		while (! $r && $tries < 4) {
			$r = twitter_post($properties, $link->title, $url, $image);
			$tries++;
			if (! $r) sleep(4);
		}
	}

	if (! empty($properties['facebook_token']) && ! empty($properties['facebook_key']) && ! empty($properties['facebook_secret'])) {
		$r = false;
		$tries = 0;
		while (! $r && $tries < 4) {
			$r = facebook_post($properties, $link);
			$tries++;
			if (! $r) sleep(4);
		}
	}

	/*
	if ($globals['pubsub']) {
		pubsub_post();
	}
	*/
}
