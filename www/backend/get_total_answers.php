<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005-2011 by
// Ricardo Galli <gallir at gmail dot com>and Menéame Comunicacions
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// Don't check the user is logged
$globals['no_auth'] = true;

// Use the alternate server for api, if it exists
$globals['alternate_db_server'] = 'backend';

include(__DIR__.'/../config.php');


switch ($_REQUEST['type']) {
	case 'post':
		$type = 'post';
		break;
	default:
		$type = 'comment';
}

$id = intval($_REQUEST['id']);
if ($id ) {
	// It selects all answers to comments of a given link
	if (isset($_REQUEST['order'])) {
		$order = 'ORDER BY ' . $db->escape($_REQUEST['order']);
	} else {
		$order = '';
	}
	$offset = intval($_REQUEST['offset']);
	$size = intval($_REQUEST['size']);
	$inner_join = "SELECT comment_id FROM comments WHERE comment_link_id = $id $order LIMIT $offset, $size";
	$sql = "SELECT conversation_to as `to`, count(*) as t FROM conversations INNER JOIN ($inner_join) as comment_id ON comment_id = conversation_to WHERE conversation_type='$type' GROUP BY conversation_to";
} elseif (! empty($_POST['ids'])){

	// Don't count answers (posts) of disabled users
	if ($type == 'post') {
		$extra_from = ', posts, users';
		$extra_where = 'and post_id = conversation_from and user_id = post_user_id and user_level not in ("disabled", "autodisabled")';
	} else {
		$extra_from = $extra_where = '';
	}

	// It selects the answers to a list of ids
	$a = explode(',', $_REQUEST['ids'], 200);
	if ($a && ($c = count($a)) > 0) {
		for ($i=0; $i < $c; $i++) {
			$a[$i] = intval($a[$i]);
		}
		$ids = implode(',', $a);
		$sql = "SELECT conversation_to as `to`, count(*) as t FROM conversations $extra_from WHERE conversation_type='$type' and conversation_to IN ($ids) $extra_where GROUP BY conversation_to";
	}

} else {
	die;
}

$answers = array();
$res = $db->get_results($sql);
if ($res) {
	foreach ($res as $answer) {
		$answers[$answer->to] = (int)$answer->t;
	}
}

header('Content-Type: application/json; charset=UTF-8');
echo json_encode($answers);
