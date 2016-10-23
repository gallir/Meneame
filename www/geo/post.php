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
	stats_increment('ajax');
}

if (!empty($_GET['id'])) {
	if (preg_match('/([\w\.\-_]+)-(\d+)/', $_GET['id'], $matches) > 0) {
		$user = $db->escape($matches[1]);
		$date = $matches[2];
		$id = (int) $db->get_var("select post_id from posts, users where user_login = '$user' and post_user_id = user_id and post_date < FROM_UNIXTIME($date) order by post_date desc limit 1");
		if (!$id > 0) {
			echo '<strong>Error: </strong>' . _('usuario o nota no encontrada');
			die;
		}
	} else {
		$id = intval($_GET['id']);
	}

} else {
	die;
}
$post = Post::from_db($id);
if(!$post) die;
echo '<div style="font-size:8.5pt;width:280px; margin-right:15px; overflow:hidden">';
if ($post->avatar)
	echo '<img src="'.get_avatar_url($post->author, $post->avatar, 40).'" width="40" height="40" alt="avatar" style="float:left; margin: 0 5px 5px 0;"/>';
echo '<a href="'.post_get_base_url($post->username).'" target="_blank"><strong>' . $post->username . '</strong></a> ('.$post->src.')<br/>';
echo $post->to_html($post->content);
echo '</div>';
?>
