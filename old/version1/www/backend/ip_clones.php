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
}

global $db, $globals;

$user_id = intval($_GET['id']);
$ip_pattern = $db->escape($_GET['type']);
if (!$user_id > 0 || empty($ip_pattern) || $current_user->user_level != 'god')  die;

$row = $db->get_row("select vote_date as date, vote_ip as ip from votes, users where vote_type='links' and vote_user_id=$user_id and vote_ip like '$ip_pattern.%' order by vote_date desc limit 1");

if (! $row) {
	// Try with comments
	$row = $db->get_var("select comment_date as date, comment_ip as ip from comments, users where comment_user_id=$user_id and comment_ip like '$ip_pattern.%' order by comment_date desc limit 1");
}

if (! $row) {
	echo _('no se ha encontrado la IP');
	exit;
}

$date = $row->date;
$ip = $row->ip;

$clones = $db->get_col("select distinct user_login from votes, users where vote_type='links' and (vote_date > date_sub('$date', interval 7 day) and vote_date < date_add('$date', interval 7 day)) and vote_ip = '$ip' and vote_user_id != $user_id and user_id = vote_user_id and user_level != 'god' and user_level != 'admin'");

if (! $clones) {
	$clones = $db->get_col("select distinct user_login from comments, users where (comment_date > date_sub('$date', interval 7 day) and comment_date < date_add('$date', interval 7 day)) and comment_ip = '$ip' and comment_user_id != $user_id and user_id = comment_user_id and user_level != 'god' and user_level != 'admin'");
}


if (! $clones) {
	echo _('no se han encontrado clones');
	exit;
}

foreach ($clones as $clone) {
	$user = urlencode($clone);
	echo '<a href="'.get_user_uri($user)."\">$user</a>&nbsp;&nbsp; ";
}
?>
