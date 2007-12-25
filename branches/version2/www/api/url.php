<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

header('Content-Type: text/plain; charset=UTF-8');

$url = $db->escape($_GET['url']);
$url = addcslashes($url, '%_');
if(strlen($url) < 8) {
	echo 'KO';
	die;
}
$url = preg_replace('/\/$/', '', $url);
$all = intval($_GET['all']);
if ($all == '1') {
    $links = $db->get_results("select SQL_NO_CACHE link_id, link_votes, link_anonymous, link_status from links where link_url like '$url%' order by link_date DESC");
} else {
    $links = $db->get_results("select SQL_NO_CACHE link_id, link_votes, link_anonymous, link_status from links where link_url in ('$url', '$url/')");
}
if ($links) {
	foreach ($links as $dblink) {
	    echo 'OK http://'.get_server_name().'/story.php?id='.$dblink->link_id.' '.($dblink->link_votes+$dblink->link_anonymous).' '.$dblink->link_status."\n";
	}
} else {
	echo 'KO http://'.get_server_name().'/submit.php?url='.$url;
}
?>
