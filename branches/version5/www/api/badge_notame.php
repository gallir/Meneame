<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>
include_once('../config.php');
header('Content-Type: text/javascript; charset=UTF-8');
header('Cache-Control: max-age=30');

if (!empty($_GET['user'])) {
	$user = $db->escape($_GET['user']);
	$sql = "SELECT post_id FROM users, posts WHERE user_login='$user' and post_user_id=user_id ORDER BY post_date desc limit 1";
} elseif (!empty($_GET['id']))  {
	$id = intval($_GET['id']);
	$sql = "SELECT post_id FROM posts WHERE post_user_id=$id ORDER BY post_date desc limit 1";
} else {
	die;
}

switch ($_GET['size']) {
	case 'small':
		$width = 110;
		$height = 120;
		$avatar = 25;
		$text_length = 120;
		break;
	case 'large':
		$width = 160;
		$height = 160;
		$avatar = 40;
		$text_length = 180;
		break;
	case 'xl':
		$width = 190;
		$height = 150;
		$avatar = 40;
		$text_length = 210;
		break;
	case 'medium':
	default:
		$width = 140;
		$height = 120;
		$avatar = 40;
		$text_length = 150;
}

if (!empty($_GET['border'])) {
	$border = get_hex_color($_GET['border'], '#');
} else {
	$border = '#bbb';
}


$id = $db->get_var($sql);
if(! $id > 0) die;
$post = new Post;
$post->id=$id;
$post->read();
if(!$post->read) die;
echo 'document.write(\'';
echo '<a href="http://'.get_server_name().post_get_base_url($post->username).'" style="text-decoration: none; border: none">';
echo '<div style="overflow: hidden; background: #fff; width: '.$width.'px; max-height: '.$height.'px; border: 1px solid; border-color: '.$border.'; ">';
echo '<div style="padding: 4px 4px 4px 4px;">';
echo '<div style="overflow: hidden; color: #666; text-align: left; font-family: sans-serif; font-size: 8pt; padding: 0; line-height: 1.1">';
echo '<img src="'.get_avatar_url($post->author, $post->avatar, $avatar).'" width="'.$avatar.'" height="'.$avatar.'" alt="avatar" style="float:left; margin: 0 5px 4px 0;" border: none/>';
echo '<div>';
echo '<span style="color: #111;">'.$post->username.'</span><br/>';
echo addslashes(text_to_summary($post->content, $text_length));
echo '</div></div></div></div></a>';
echo '\');';
?>
