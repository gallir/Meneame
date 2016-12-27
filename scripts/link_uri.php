<?php
include('../config.php');
include(mnminclude.'uri.php');
include(mnminclude.'link.php');

header("Content-Type: text/plain");

$link = new Link;
$ids = $db->get_results("SELECT link_id from links where link_uri is null order by link_id");
foreach($ids as $dbid) {
	$link->id = $dbid->link_id;
	$link->read();
	if (!empty($link->title)) {
		$link->get_uri();
		echo "$link->title -> $link->uri\n";
		$link->store();
	}
}
