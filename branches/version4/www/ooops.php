<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2009 by
// Benjamí Villoslada <benjami at bitassa dot cat>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

$errn = $_GET{"e"};

// Check we must reconstruct an image in cache directory
$cache_dir = preg_quote($globals['base_url'] . $globals['cache_dir'], '/');
if (preg_match("/$cache_dir/", $_SERVER['REQUEST_URI'])) {
	$filename = basename(clean_input_string($_SERVER['REQUEST_URI']));
	$base_filename = preg_replace('/\..+$/', '', $filename);
	$parts = explode('-', $base_filename);
	switch ($parts[0]) {
		case "media_thumb":
			if ($parts[1] != 'post' && $parts[1] != 'comment') break;
			$media = new Upload($parts[1], $parts[2], 0);
			if (! $media->read()) break;
			if ($media->create_thumb(40)) {
				header("HTTP/1.0 200 OK");
				header('Content-Type: image/jpeg');
				$media->thumb->output();
				die;
			}
		default:
			$errn = 404;
	}
}

switch($errn) {
  case 400:
	$errp = _('petición desconocida');
	break;
  case 401:
	$errp = _('no autorizado');
	break;
  case 403:
	$errp = _('acceso prohibido');
	break;
  case 404:
	$errp = _('la página no existe');
	break;
  case 500:
  case 501:
  case 503:
	$errp = _('error de servidor');
	break;
  default:
	$errn = false;
	$errp = false;
}

do_error($errp, $errn, false);
