<?
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail dot com>.
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

$error = false;
$field = 'tmp_file';
$r = new stdClass();


$headers = request_headers();

if ( ! $current_user->user_id
	|| empty($headers['X-File-Type'])
	|| empty($headers['X-File-Size'])
	|| Upload::current_user_limit_exceded($headers['X-File-Size']) ) {
		$r->error = "Error";
		syslog(LOG_INFO, "Error " . $headers['X-File-Type'] . "-" . $headers['X-File-Size']);
		echo json_encode($r);
		die;
}

$type = $headers['X-File-Type'];
$size = $headers['X-File-Size'];


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
	echo json_encode($r);
}

