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

$globals['max_load'] = 2;
include('../config.php');

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
		echo json_encode($dict);
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
	$links = $db->get_results("select SQL_NO_CACHE link_id, link_votes, link_anonymous, link_negatives, link_status, link_karma from links where link_url like '$url_db%' order by link_date DESC limit 100");
} else {
	$url_db = preg_replace('/\/$/', '', $url_db);
	$links = $db->get_results("select SQL_NO_CACHE link_id, link_votes, link_anonymous, link_negatives, link_status, link_karma from links where link_url in ('$url_db', '$url_db/')");
}

if ($links) {
	$dict['status'] = 'OK';
	$dict['data'] = array();
	foreach ($links as $dblink) {
		if ($json) {
			$data = array();
			$data['url'] = 'http://'.get_server_name().'/story.php?id='.$dblink->link_id;
			$data['status'] = $dblink->link_status;
			$data['votes'] = intval($dblink->link_votes);
			$data['anonymous'] = intval($dblink->link_anonymous);
			$data['karma'] = intval($dblink->link_karma);
			array_push($dict['data'], $data);
		} else {
			$response = 'OK http://'.get_server_name().'/story.php?id='.$dblink->link_id.' '.($dblink->link_votes+$dblink->link_anonymous).' '.$dblink->link_status."\n";
		}
	}
} else {
	if ($json) {
		$dict['status'] = 'KO';
		$dict['submit_url'] = 'http://'.get_server_name().'/submit.php?url='.$url;
	} else $response = 'KO http://'.get_server_name().'/submit.php?url='.$url;
}

if ($json) {
		$response = json_encode($dict) . $ending;
}

echo $response;
memcache_madd($cache_key, $response, 5);
?>
