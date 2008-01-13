<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and 
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>
if (! defined('mnmpath')) {
	include_once('../config.php');
	header('Content-Type: text/html; charset=utf-8');
	stats_increment('ajax');
}
include_once(mnminclude.'user.php');
include_once('pager.php');

global $db;

if (!isset($_GET['id']) && !isset($prefered_id)) die;
if (!empty($_GET['id'])) $prefered_id = intval($_GET['id']);

if (empty($_GET['p'])) $prefered_page = 1;
else $prefered_page = intval($_GET['p']);

if (!isset($prefered_type) && !empty($_GET['type'])) $prefered_type = $_GET['type'];

$prefered_page_size = 20;
$prefered_offset=($prefered_page-1)*$prefered_page_size;
switch ($prefered_type) {
	case 'friends':
		$prefered_total= $db->get_var("SELECT count(*) FROM friends WHERE friend_type='affiliate' AND friend_from=$prefered_id AND friend_to !=0");
		$dbusers = $db->get_results("SELECT friend_to as who, friend_value FROM friends WHERE friend_type='affiliate' AND friend_from=$prefered_id AND friend_to !=0 ORDER BY friend_value DESC LIMIT $prefered_offset,$prefered_page_size");
		break;
	case 'voters':
		$prefered_total= $db->get_var("SELECT count(*) FROM friends WHERE friend_type='affiliate' AND friend_to=$prefered_id AND friend_from !=0");
		$dbusers = $db->get_results("SELECT friend_from as who, friend_value FROM friends WHERE friend_type='affiliate' AND friend_to=$prefered_id AND friend_from !=0 ORDER BY friend_value DESC LIMIT $prefered_offset,$prefered_page_size");
		break;
}
if ($dbusers) {
	$friend = new User;
	echo '<div class="voters-list">';
	foreach($dbusers as $dbuser) {
		$friend->id=$dbuser->who;
		$value = $dbuser->friend_value * 100;
		$value = sprintf("%6.2f", $value);
		$friend->read();
		echo '<div class="item">';
		echo '<a href="'.get_user_uri($friend->username).'" title="'.$value.' %">';
		echo '<img src="'.get_avatar_url($friend->id, $friend->avatar, 20).'" width="20" height="20" alt="'.$friend->username.'"/>';
		echo $friend->username.'</a>';
		echo '</div>';
		echo "\n";
	}
	echo '</div>';
	do_contained_pages($prefered_id, $prefered_total, $prefered_page, $prefered_page_size, 'get_prefered.php', $prefered_type, $prefered_type.'-container');
}

?>
