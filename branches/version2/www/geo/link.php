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
echo '<div style="width:250px; margin-right:15px">';
if ($link->avatar) {
	echo '<img src="'.get_avatar_url($link->author, $link->avatar, 40).'" width="40" height="40" alt="avatar"  style="float:left; margin: 0 5px 4px 0;"/>';
}
echo '<a href="'.$link->get_permalink().'" target="_new"><strong>'.$link->title.'</strong></a><br clear="all"/>';
echo _('en').'&nbsp;: '.$link->meta_name.', '.$link->category_name.' | '._('comentarios').':&nbsp;'.$link->comments.' | karma:&nbsp;'. intval($link->karma). ' | '._('negativos').':&nbsp;'. $link->negatives;
echo '</div>';
?>
