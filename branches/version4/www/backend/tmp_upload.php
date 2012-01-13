<?
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'avatars.php');

header('Content-Type: application/json; charset=UTF-8');

array_push($globals['cache-control'], 'no-cache');
http_cache();

$error = false;
$field = 'tmp_file';
$r = new stdClass();


$headers = request_headers();

if ( ! $current_user->user_id
	|| empty($headers['X-File-Type']) ) {
		$r->error = _('Error en cabeceras');
		syslog(LOG_INFO, "Menáme, $r->error " . $headers['X-File-Type'] . " - " . $headers['X-File-Size'] . $headers['x-file-type']);
		echo json_encode($r);
		die;
}

if (Upload::current_user_limit_exceded($headers['X-File-Size']) ) {
		$r->error = _("Límite de ficheros excedidos");
		syslog(LOG_INFO, "Error $r->error " . $headers['X-File-Size']);
		echo json_encode($r);
		die;
}

$type = $headers['X-File-Type'];


$dir = Upload::get_cache_dir() . '/tmp';

if (! file_exists($dir)) {
	$old_mask = umask(0);
	$res = @mkdir($dir, 0777, true);
	umask($old_mask);
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

$ext = preg_replace('/^[^\/]*\//', '', $type);
$uploadfile =  $dir . '/' . $current_user->user_id . '-' . $current_user->user_login . '-' . uniqid() . ".$ext";

$source = file_get_contents('php://input');
if (Upload::current_user_limit_exceded(strlen($source)) ) {
		$r->error = "Size Error";
		echo json_encode($r);
		die;
}

if (file_put_contents($uploadfile, $source)) {
	$r->type = $type;
	$r->name = basename($uploadfile);
	$r->url = $globals['base_static'].Upload::get_cache_relative_dir().'/tmp/'.$r->name;
	$r->thumb = $globals['base_static'].Upload::get_cache_relative_dir()."/tmp/tmp_thumb-$r->name";


	// Creates the thumbnail
	require_once(mnminclude."simpleimage.php");
	$thumb = new SimpleImage();
	$thumb->load($uploadfile);
	$thumb->resize($globals['media_thumb_size'], $globals['media_thumb_size'], true);
	$thumb->save(dirname($uploadfile)."/tmp_thumb-$r->name", -1);

	echo json_encode($r);
}

