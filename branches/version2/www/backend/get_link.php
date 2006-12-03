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
echo '<strong>' . $link->title . '</strong><br/>';
if ($link->avatar) {
	echo '<img hspace="2" src="'.get_avatar_url($link->author, $link->avatar, 20).'" width="20" height="20" alt="avatar"/>';
}
echo '<strong>' . $link->username . '</strong><br/>';
echo text_to_html($link->content);
?>
