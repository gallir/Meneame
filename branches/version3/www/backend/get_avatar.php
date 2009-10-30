<?php
include_once('../config.php');
include_once(mnmpath.'/libs/avatars.php');

if (!$_GET['id'] && !empty($_GET['user'])) {
	$id = (int) $db->get_var("select user_id from users where user_login = '".$db->escape($_GET['user'])."'");
} else {
	$id = intval($_GET['id']);
}
if (! $id > 0) die;
$size = intval($_GET['size']);
$time = intval($_GET['time']);
if (!$size > 0) $size = 80;

if (!($img=avatar_get_from_file($id, $size))) {
	$img=avatar_get_from_db($id, $size);
	if (!$img) {
		if (is_writable($globals['avatars_dir'])) {
			$user=$db->get_row("select user_avatar, user_email from users where user_id=$id");
			if ($user) {
				header('Location: ' . get_avatar_url($id, $user->user_avatar, $size));
			}
		} else {
				header('Location: ' . get_no_avatar_url($size));
		}
		die;
	}  
}

header("Content-type: image/jpg");
//header('Cache-Control: max-age=7200');
echo $img;
?>
