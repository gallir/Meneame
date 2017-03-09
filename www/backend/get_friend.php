<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include(__DIR__.'/../config.php');

header('Content-Type: text/plain; charset=UTF-8');

if (!$current_user->user_id) {
    die('ERROR: '._('usuario incorrecto'));
}

if (!($to = (int)$_REQUEST['id'])) {
    die('ERROR: '._('falta el código de usuario'));
}

if (!check_security_key($_REQUEST['key'])) {
    die('ERROR: '._('clave de control incorrecta'));
}

switch ($_REQUEST['value']) {
    case '0':
        die((string)User::friend_delete($current_user->user_id, $to));

    case '1':
        die((string)User::friend_insert($current_user->user_id, $to, 1));

    case '-1':
        die((string)User::friend_insert($current_user->user_id, $to, -1));

    default:
        die('ERROR: '._('opción incorrecta'));
}
