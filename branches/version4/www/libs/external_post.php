<?
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function twitter_post($text, $short_url) {
	global $globals;

	if (!class_exists("OAuth")) {
			syslog(LOG_NOTICE, "Meneame: pecl/oauth is not installed");
			return;
	}

	if (! $globals['twitter_consumer_key'] || ! $globals['twitter_consumer_secret']
		|| ! $globals['twitter_token'] || ! $globals['twitter_token_secret']) {
			syslog(LOG_NOTICE, "Meneame: consumer_key, consumer_secret, token, or token_secret not defined");
			return;
	}

	$maxlen = 140 - 24; //strlen($short_url);
	$msg = mb_substr(text_to_summary(html_entity_decode($text), $maxlen), 0, $maxlen) . ' ' . $short_url;
	$req_url = 'https://api.twitter.com/oauth/request_token';
	$acc_url = 'https://api.twitter.com/oauth/access_token';
	$authurl = 'https://api.twitter.com/oauth/authorize';
	$api_url = 'https://api.twitter.com/1.1/statuses/update.json';

	$oauth = new OAuth($globals['twitter_consumer_key'],$globals['twitter_consumer_secret'],OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
	$oauth->debug = 1;
	$oauth->setToken($globals['twitter_token'], $globals['twitter_token_secret']);

	$api_args = array("status" => $msg, "empty_param" => NULL);
	/* No using geo yet
	if (isset($entry['lat'])) {
		$api_args['lat'] = $entry['lat'];
		$api_args['long'] = $entry['long'];
	}
	*/
	try {
		$oauth->fetch($api_url, $api_args, OAUTH_HTTP_METHOD_POST, array("User-Agent" => "pecl/oauth"));
	} catch (Exception $e) {
		syslog(LOG_INFO, 'Menéame, Twitter caught exception: '.  $e->getMessage(). " in ".basename(__FILE__)."\n");
	}
}

function twitter_post_basic($text, $short_url) {
	global $globals;

	$t_status = urlencode(text_to_summary($text, 115) . ' ' . $short_url);
	syslog(LOG_NOTICE, "Meneame: twitter updater called, $short_url");
	$t_url = "http://twitter.com/statuses/update.xml";

	if (!function_exists('curl_init')) {
		syslog(LOG_NOTICE, "Meneame: curl is not installed");
		return;
	}
	$session = curl_init();
	curl_setopt($session, CURLOPT_URL, $t_url);
	curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($session, CURLOPT_HEADER, false);
	curl_setopt($session, CURLOPT_USERAGENT, "meneame.net");
	curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 15);
	curl_setopt($session, CURLOPT_TIMEOUT, 20);
	curl_setopt($session, CURLOPT_USERPWD, $globals['twitter_user'] . ":" . $globals['twitter_password']);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($session, CURLOPT_POST, 1);
	curl_setopt($session, CURLOPT_POSTFIELDS,"status=" . $t_status);
	$result = curl_exec($session);
	curl_close($session);
}


function jaiku_post($text, $short_url) {
	global $globals;

	syslog(LOG_NOTICE, "Meneame: jaiku updater called, $short_url");
	$url = "http://api.jaiku.com/json";

	if (!function_exists('curl_init')) {
		syslog(LOG_NOTICE, "Meneame: curl is not installed");
		return;
	}


	$postdata =  "method=presence.send";
	$postdata .= "&user=" . urlencode($globals['jaiku_user']);
	$postdata .= "&personal_key=" . $globals['jaiku_key'];
	$postdata .= "&icon=337"; // Event
	$postdata .= "&message=" . urlencode(text_to_summary(html_entity_decode($text), 115). ' ' . $short_url);

	$session = curl_init();
	curl_setopt($session, CURLOPT_URL, $url);
	curl_setopt($session, CURLOPT_HEADER, false);
	curl_setopt($session, CURLOPT_USERAGENT, "meneame.net");
	curl_setopt($session, CURLOPT_CONNECTTIMEOUT, 15);
	curl_setopt($session, CURLOPT_TIMEOUT, 20);
	curl_setopt ($session, CURLOPT_FOLLOWLOCATION,1);
	curl_setopt($session, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($session, CURLOPT_POST, 1);
	curl_setopt($session, CURLOPT_POSTFIELDS,$postdata);
	$result = curl_exec($session);
	curl_close($session);
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
	$rss = 'http://'.get_server_name().$globals['base_url'].'rss2.php';
	$p = new Publisher($globals['pubsub']);
	if ($p->publish_update($rss)) {
		syslog(LOG_NOTICE, "Meneame: posted to pubsub ($rss)");
	} else {
		syslog(LOG_NOTICE, "Meneame: failed to post to pubsub ($rss)");
	}
}


function facebook_post($link, $text = '') {
	require_once(mnminclude.'facebook-php-sdk/facebook.php');
	global $globals;


	$facebook = new Facebook(array(
		 'appId'  => $globals['facebook_key'],
		 'secret' => $globals['facebook_secret'],
		 'cookie' => true,
		 'perms' => 'read_stream, publish_stream',
	));

	$thumb = $link->has_thumb();
	if ($thumb) {
		if ($link->thumb_medium_url) {
			$thumb = $link->thumb_medium_url;
		}
	} else {
		$thumb = get_avatar_url($link->author, $link->avatar, 80);
	}

	$permalink = $link->get_permalink();
	syslog(LOG_INFO, "Meneame, $permalink picture sent to FB: $thumb");

	$data = array(
				'link' => $permalink,
				'message' => $text,
				'access_token' => $globals['facebook_token'],
				'picture' => $thumb
			);

	try {
		$r = $facebook->api('/me/links', 'POST', $data);
	} catch (Exception $e) {
		syslog(LOG_INFO, 'Menéame, Facebook caught exception: '.  $e->getMessage(). " in ".basename(__FILE__)."\n");
	}
}
