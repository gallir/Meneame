<?php
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function twitter_post($auth, $text, $short_url, $image = false) {
	global $globals;

	if (empty($auth['twitter_token']) || empty($auth['twitter_token_secret']) || empty($auth['twitter_consumer_key']) ||  empty($auth['twitter_consumer_secret'])) {
		return false;
	}	

	if (!class_exists("OAuth")) {
			syslog(LOG_NOTICE, "Meneame: pecl/oauth is not installed");
			return;
	}

	if (! $auth['twitter_consumer_key'] || ! $auth['twitter_consumer_secret']
		|| ! $auth['twitter_token'] || ! $auth['twitter_token_secret']) {
			syslog(LOG_NOTICE, "Meneame: consumer_key, consumer_secret, token, or token_secret not defined");
			return;
	}

	$req_url = 'https://api.twitter.com/oauth/request_token';
	$acc_url = 'https://api.twitter.com/oauth/access_token';
	$authurl = 'https://api.twitter.com/oauth/authorize';
	$api_url = 'https://api.twitter.com/1.1/statuses/update.json';
	$api_media_url = 'https://api.twitter.com/1.1/statuses/update_with_media.json';

	$api_args = array("empty_param" => NULL);

	$maxlen = 140 - 24; // minus the url length
	if ($image) {
		$maxlen -= 24;
		echo "Adding image: $image\n";
		$api_args['@media[]'] = '@'.$image;
		$url = $api_media_url;
	} else {
		$url = $api_url;
	}

	$msg = mb_substr(text_to_summary(html_entity_decode($text), $maxlen), 0, $maxlen);
	$msg_full = $msg . ' ' . $short_url;
	$api_args["status"] = $msg_full;

	$oauth = new OAuth($auth['twitter_consumer_key'],$auth['twitter_consumer_secret'],OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
	$oauth->debug = 1;
	$oauth->setRequestEngine( OAUTH_REQENGINE_CURL ); // For posting images
	$oauth->setToken($auth['twitter_token'], $auth['twitter_token_secret']);


	try {
		$oauth->fetch($url, $api_args, OAUTH_HTTP_METHOD_POST, array("User-Agent" => "pecl/oauth"));
	} catch (Exception $e) {
		syslog(LOG_INFO, 'Menéame, Twitter caught exception: '.  $e->getMessage(). " in ".basename(__FILE__)."\n");
		echo "Twitter post failed: $msg " . mb_strlen($msg) . "\n";
		return false;
	}

	// $response_info = $oauth->getLastResponseInfo();
	// echo $oauth->getLastResponse() . "\n";

	return true;
}

function fon_gs($url) {
	$gs_url = 'http://fon.gs/create.php?url='.urlencode($url);
	$res = get_url($gs_url);
	if ($res && $res['content'] && preg_match('/^OK/', $res['content'])) {
		$array = explode(' ', $res['content']);
		return $array[1];
	} else {
		return $url;
	}
}

function pubsub_post() {
	require_once(mnminclude.'pubsubhubbub/publisher.php');
	global $globals;

	if (! $globals['pubsub']) return false;
	$rss = 'http://'.get_server_name().$globals['base_url'].'rss';
	$p = new Publisher($globals['pubsub']);
	if ($p->publish_update($rss)) {
		syslog(LOG_NOTICE, "Meneame: posted to pubsub ($rss)");
	} else {
		syslog(LOG_NOTICE, "Meneame: failed to post to pubsub ($rss)");
	}
}


function facebook_post($auth, $link, $text = '') {
	global $globals;

	if (empty($auth['facebook_token']) || empty($auth['facebook_key']) || empty($auth['facebook_secret'])) {
		return false;
	}

	require_once(mnminclude.'facebook/facebook.php');


	$facebook = new Facebook(array(
		 'appId'  => $auth['facebook_key'],
		 'secret' => $auth['facebook_secret'],
		 'cookie' => true,
		 'perms' => 'read_stream, publish_stream',
	));

	$thumb = $link->has_thumb();
	if ($thumb) {
		if ($link->media_url) {
			$thumb = $link->media_url;
		}
	} else {
		$thumb = get_avatar_url($link->author, $link->avatar, 80);
	}

	$permalink = $link->get_permalink();
	syslog(LOG_INFO, "Meneame, $permalink picture sent to FB: $thumb");

	$data = array(
				'link' => $permalink,
				'message' => $text,
				'access_token' => $auth['facebook_token'],
				'picture' => $thumb
			);

	try {
		$r = $facebook->api('/me/links', 'POST', $data);
	} catch (Exception $e) {
		syslog(LOG_INFO, 'Menéame, Facebook caught exception: '.  $e->getMessage(). " in ".basename(__FILE__)."\n");
		return false;
	}
	return true;
}
