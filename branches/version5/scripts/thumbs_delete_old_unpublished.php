#! /usr/bin/env php
<?php
include('../config.php');
global $_SERVER;

if (isset($argv[1])) {
	$procs = $argv[1];
} else {
	$procs = 2;
}

$pids = array();
// Check which hostname server we run for, for example: mnm, emnm, etc.

$sql = "select link_id from links, media where type='link' and id = link_id and link_status != 'published' and link_date < date_sub(now(), interval 30 day)";

$db = get_db();
if ( ($res = $db->get_col($sql))) {
	$db->close();
	foreach ($res as $id) {
		$pid = pcntl_fork();
		if ($pid < 0) {
			echo "Error in fork\n";
		} else if ($pid == 0) {
			$db = get_db();
			$link = Link::from_db($id, null, false);
			$thumb = $link->has_thumb();

			if ($thumb && $link->status != 'published' && $link->date < time() - 86400*10) {
				$link->delete_thumb();
				echo "$id: deleted\n";
			}
			exit(0);
		} else {
			check_wait($pid);
		}
	}
	$db = get_db();
}

function check_wait($pid) {
	global $pids, $procs;

	array_push($pids, $pid);
	if (count($pids) > $procs) {
		$pid = pcntl_wait($status);
		array_remove($pid, $pids);
	}
}

function get_db() {
	global $globals;

    $db = new RGDB($globals['db_user'], $globals['db_password'], $globals['db_name'], $globals['db_server'], true);
    $db->persistent = false;
    return $db;
 }

function array_remove ($val, $array) {
	if(($key = array_search($val, $array)) !== false) {
		unset($array[$key]);
	}
}
