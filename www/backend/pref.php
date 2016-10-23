<?php
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail.com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include(__DIR__.'/../config.php');

if (! check_security_key($_POST['control_key'])) {
	die;
}

$user = intval($_POST['id']);
$key = $_POST['key'];
$value = intval($_POST['value']);


if (!$value) $value = false;

if (! $user || $user != $current_user->user_id) die;
if (empty($key)) die;

if (!empty($_POST['set'])) {
	$value = intval($_POST['value']);
	if (User::set_pref($user, $key, $value)) {
		$res = $value;
	} else {
		$res = false;
	}
} else {
	$res = User::get_pref($user, $key, $value);
}

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($res);
