<?php
include('../config.php');
include(mnminclude.'link.php');
include(mnminclude.'tags.php');

header("Content-Type: text/html");
ob_end_flush();

$links = $db->get_results("SELECT link_id  from links where link_status != 'discard' ");
	if ($links) {
		foreach($links as $dblink) {
			echo $dblink->link_id . "<br>\n";
			$link = new Link;
			$link->id=$dblink->link_id;
			$link->read();
			if (!empty($link->tags)) tags_insert_string($link->id, $dblang, $link->tags, $link->date);
	}
	echo "--------------------------\n";
}
