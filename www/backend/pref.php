<?php
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail.com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once __DIR__.'/../config.php';

if (!check_security_key($_POST['control_key'])) {
    die;
}

$user = intval($_POST['id']);
$key = $_POST['key'];
$value = intval($_POST['value']) ?: false;

if (empty($key) || empty($user) || ($user != $current_user->user_id)) {
    die;
}

if (empty($_POST['set'])) {
    $res = User::get_pref($user, $key, $value);
} else {
    $res = intval($_POST['value']);
    $res = User::set_pref($user, $key, $res) ? $res : false;
}

header('Content-Type: application/json; charset=UTF-8');
die(json_encode($res));
