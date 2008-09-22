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
include_once(mnminclude.'link.php');


if (empty($_GET['id'])) die;
$id = intval($_GET['id']);
$link = new Link;
$link->id=$id;
$link->read();
if(!$link->read) die;
$user_login = $db->get_var("select user_login from users where user_id = $link->author");
echo '<p>';
if ($link->avatar) {
	echo '<img src="'.get_avatar_url($link->author, $link->avatar, 40).'" width="40" height="40" alt="avatar"  style="float:left; margin: 0 5px 0 0;"/>';
}
echo '<strong>' . $link->title . '</strong><br/>';
echo _('por').'&nbsp;<strong>' . $user_login . '</strong><br/>';
echo _('en').'&nbsp;'.$link->meta_name.', '.$link->category_name.'&nbsp;|&nbsp;karma:&nbsp;'. intval($link->karma). '&nbsp;|&nbsp;'._('negativos').':&nbsp;'. $link->negatives. '</p>';
echo text_to_html($link->content);
?>
