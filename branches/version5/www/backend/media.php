<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
include_once('../config.php');

$type = $db->escape($_GET['type']);
$id = intval($_GET['id']);
if (empty($_GET['version'])) $version = 0;
else $version = intval($_GET['version']);

if (empty($type) || ! $id ) not_found();

$media = new Upload($type, $id, $version);

/* The user requests to delete the image */
if (! empty($_REQUEST['op'])) {
	if ($_REQUEST['op'] == 'delete') {
		delete_image($media);
		exit(0);
	}
}

if (! $media->read()) not_found();

if (! $globals['media_public'] && $media->access == 'restricted' && ! $current_user->user_id > 0) {
	error_image(_('Debe estar autentificado'));
	die;
} elseif ($globals['bot']
		|| ($media->type == 'private' 
			&& ($current_user->user_id <= 0 || ($media->user != $current_user->user_id && $media->to != $current_user->user_id))) ) {
	error_image(_('No está autorizado'));
	die;
}

header("Content-Type: $media->mime");
header('Last-Modified: ' . date('r', $media->date));
header('Cache-Control: max-age=3600');

if (!empty($media->mime)) {
	$ext = explode("/", $media->mime);
	$ext = $ext[count($ext)-1];
}
header("Content-Disposition: filename=meneame-media-$type-$id." . $ext);

if ($media->file_exists() && ! empty($globals['xsendfile'])) {
	/* Be careful with privacy and rules in the server
	* Good rule for nginx:
    	location ~ \.media$ {
        	internal;
    	}
	*/
	header($globals['xsendfile'].': '.$media->url());
	die;
}

header("Content-Length: " .$media->filesize());
$media->readfile();
exit(0);

function error_image($message) {
	header("HTTP/1.0 403 Not authorized");
	header("Content-type: image/png");
	header('Cache-Control: max-age=10, must-revalidate');
	header('Expires: ' . date('r', time()+10));
	readfile(mnmpath.'/img/common/access_denied-01.png');
	die;
}

function delete_image($media) {
	global $current_user, $globals;

	$r = array();

	if (! $media->read()) {
		$r['ok'] = 0;
		$r['text'] = _('imagen no existente');
	} elseif (! $current_user->user_id > 0 || $media->user != $current_user->user_id) {
		$r['ok'] = 0;
		$r['text'] = _('no autorizado');
	} else {
		$media->delete();
		$r['ok'] = 1;
		$r['text'] = _('imagen eliminada');
	}
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($r);
	exit(0);
}

