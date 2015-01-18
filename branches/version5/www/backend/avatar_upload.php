<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'avatars.php');

//header('Content-Type: application/json; charset=UTF-8');
header('Content-Type: text/plain; charset=UTF-8'); // It's being read in a iframe son can't be a json to avoid problems

array_push($globals['cache-control'], 'no-cache');
http_cache();

if (!$current_user->user_id) json_error(_('usuario no autentificado'));

$user = new User($current_user->user_id);

$error = false;
// Manage avatars upload
if (!empty($_FILES['image']['tmp_name']) ) {
	if (avatars_check_upload_size('image')) {
		$avatar_mtime = avatars_manage_upload($user->id, 'image');
		if (!$avatar_mtime) {
			$error = _('error guardando la imagen');
		}
	} else {
		$error = _('el tamaño de la imagen excede el límite');
	}
} else {
	$error =_('datos recibidos incorrectamente');
}

if ($avatar_mtime) {
	$user->avatar = $avatar_mtime;
	$user->store();
}
$dict['avatar_url'] = get_avatar_url($user->id, $user->avatar, 80);
$dict['error'] = $error;
echo json_encode($dict);


function json_error($mess) {
	syslog(LOG_INFO, "Meneame: avatar $mess");
	$dict['error'] = $mess;
	echo json_encode($dict);
	die;
}
