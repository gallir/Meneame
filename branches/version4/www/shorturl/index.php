<?
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// Don't check the user is logged
$globals['no_auth'] = true;
include('../config.php');

if ($globals['url_shortener_mobile_to'] && $globals['mobile']) {
	$server_to = $globals['url_shortener_mobile_to'];
} else {
	$server_to = $globals['url_shortener_to'];
}

if (preg_match('/^\/*$/', $_SERVER['PATH_INFO'])) {
	header('Location: http://' . $server_to);
	die;
}

$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);

// If the first argument are only numbers, redirect to the story with that id
if (preg_match('/^[\da-z]+$/', $url_args[1])) {
	$link = Link::from_db(intval(base_convert($url_args[1], 36, 10)), null, false);
	if ($link) {
		header ('HTTP/1.1 301 Moved');
		header('Location: http://' . $server_to . $link->get_relative_permalink());
		die;
	}
}

not_found('Link not found');
die;

?>
