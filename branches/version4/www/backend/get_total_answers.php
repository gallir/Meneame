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

$offset = intval($_GET['offset']);
$size = intval($_GET['size']);

if (isset($_GET['order'])) {
	$order = 'ORDER BY ' . $db->escape($_GET['order']);
} else {
	$order = '';
}

switch ($_GET['type']) {
	case 'post':
		$type = 'post';
		break;
	default:
		$type = 'comment';
}




$inner_join = "SELECT comment_id FROM comments WHERE comment_link_id = $id $order LIMIT $offset, $size";
$sql = "SELECT conversation_to as `to`, count(*) as t FROM conversations INNER JOIN ($inner_join) as comment_id ON comment_id = conversation_to WHERE conversation_type='$type' GROUP BY conversation_to";

$answers = array();
$res = $db->get_results($sql);
if ($res) {
	foreach ($res as $answer) {
		$answers[$answer->to] = (int)$answer->t;
	}
}

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($answers);
