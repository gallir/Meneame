<?php
if (! defined('mnmpath')) {
	include_once(__DIR__.'/../config.php');
}

include_once(mnmpath.'/libs/avatars.php');

if (! isset($_GET['id']) && !empty($_GET['user'])) {
	$id = (int) $db->get_var("select user_id from users where user_login = '".$db->escape($_GET['user'])."'");
} else {
	$id = intval($_GET['id']);
}
if (! $id > 0) {
	header("HTTP/1.0 404 Not Found");
	header("Status: 404 Not Found");
	die;
}
$size = intval($_GET['size']);
if (!$size > 0) $size = 80;

$time = $db->get_var("select user_avatar from users where user_id=$id");

if (! $time > 0) {
	header('HTTP/1.1 302 Moved');
	header('Location: ' . get_no_avatar_url($size));
	die;
}

// If the time given in the URL is wrong, redirect to the right one
if (isset($_GET['time']) && $_GET['time'] != $time) {
	header('HTTP/1.1 301 Moved');
	header('Location: ' . get_avatar_url($id, $time, $size));
	die;
}


if (!($img=avatar_get_from_file($id, $size, $time))) {
	//syslog(LOG_INFO, "Meneame, creating avatar for user $id size $size time $time");
	$img=avatar_get_from_db($id, $size);
}

if ($img) {
	header('HTTP/1.1 200 OK');
	header("Content-type: image/jpeg");
	echo $img;
} else {
	header('HTTP/1.1 307 Image not found');
	header('Location: ' . get_no_avatar_url($size));
}
