<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'geo.php');

header('Content-Type: text/plain; charset=UTF-8');

if(!($id=intval($_REQUEST['id']))) {
	error(_('falta el id'). " $link");
}

$type = $_REQUEST['type'];

if ($type == 'user') {
	if ($id != $current_user->user_id) {
		error(_('usuario incorrecto'));
	}
} else {
	error(_('tipo incorrecto'));
}

if(geo_delete($type, $id)) {
	echo "OK";
} else {
	error(_('borrado anteriormente'));
}


function error($mess) {
	echo "ERROR: $mess\n";
	die;
}

?>
