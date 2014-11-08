<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>
if (! defined('mnmpath')) {
	include_once('../config.php');
	header('Content-Type: text/html; charset=utf-8');
	header('Cache-Control: public, s-maxage=300');
}

if (!empty($_GET['id'])) {
	$keys = preg_split('/-/', $_GET['id'], -1, PREG_SPLIT_NO_EMPTY);
	if (count($keys) == 2) {
		$link = intval($keys[0]);
		$order = intval($keys[1]);
		$id = $db->get_var("select comment_id from comments where comment_link_id=$link and comment_order=$order");
		if (! $id > 0) die;
	} else {
		$id = intval($keys[0]);
	}
} else {
	die;
}
$comment = Comment::from_db($id);
if(!$comment) die;
echo '<div class="comment-body">';
if ( $comment->type != 'admin') {
	if ($comment->avatar) {
		echo '<img class="avatar" src="'.get_avatar_url($comment->author, $comment->avatar, 40).'" width="40" height="40" alt="avatar" style="float:left; margin: 0 5px 4px 0;"/>';
	}
	echo '<strong><span style="color:#3D72C3">' . $comment->username . '</span></strong>, karma: '.$comment->karma.'<br/>';
} else {
	echo '<strong>' . get_server_name() . '</strong><br/>';
}
$comment->print_text(1000);
echo '</div>';
?>
