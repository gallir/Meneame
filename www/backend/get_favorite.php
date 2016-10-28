<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include(__DIR__.'/../config.php');
include(mnminclude.'favorites.php');

header('Content-Type: application/json; charset=UTF-8');

if(!($id=intval($_REQUEST['id']))) {
	error(_('falta el ID'). " $id");
}

if(empty($_REQUEST['type'])) {
	error(_('falta el tipo'));
}
$type = $_REQUEST['type'];

if(!($user = intval($_REQUEST['user']))) {
	error(_('falta el código de usuario'));
}

if ($user != $current_user->user_id) {
	error(_('usuario incorrecto'));
}

if (! check_security_key($_REQUEST['key'])) {
	error(_('clave de control incorrecta'));
}

$dict['value'] = favorite_add_delete($user, $id, $type);
echo json_encode($dict);

function error($mess) {
	$dict['error'] = $mess;
	echo json_encode($dict);
    die;
}

