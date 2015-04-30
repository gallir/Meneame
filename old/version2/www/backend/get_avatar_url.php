<?php
include_once('../config.php');

stats_increment('other');

$id = intval($_GET['id']);
if (! $id > 0) die;
$size = intval($_GET['size']);
if (!$size > 0) $size = 80;

$user=$db->get_row("select user_avatar from users where user_id=$id");
if ($user) {
	//header('HTTP/1.1 301 Moved Permanently');
	header('Location: ' . get_avatar_url($id, $user->user_avatar, $size));
}
die;
?>
