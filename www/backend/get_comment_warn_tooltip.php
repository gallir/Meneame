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
	include_once(__DIR__.'/../config.php');
	header('Content-Type: text/html; charset=utf-8');
	header('Cache-Control: public, s-maxage=300');
}

if (empty($_GET['id'])) die;
$link_id = intval($_GET['id']);
$link = Link::from_db($link_id, null, false);

if(! $link) die;
if ($link->date < $globals['now'] - $globals['time_enabled_votes']) die;

$min_len = 20;
$min_karma = 20;
$sql = "select comment_id, comment_karma, comment_karma + least(50, comment_order * 0.5) + least(50, length(comment_content) * 0.1) as val from comments, votes WHERE comment_link_id = $link->id and vote_type = 'links' and vote_link_id = comment_link_id and vote_user_id = comment_user_id and vote_value < 0 and comment_karma >= $min_karma and length(comment_content) >= $min_len order by val desc limit 1";


$res = $db->get_row($sql);
if (! $res) die;

$comment = Comment::from_db($res->comment_id);
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
