<?php
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
	header('Cache-Control: public, s-maxage=300');
}
include_once(mnminclude.'geo.php');


if (empty($_GET['id'])) die;
$id = intval($_GET['id']);
$user = new User;
if ($id > 0) {
	$user->id=$id;
} else {
	$user->username = $_GET['id'];
}
if (! $user->read()) {
	echo _('usuario inexistente');
	die;
}
if ($user->avatar)
	echo '<div style="float: left;"><img class="avatar big" style="margin-right: 5px" src="'.get_avatar_url($user->id, $user->avatar, 80).'" width="80" height="80" alt="'.$user->username.'"/></div>';
echo '<strong>' . _('usuario') . ':</strong>&nbsp;' . $user->username;

$user->print_medals();

if ($current_user->user_id > 0 && $current_user->user_id  != $user->id)  {
	echo '&nbsp;' . User::friend_teaser($current_user->user_id, $user->id);
}
echo '<br/>';
if ($user->names) echo '<strong>' . _('nombre') . ':</strong>&nbsp;' . $user->names . '<br/>';
if ($user->url) echo '<strong>' . _('web') . ':</strong>&nbsp;' . $user->url . '<br/>';
echo '<strong>' . _('karma') . ':</strong>&nbsp;' . $user->karma . '<br/>';
/*
echo '<strong>' . _('ranking') . ':</strong>&nbsp;#' . $user->ranking() . '<br/>';
*/
echo '<strong>' . _('desde') . ':</strong>&nbsp;' . get_date($user->date) . '<br/>';
if ($user->total_images > 0) {
	echo '<strong>' . _('imágenes') . ':</strong>&nbsp;' . $user->total_images . '<br/>';
}
if ($globals['google_maps_api'] && $current_user->user_id > 0 && $current_user->user_id != $user->id && ($her_latlng = geo_latlng('user', $user->id)) && ($my_latlng = geo_latlng('user', $current_user->user_id))) {
	$distance = (int) geo_distance($my_latlng, $her_latlng);
	echo '<strong>'._('distancia') . ':</strong>&nbsp;' . $distance . '&nbsp;kms<br/>';
}

if ($user->bio) {
	echo '<br clear="left"><strong>'._('bio').'</strong>:<br/>';
	echo text_to_html($user->bio);
}

/*
$post = new Post;
if ($post->read_last($user->id)) {
	echo '<br clear="left"><strong>'._('última nota').'</strong>:<br/>';
	$post->show_avatar = false;
	$post->print_text(150);
}
*/
