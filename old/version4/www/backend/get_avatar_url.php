<?php
include_once('../config.php');

$id = intval($_GET['id']);
if (! $id > 0) die;
$size = intval($_GET['size']);
if (!$size > 0) $size = 80;

$time=$db->get_var("select user_avatar from users where user_id=$id");
if ($time) {
	//header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . get_avatar_url($id, $time, $size));
}
die;
?>
