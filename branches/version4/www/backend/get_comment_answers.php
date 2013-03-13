<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2011 by
// Ricardo Galli <gallir at gmail dot com>and Menéame Comunicacions
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// Use the alternate server for api, if it exists
$globals['alternate_db_server'] = 'backend';

include('../config.php');

$id = intval($_GET['id']);
if (! $id) die;

// Print answers to the comment
$sql = "SELECT conversation_from as comment_id FROM conversations, comments WHERE conversation_type='comment' and conversation_to = $id and comment_id = conversation_from ORDER BY conversation_from asc";
$res = $db->get_col($sql);

if ($res) {
	header('Content-Type: text/html; charset=UTF-8');
	foreach($res as $answer) {
		$comment = Comment::from_db($answer);
		$comment->basic_summary = true;
		$comment->not_ignored = true;
		$comment->prefix_id = "$id-"; // This a trick in order not to confuse with other ids
		$comment->print_summary(false, 2500);
		echo "\n";
	}
}

