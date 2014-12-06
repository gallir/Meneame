#! /usr/bin/env php
<?php
include(dirname(__FILE__).'/../config.php');
include(mnminclude.'webimages.php');


$sql = "select link_id from links where link_status='queued' and link_thumb_status = 'unknown' and link_date > date_sub(now(), interval 8 hour) and link_date < date_sub(now(), interval 1 minute) and link_votes > 1 and link_votes > link_negatives * 10 order by link_date desc limit 10";

$res = $db->get_col($sql);
foreach ($res as $l) {
	$link = Link::from_db($l, null, false);
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
