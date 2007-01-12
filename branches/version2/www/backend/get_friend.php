<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'user.php');

header('Content-Type: text/plain; charset=UTF-8');

if(!($to=intval($_REQUEST['id']))) {
	error(_('falta el código de usuario'));
}

if(!($src = intval($_REQUEST['type']))) {
	error(_('falta el código de usuario'));
}

if ($src != $current_user->user_id) {
	error(_('usuario incorrecto'). " ($src, $current_user->user_id)");
}

echo friend_add_delete($src, $to);

function error($mess) {
	echo "ERROR: $mess\n";
	die;
}

?>
