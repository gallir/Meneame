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

global $db, $current_user;

if (!isset($_GET['id']) && !isset($prefered_id)) die;
if (!empty($_GET['id'])) $prefered_id = intval($_GET['id']);

if (empty($_GET['p'])) $prefered_page = 1;
else $prefered_page = intval($_GET['p']);

if (!isset($prefered_type) && !empty($_GET['type'])) $prefered_type = $_GET['type'];

$prefered_page_size = 20;
$prefered_offset=($prefered_page-1)*$prefered_page_size;
switch ($prefered_type) {
	case 'from':
		if ($prefered_id != $current_user->user_id) {
			$friend_value = 'AND friend_value > 0';
		} else {
			$friend_value = '';
		}
		$prefered_total= $db->get_var("SELECT count(*) FROM friends WHERE friend_type='manual' AND friend_from=$prefered_id $friend_value");
		$dbusers = $db->get_results("SELECT friend_to as who FROM friends, users WHERE friend_type='manual' AND friend_from=$prefered_id and user_id = friend_to $friend_value order by user_login asc LIMIT $prefered_offset,$prefered_page_size");
		break;
	case 'to':
		$prefered_total= $db->get_var("SELECT count(*) FROM friends WHERE friend_type='manual' AND friend_to=$prefered_id AND friend_from != 0 and friend_value > 0");
		$dbusers = $db->get_results("SELECT friend_from as who FROM friends, users WHERE friend_type='manual' AND friend_to=$prefered_id and user_id = friend_from and friend_value > 0 order by user_login asc LIMIT $prefered_offset,$prefered_page_size");
		break;
}
if ($dbusers) {
	$friend = new User;
	foreach($dbusers as $dbuser) {
		$friend->id=$dbuser->who;
		$friend->read();
		echo '<div class="friends-item">';
		echo '<a href="'.get_user_uri($friend->username).'" title="'.$friend->username.'">';
		echo '<img src="'.get_avatar_url($friend->id, $friend->avatar, 20).'" width="20" height="20" alt="'.$friend->username.'"/>';
		echo $friend->username.'</a>&nbsp;';
		if ($current_user->user_id > 0 && $current_user->user_id != $friend->id) {
			echo '<a id="friend-'.$prefered_type.'-'.$current_user->user_id.'-'.$friend->id.'" href="javascript:get_votes(\'get_friend.php\',\''.$current_user->user_id.'\',\'friend-'.$prefered_type.'-'.$current_user->user_id.'-'.$friend->id.'\',0,\''.$friend->id.'\')">'.friend_teaser($current_user->user_id, $friend->id).'</a>';
		}
		echo '</div>';
		echo "\n";
	}
	do_contained_pages($prefered_id, $prefered_total, $prefered_page, $prefered_page_size, 'get_friends_bars.php', $prefered_type, $prefered_type.'-container');
}
?>
