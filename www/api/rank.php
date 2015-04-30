<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

stats_increment('api', true);
	
if(!empty($_REQUEST['rows'])) {
	$rows = intval($_REQUEST['rows']);
	if ($rows > 3000) $rows = 3000; //avoid abuses
} else $rows = 200;
	

if(!empty($_REQUEST['days']) && intval($_REQUEST['days'] <= 90))
	$days = intval($_REQUEST['days']);
else $days = 7;

//$sql = "SELECT link_id, count(*) as votes FROM votes, links WHERE  ";	
//$sql .= "vote_type='links' AND vote_date > DATE_SUB(now(), INTERVAL $days DAY) AND ";
//$sql .= "vote_link_id=link_id  AND link_status != 'discard' GROUP BY vote_link_id  ORDER BY votes DESC LIMIT $rows";

$sql = "SELECT link_id, link_url, link_votes, link_anonymous, link_negatives, link_karma from links WHERE link_date > DATE_SUB(now(), INTERVAL $days DAY) AND link_status != 'discard' ORDER BY link_karma DESC, link_votes DESC LIMIT $rows";
$link = new Link;
$links = $db->get_results($sql);
if ($links) {
	header('Content-Type: text/plain');
	foreach($links as $link) {
		echo "$link->link_url\t".($link->link_votes+$link->link_anonymous)."\t$link->link_negatives\t$link->link_karma\n";
	}
}
?>
