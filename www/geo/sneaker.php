<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

stats_increment('ajax');

$time = (int) $_GET['time'];
if($time <= 0 || $globals['now']-$time > 900) {
	$time = $globals['now']-180;
}
$dbtime = date("YmdHis", $time);
$max_items = (int) $_GET['items'];
if ($max_items < 1 || $max_items > 100) {
	$max_items = 50; // Avoid abuse
}

header('Content-Type: application/json; charset=utf-8');

// Get the logs
$logs = $db->get_results("select UNIX_TIMESTAMP(log_date) as time, log_type, log_ref_id, log_user_id from logs where log_date > $dbtime order by log_date desc limit $max_items");

$timestamp = 0;
$data['items'] = array();
if ($logs) {
	foreach ($logs as $log) {
		if ($log->time > $timestamp) $timestamp = $log->time;
		switch ($log->log_type) {
			case 'link_new':
			case 'link_publish':
				$item = $db->get_row("select link_id as id, link_status as status, X(geo_pt) as lat, Y(geo_pt) as lng from links, geo_users where link_id = $log->log_ref_id and geo_id = $log->log_user_id");
				if ($item) push_event($item, $log->time, 'link');
				break;
			case 'link_geo_edit':
				$item = $db->get_row("select link_id as id, link_status as status, X(geo_pt) as lat, Y(geo_pt) as lng from links, geo_links where link_id = $log->log_ref_id and geo_id = $log->log_ref_id");
				if ($item) push_event($item, $log->time, 'link','geo_edit');
				break;
			case 'comment_new':
				$item = $db->get_row("select comment_id as id, X(geo_pt) as lat, Y(geo_pt) as lng from comments, geo_users where comment_id = $log->log_ref_id and geo_id = comment_user_id");
				if ($item) push_event($item, $log->time, 'comment');
				break;
			case 'post_new':
				$item = $db->get_row("select post_id as id, X(geo_pt) as lat, Y(geo_pt) as lng from posts, geo_users where post_id = $log->log_ref_id and geo_id = post_user_id");
				if ($item) push_event($item, $log->time, 'post');
				break;
		}
	}
}
$data['ts'] = max($timestamp,$time);
echo json_encode($data);

function push_event($item, $time, $type, $event='') {
	global $data;
	$item->time = $time;
	$item->type = $type;
	$item->evt = $event;
	array_push($data['items'], $item);
}

?>
