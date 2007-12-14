<?php
include('../config.php');
include(mnminclude.'link.php');
include_once(mnminclude.'lucene.php');

ini_set('memory_limit', '128M');
//echo  ini_get('memory_limit'). "\n";
ini_set('max_execution_time', '180');
//echo  ini_get('max_execution_time'). "\n";


if($_SERVER["SERVER_ADDR"] != $_SERVER["REMOTE_ADDR"]) {
	syslog(LOG_NOTICE, "Menéame: Remote address $_SERVER[REMOTE_ADDR] is no local address ($_SERVER[SERVER_ADDR]).");
	echo "ein? $_SERVER[REMOTE_ADDR]\n";
	die;
}

$linkid = (int) $_REQUEST['id'];
if ($linkid <= 0) {
	echo "no id";
	die;
}

$link = new Link;
$link->id = $linkid;
if (!$link->read()) {
	echo "error reading link\n";
	die;
}

$link->lucene_update();
echo "ok\n";

?>
