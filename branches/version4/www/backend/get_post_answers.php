<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2011 by
// Ricardo Galli <gallir at gmail dot com>and Menéame Comunicacions
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

$id = intval($_GET['id']);
if (! $id) die;

// Print answers to the comment
$sql = "SELECT conversation_from as comment_id FROM conversations, comments WHERE conversation_type='post' and conversation_to = $id and comment_id = conversation_from ORDER BY conversation_from asc";
$res = $db->get_col($sql);

if ($res) {
	header('Content-Type: text/html; charset=UTF-8');
	foreach($res as $answer) {
		$post = Post::from_db($answer);
		$post->basic_summary = true;
		$post->not_ignored = true;
		$post->prefix_id = "$id-"; // This a trick in order not to confuse with other ids
		$post->print_summary();
		echo "\n";
	}
	Haanga::Load('fancybox.html');
}

