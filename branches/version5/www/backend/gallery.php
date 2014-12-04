<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

$user_id = intval($_GET['user']);

$limit = 250; 
$show_all = false;

switch ($_GET['type']) {
	case 'comment':
		$type_in = '("comment")';
		break;
	case 'post':
		$type_in = '("post")';
		break;
	case 'all':
	default:
		$type_in = '("comment", "post")';
		break;

}

if ($user_id > 0) {
	$user = "and user = $user_id";
	if ($current_user->user_id) {
		$show_all = true;
	}
	if ($user_id == $current_user->user_id) {
		$limit = 5000;
	} else {
		$limit = 500;
	}
} else $user = '';

header('Content-Type: text/html; charset=utf-8');
$media = $db->get_results("select type, id, version, unix_timestamp(date) as date, mime, user as uid, user_login as user from media, users where type in $type_in $user and version = 0 and user_id = media.user order by date desc limit $limit");

$images = array();

if ($media) {
	foreach ($media as $image) {
		if (! $show_all) {
			switch ($image->type) {
				case 'comment':
					$karma = $db->get_var("select comment_karma from comments where comment_id = $image->id");
					break;
				case 'post':
					$karma = $db->get_var("select post_karma from posts where post_id = $image->id");
					break;
				default:
					$karma = 0;
			}
		}
			
		if ($show_all || $karma > -10) {
			$image->url = Upload::get_url($image->type, $image->id, $image->version, $image->date, $image->mime);
			$images[] = $image;
		}
	}
}
if ($images) Haanga::Load("backend/gallery.html", compact('images'));
?>
