#! /usr/bin/env php
<?php
// This file post the indicated link to ever twitter o facebook account
// Argument required: hostname, link_id


if (count($argv) < 3) {
	syslog(LOG_INFO, "Usage: ".basename(__FILE__)." site_id hostname link_id");
	echo "Usage: ".basename(__FILE__)." site_id hostname link_id status\n";
	die;
}

$hostname = $argv[1];
$link_id = (int) $argv[2];
$status = $argv[3];

$_SERVER['SERVER_NAME'] = $hostname;

include(dirname(__FILE__).'/../config.php');
include(mnminclude.'external_post.php');

$my_id = SitesMgr::my_id();

if (! $my_id > 0) {
	syslog(LOG_INFO, "Meneame, post_link.php, site not found $hostname");
	echo "No site id found\n";
	die;
}

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

	syslog(LOG_INFO, "Meneame, posting $link->uri");

	echo "Posting $link->uri: ".$globals['server_name']. "--".$globals["site_shortname"]."\n"; 
	$url = $link->get_permalink();
	$image = $link->try_thumb('thumb_medium');
	if ($image && ! file_exists($image)) {
		$image = false;
	}

	if ($globals['url_shortener']) {
		$short_url = $link->get_short_permalink();
	} else {
		$short_url = $url;
	}

	if ($globals['twitter_token'] && $globals['twitter_token_secret']) {
		$r = false;
		$tries = 0;
		while (! $r && $tries < 4) {
			$r = twitter_post($link->title, $url, $image);
			$tries++;
			if (! $r) sleep(4);
		}
	}

	if ($globals['facebook_token']) {
		$r = false;
		$tries = 0;
		while (! $r && $tries < 4) {
			$r = facebook_post($link);
			$tries++;
			if (! $r) sleep(4);
		}
	}

	if ($globals['jaiku_user'] && $globals['jaiku_key']) {
		jaiku_post($link->title, $short_url);
	}
	if ($globals['pubsub']) {
		pubsub_post();
	}
}
