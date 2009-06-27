<?
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'link.php');

if ($globals['url_shortener_mobile_to'] && $globals['mobile']) {
	$server_to = $globals['url_shortener_mobile_to'];
} else {
	$server_to = $globals['url_shortener_to'];
}

$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);

// If the first argument are only numbers, redirect to the story with that id
$link = new Link;
if (preg_match('/^\d+$/', $url_args[1])) {
	$link->id = intval($url_args[1]);
	if ($link->read('id')) {
		header('Location: http://' . $server_to . $link->get_relative_permalink());
		die;
	}
}

header('Location: http://'.$server_to);
die;

?>
