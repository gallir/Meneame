<?php
include('../config.php');
include(mnminclude.'link.php');
include(mnminclude.'trackback.php');


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

preg_match_all('/([\(\[:\.\s]|^)(https*:\/\/[^ \t\n\r\]\(\)\&]{5,70}[^ \t\n\r\]\(\)]*[^ .\t,\n\r\(\)\"\'\]\?])/i', $link->content, $matches);
foreach ($matches[2] as $match) {
	$tb = new Trackback;
	$tb->link=clean_input_url($match);
	$tb->link_id=$link->id;
	$tb->author=$link->author;
	if(!$tb->read()) {
		echo "No está $match\n";
		$tmp = new Link;
		if(!$tmp->get($match, 2000)) {
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
		sleep (1);
	} else {
		next;
	}
}
?>
