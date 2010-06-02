<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

if (isset($_GET['json']) || !empty($_GET['jsonp']))  {
	$json = true;
	$dict = array();
	header('Content-Type: application/json; charset=utf-8');
	if ($_GET['jsonp']) {
		$jsonp = preg_replace('/[^\w\d]/', '', $_GET['jsonp']);
		echo $jsonp . '(';
		$ending = ')';
	} else $ending = '';
} else {
	$json = false;
	header('Content-Type: text/plain; charset=UTF-8');
}

stats_increment('api', true);

$url = $db->escape($_GET['url']);
$url = addcslashes($url, '%_');
if(strlen($url) < 8 || ! ($p_url = parse_url($_GET['url'])) || strlen($p_url['host']) < 5) {
	if ($json) {
		$dict['status'] = 'KO';
		echo json_encode($dict);
		echo $ending;
	} else echo 'KO';
	die;
}

$url = preg_replace('/\/$/', '', $url);
if (isset($_GET['all'])) {
    $links = $db->get_results("select SQL_NO_CACHE link_id, link_votes, link_anonymous, link_negatives, link_status, link_karma from links where link_url like '$url%' order by link_date DESC limit 100");
} else {
    $links = $db->get_results("select SQL_NO_CACHE link_id, link_votes, link_anonymous, link_negatives, link_status, link_karma from links where link_url in ('$url', '$url/')");
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
			echo 'OK http://'.get_server_name().'/story.php?id='.$dblink->link_id.' '.($dblink->link_votes+$dblink->link_anonymous).' '.$dblink->link_status."\n";
		}
	}
} else {
	if ($json) {
		$dict['status'] = 'KO';
		$dict['submit_url'] = 'http://'.get_server_name().'/submit.php?url='.$url;
	} else echo 'KO http://'.get_server_name().'/submit.php?url='.$url;
}

if ($json) {
		echo json_encode($dict);
		echo $ending;
}
?>
