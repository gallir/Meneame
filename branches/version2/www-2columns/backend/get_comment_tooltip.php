<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and 
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>
if (! defined('mnmpath')) {
	include_once('../config.php');
	header('Content-Type: text/html; charset=utf-8');
}
include_once(mnminclude.'comment.php');


if (!empty($_GET['id'])) {
	if (!empty($_GET['link'])) {
		$link = intval($_GET['link']);
		$order = intval($_GET['id']);
		$id = $db->get_var("select comment_id from comments where comment_link_id=$link and comment_order=$order");
		if (! $id > 0) die;
	} else {
		$id = intval($_GET['id']);
	}
} else {
	die;
}
$comment = new Comment;
$comment->id=$id;
$comment->read();
if(!$comment->read) die;
if ($comment->avatar)
    echo '<img hspace="2" src="'.get_avatar_url($comment->author, $comment->avatar, 20).'" width="20" height="20" alt="avatar"/>';
echo '<strong>' . $comment->username . '</strong><br/>';
echo $comment->put_smileys(save_text_to_html(mb_substr($comment->content, 0, 1000)));
?>
