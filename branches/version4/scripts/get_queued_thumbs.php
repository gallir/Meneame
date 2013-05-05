#! /usr/bin/env php
<?
include('../config.php');
include(mnminclude.'webimages.php');


$sql = "select link_id from links where link_status='queued' and link_thumb_status = 'unknown' and link_date > date_sub(now(), interval 8 hour) and link_date < date_sub(now(), interval 3 minute) and link_votes > 2 and link_votes > link_negatives * 5 order by link_date desc limit 10";

$res = $db->get_col($sql);
foreach ($res as $l) {
	$link = new Link();
	$link->id = $l;
	$link->read();
	//echo "$l $link->title URL: $link->url<br>";
	echo $link->get_permalink().": ";
	if (($thumb = $link->get_thumb())) {
		echo $thumb;
	} else {
		echo "NO thumb";
	}
	echo "\n";
	ob_flush(); flush();
}


?>
