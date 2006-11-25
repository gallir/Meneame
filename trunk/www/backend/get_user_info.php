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
include_once(mnminclude.'user.php');


if (empty($_GET['id'])) die;
$id = intval($_GET['id']);
$user = new User;
$user->id=$id;
if (! $user->read()) die;
if ($user->avatar) 
	echo '<img align="left" hspace="4" src="'.get_avatar_url($user->id, $user->avatar, 80).'" width="80" height="80" alt="'.$user->username.'"/>';
echo '<strong>' . _('usuario') . ':</strong>&nbsp;' . $user->username . '<br/>';
echo '<strong>' . _('nombre') . ':</strong>&nbsp;' . $user->names . '<br/>';
echo '<strong>' . _('web') . ':</strong>&nbsp;' . $user->url . '<br/>';
echo '<strong>' . _('karma') . ':</strong>&nbsp;' . $user->karma . '<br/>';
echo '<strong>' . _('desde') . ':</strong>&nbsp;' . get_date($user->date) . '<br/>';
?>
