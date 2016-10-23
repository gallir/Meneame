<?php
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include(__DIR__.'/../config.php');
//include(mnminclude.'geo.php');
stats_increment('ajax');

$id = (int) $_REQUEST['id'];

if (!empty($_GET["mapplet"])) $limit = "limit 100";

$from = (int) $_REQUEST['from'];
if ($from <= 0 || $from > 240) $from = 24;

$type = $_REQUEST['type'];
if ($type != 'link' && $type != 'comment' && $type != 'user' && $type != 'author' && $type != 'post') $type = 'link';

$status = $_REQUEST['status'];
if (!empty($status) && $status != 'published' && $status != 'all' && $status != 'queued') $status = false;

switch ($type) {
	case 'link':
		if ($id > 0) $cond = add_cond($cond, "link_id = $id");
		else {
			if ($from > 0) {
				$cond = add_cond($cond, "link_date > date_sub(now(), interval $from hour)");
			}
			if ($status) $cond = add_cond($cond, "link_status = '$status'");
		}
		$res = $db->get_results("select link_id as id, link_status as status, X(geo_pt) as lat, Y(geo_pt) as lng from links, geo_links where $cond and geo_id = link_id $limit");
		break;

	case 'author':
		if ($id > 0) $cond = add_cond($cond, "link_id = $id");
		else {
			$cond = add_cond($cond, "link_date > date_sub(now(), interval $from hour)");
			if ($status) $cond = add_cond($cond, "link_status = '$status'");
		}
		$res = $db->get_results("select distinct link_author as id, 'user' as status, X(geo_pt) as lat, Y(geo_pt) as lng from links, geo_users where $cond and geo_id = link_author $limit");
		break;

	case 'post':
		if ($id > 0) $cond = add_cond($cond, "post_id = $id");
		if ($from > 0) $cond = add_cond($cond, "post_date > date_sub(now(), interval $from hour)");
		$res = $db->get_results("select post_id as id, 'post' as status, X(geo_pt) as lat, Y(geo_pt) as lng from posts, geo_users where $cond and geo_id = post_user_id $limit");
		break;

}


header('Content-Type: text/xml; charset=UTF-8');

echo '<?xml version="1.0" encoding="utf-8"?>'. "\n";
echo "<markers>\n";

if ($res) {
	foreach ($res as $item) {
		echo "<marker lat='$item->lat' lng='$item->lng' id='$item->id' status='$item->status'/>\n";
	}
}
echo "</markers>\n";

function add_cond($cond, $new) {
	if (empty($cond)) $cond = $new;
	else $cond .= " and $new";
	return $cond;
}
?>
