<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// Don't check the user is logged
$globals['no_auth'] = true;
// Use the alternate server for api, if it exists
$globals['alternate_db_server'] = 'api';

$globals['max_load'] = 6;
include('../config.php');

// Free connections fast
ini_set('default_socket_timeout', 2);
$db->connect_timeout = 2;

if (isset($_GET['json']) || !empty($_GET['jsonp']))  {
	$json = true;
	$dict = array();
	header('Content-Type: application/json; charset=utf-8');
	if ($_GET['jsonp']) {
		$jsonp = preg_replace('/[^\w\d\.\-]/', '', $_GET['jsonp']);
		echo $jsonp . '(';
		$ending = ')';
	} else $ending = '';
} else {
	$json = false;
	header('Content-Type: text/plain; charset=UTF-8');
}

$cache_key = 'api_url'.$json.$_GET['url'];
if(memcache_mprint($cache_key)) {
	exit(0);
}

stats_increment('api', true);

$url = $db->escape($_GET['url']);

if(strlen($url) < 8 || ! preg_match('/^https{0,1}:\/\//', $url) || ! ($parsed = parse_url($url)) || mb_strlen($parsed['host']) < 5) {
	if ($json) {
		$dict['status'] = 'KO';
		echo @json_encode($dict);
		echo $ending;
	} else echo 'KO';
	die;
}

if (mb_strlen($parsed['path']) > 30) {
	// Filter extra queries and anchors
	$url = preg_replace('/[?#].*$/', '', $url);
	$unique = true;
} else {
	if (isset($_GET['all'])) $unique = false;
	else $unique = true;
}

$url_db = $url;

if (! $unique) {
	$url_db = addcslashes($url, '%_');
	$url_db = preg_replace('/\/$/', '', $url_db);
	$links = $db->get_col("select SQL_NO_CACHE link_id from links where link_url like '$url_db%' order by link_date DESC limit 100");
} else {
	$url_db = preg_replace('/\/$/', '', $url_db);
	$links = $db->get_col("select SQL_NO_CACHE link_id from links where link_url in ('$url_db', '$url_db/')");
}

if ($links) {
	$dict['status'] = 'OK';
	$dict['data'] = array();
	foreach ($links as $link_id) {
		$link = Link::from_db($link_id, null, false);
		if ($json) {
			$data = array();
			$data['id'] = $link_id;
			$data['url'] = $link->get_canonical_permalink();
			$data['status'] = $link->status;
			$data['votes'] = intval($link->votes);
			$data['anonymous'] = intval($link->anonymous);
			$data['karma'] = intval($link->karma);
			array_push($dict['data'], $data);
		} else {
			$response = 'OK '. $link->get_canonical_permalink()." $link->total_votes $link->status $link_id\n";
		}
	}
} else {
	if ($json) {
		$dict['status'] = 'KO';
		$dict['submit_url'] = 'http://'.get_server_name().'/submit?url='.$url;
	} else $response = 'KO http://'.get_server_name().'/submit?url='.$url;
}

if ($json) {
		$response = json_encode($dict) . $ending;
}

echo $response;
memcache_madd($cache_key, $response, 5);
?>
