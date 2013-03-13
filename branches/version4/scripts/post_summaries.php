#! /usr/bin/env php
<?php

global $_SERVER;
// Check which hostname server we run for, for example: e.meneame.net or www.meneame.net
if (!empty($argv[2])) {
	$_SERVER['SERVER_NAME'] = $argv[2];
}


// Post to Twitter/Jaiku the most voted and commented during last 24 hr
include(dirname(__FILE__).'/../config.php');
include(mnminclude.'external_post.php');

syslog(LOG_INFO, "Meneame, running ".basename(__FILE__)." for ".get_server_name());

if (intval($argv[1]) > 0) {
	$hours = intval($argv[1]);
} else {
	$hours = 24;
}
// Get most voted link
$link_sqls[_('Más votada')] = "select vote_link_id as id, count(*) as n from sub_statuses, links, votes use index (vote_type_4) where id = ".SitesMgr::my_id()." AND link_id = link AND link_status = 'published' AND vote_link_id = link AND vote_type='links' and vote_date > date_sub(now(), interval $hours hour) and vote_user_id > 0 and vote_value > 0 group by vote_link_id order by n desc limit 1";

// Most commented
$link_sqls[_('Más comentada')] = "select comment_link_id as id, count(*) as n from sub_statuses, comments use index (comment_date) where id = ".SitesMgr::my_id()." AND sub_statuses.status in ('published', 'metapublished') AND comment_link_id = link AND comment_date > date_sub(now(), interval $hours hour) group by comment_link_id order by n desc limit 1";

if ($globals['click_counter'] && $hours > 20) {
	$link_sqls[_('Más leída')] = "select sub_statuses.link as id, counter as n from sub_statuses, link_clicks where sub_statuses.id = ".SitesMgr::my_id()." AND sub_statuses.status in ('published', 'metapublished') AND date > date_sub(now(), interval $hours hour) and link_clicks.id = sub_statuses.link order by n desc limit 1";
}



foreach ($link_sqls as $key => $sql) {
	$res = $db->get_row($sql);
	if (! $res) next;
	$link = new Link;
	$link->id = $res->id;
	if ($link->read()) {
		if ($globals['url_shortener']) {
			$short_url = $link->get_short_permalink();
		} else {
			$short_url = fon_gs($link->get_permalink());
		}
		$intro = "$key ${hours}h";
		$text = "$intro: $link->title";

		if ($globals['twitter_token']) {
			twitter_post($text, $short_url); 
		}
		if ($globals['jaiku_user'] && $globals['jaiku_key']) {
			jaiku_post($text, $short_url); 
		}
		if ($globals['facebook_token']) {
			facebook_post($link, $intro);
		}

		echo "$short_url $text\n"; continue;
	}
}
?>
