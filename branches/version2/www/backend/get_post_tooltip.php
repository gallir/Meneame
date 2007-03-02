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
include_once(mnminclude.'post.php');


if (!empty($_GET['id'])) {
		$id = intval($_GET['id']);
} else {
	die;
}
$post = new Post;
$post->id=$id;
$post->read();
if(!$post->read) die;
if ($post->avatar)
    echo '<img src="'.get_avatar_url($post->author, $post->avatar, 40).'" width="40" height="40" alt="avatar" style="float:left; margin: 0 5px 5px 0;"/>';
echo '<strong>' . $post->username . '</strong><br/>';
echo put_smileys(save_text_to_html($post->content));
?>
