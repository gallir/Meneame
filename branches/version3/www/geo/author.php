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
include_once(mnminclude.'geo.php');


if (empty($_GET['id'])) die;
$id = intval($_GET['id']);
$user = new User;
$user->id=$id;
if (! $user->read()) die;
echo '<div style="font-size:8.5pt;width:280px; margin-right:15px; overflow:hidden">';
if ($user->avatar) 
	echo '<div style="float: left;"><img hspace="4" src="'.get_avatar_url($user->id, $user->avatar, 40).'" width="40" height="40" alt="'.$user->username.'"/></div>';
echo '<strong>' . _('usuario') . ':</strong>&nbsp;<a target="_blank" href="' . get_user_uri($user->username) .'">'. $user->username.'</a>';
if ($current_user->user_id > 0 && $current_user->user_id  != $user->id)  {
	echo '&nbsp;' . friend_teaser($current_user->user_id, $user->id);
}
echo '<br/>';
if ($user->names) echo '<strong>' . _('nombre') . ':</strong>&nbsp;' . $user->names . '<br/>';
if ($user->url) echo '<strong>' . _('web') . ':</strong>&nbsp;' . $user->url . '<br/>';
echo '<strong>' . _('karma') . ':</strong>&nbsp;' . $user->karma . '<br/>';
echo '<strong>' . _('desde') . ':</strong>&nbsp;' . get_date($user->date) . '<br/>';
if ($current_user->user_id > 0 && $current_user->user_id != $user->id && ($her_latlng = geo_latlng('user', $user->id)) && ($my_latlng = geo_latlng('user', $current_user->user_id))) {
	$distance = (int) geo_distance($my_latlng, $her_latlng);
	echo '<strong>'._('distancia') . ':</strong>&nbsp;' . $distance . '&nbsp;kms<br/>';
}
echo '</div>';

?>
