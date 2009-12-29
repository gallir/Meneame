<?php
include('../config.php');
include(mnminclude.'link.php');
include(mnminclude.'trackback.php');

$remote = $_SERVER["REMOTE_ADDR"];
$local_ips = gethostbynamel($_SERVER["SERVER_NAME"]);

if(!isPrivateIP($remote)  && !in_array($remote, $local_ips)) {
	syslog(LOG_NOTICE, "Meneame: send_pingback remote address $_SERVER[REMOTE_ADDR] is no local address ($_SERVER[SERVER_ADDR]).");
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

// Send the trackback for the main link
if (($tbs = $db->get_col("select trackback_id from trackbacks where trackback_link_id = $link->id and trackback_status='pendent'"))) {
	foreach ($tbs as $tb_id) {
		$tb = new Trackback;
		$tb->id = $tb_id;
		if ($tb->read()) {
			$res = $tb->send($link);
		}
	}
}

// Send pingbacks for link inside the text
preg_match_all('/([\(\[:\.\s]|^)(https*:\/\/[^ \t\n\r\]\(\)\&]{5,70}[^ \t\n\r\]\(\)]*[^ .\t,\n\r\(\)\"\'\]\?])/i', $link->content, $matches);
foreach ($matches[2] as $match) {
	$tb = new Trackback;
	$tb->link=clean_input_url($match);
	$tb->link_id=$link->id;
	$tb->author=$link->author;
	if(!$tb->read()) {
		$tmp = new Link;
		if(!$tmp->get($match, 2000, false)) {
			echo "couldn't get $match\n";
			next;
		}
		if(!$tmp->pingback()) {
			echo "couldn't get pingback $match\n";
			next;
		}
		$tb->link = clean_input_url($match);
		$tb->url = clean_input_url($tmp->trackback);
		$tb->send($link);
	}
}
?>
