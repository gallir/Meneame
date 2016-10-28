<?php
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include(__DIR__.'/../config.php');
include(mnminclude.'avatars.php');

header('Content-Type: application/json; charset=UTF-8');

array_push($globals['cache-control'], 'no-cache');
http_cache();

$r = new stdClass();
$headers = request_headers();

if ( ! $current_user->user_id) {
		die;
}

// If the header is available, chech the size
if (isset($headers['X-File-Size'])
		&& $headers['X-File-Size'] > 0
		&& Upload::current_user_limit_exceded($headers['X-File-Size']) ) {
		$r->error = _("Límite de ficheros excedidos");
		syslog(LOG_INFO, "File size exceeded ".$headers['X-File-Size']);
		echo json_encode($r);
		die;
}

$dir = Upload::get_cache_dir() . '/tmp';
if (! file_exists($dir)) {
	$old_mask = umask(0);
	$res = @mkdir($dir, 0777, true);
	umask($old_mask);
}

$source = file_get_contents('php://input');
if (Upload::current_user_limit_exceded(strlen($source)) ) {
		$r->error = _("Límite de ficheros excedidos");
		echo json_encode($r);
		die;
}

// Delete old files first
$older = time() - 1800;
$iterator = new DirectoryIterator($dir);
foreach ($iterator as $fileinfo) {
	if ($fileinfo->isFile()) {
		if ($fileinfo->getMTime() < $older) {
			@unlink($fileinfo->getPathname());
		}
	}
}

$tmpfile =  $dir . '/' . $current_user->user_id . '-' . $current_user->user_login . '-' . uniqid();

if (file_put_contents($tmpfile, $source)) {
	$info = getimagesize($tmpfile);
	if (! $info) {
		@unlink($tmpfile);
		$r->error = _('imagen no soportada');
		echo json_encode($r);
		die;
	}
	$ext = image_type_to_extension($info[2]);
	$uploadfile =  $tmpfile . $ext;

	@rename($tmpfile, $uploadfile);

	require_once(mnminclude."simpleimage.php");

	$image = new SimpleImage();
	if ($image->rotate_exif($uploadfile)) {
		$image->save($uploadfile);
	}


	$r->type = $info['mime'];
	$r->name = basename($uploadfile);
	$r->url = $globals['base_static'].Upload::get_cache_relative_dir().'/tmp/'.$r->name;
	$r->thumb = $globals['base_static'].Upload::get_cache_relative_dir()."/tmp/tmp_thumb-$r->name";


	// Creates the thumbnail
	$thumb = new SimpleImage();
	$thumb->load($uploadfile);
	$thumb->resize($globals['media_thumb_size'], $globals['media_thumb_size'], true);
	$thumb->save(dirname($uploadfile)."/tmp_thumb-$r->name", -1);

	echo json_encode($r);
}

