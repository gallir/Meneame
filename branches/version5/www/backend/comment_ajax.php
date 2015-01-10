<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
	include(dirname(__FILE__).'/../config.php');
	include(mnminclude.'html1.php');
}

array_push($globals['cache-control'], 'no-cache');
http_cache();

header('Content-Type: application/json; charset=utf-8');

$link_id = $parent = $comment = $link = false;

if (isset($_REQUEST['reply_to']) && ($reply_to = intval($_REQUEST['reply_to'])) > 0 ) {
	$res = $db->get_row("select comment_link_id, comment_order from comments where comment_id=$reply_to");
	if (! $res )  die;

	$link_id = $res->comment_link_id;
	$parent = $res->comment_order;
	$comment = new Comment();
	if ($parent) {
		$comment->content = "#$parent ";
	}
} elseif (!empty($_REQUEST['id']) && ($id = intval($_REQUEST['id'])) > 0 ) {
	$comment = Comment::from_db($id);
	if (! $comment) die;
	$link_id = $comment->link;
}

$link = Link::from_db($link_id);
if (! $link) {
	die;
}

if (! $current_user->authenticated
	|| $current_user->user_karma < $globals['min_karma_for_comments']
	|| ($current_user->user_date > $globals['now'] - $globals['min_time_for_comments']
			&& $current_user->user_id != $link->author)) {
	die;
}

if ($_POST['process']=='editcomment') {
	save_comment($comment, $link);
} else {
	print_edit_form($comment, $link);
}

function print_edit_form($comment, $link) {
	global $current_user, $site_key, $globals, $reply_to;

	$data = array();

	$html = '';

	if ($comment->id == 0) {
		$comment->randkey = rand(1000000,100000000);
	}

	$html .= '<div class="commentform">';
	$html .= '<form action="'.$globals['base_url']."comment_ajax?reply_to=$reply_to&amp;link=$link->id&amp;id=$comment->id&amp;user=$current_user->user_id".'" class="comment" method="post" enctype="multipart/form-data" id="c_edit_form">';
	$html .= '<input type="hidden" name="randkey" value="'.$comment->randkey.'" />';

	$html .= '<input type="hidden" name="process" value="editcomment" />';
	$html .= '<input type="hidden" name="key" value="'.md5($comment->randkey.$site_key).'" />';
	$html .= '<input type="hidden" name="id" value="'.$comment->id.'" />';

	//print_simpleformat_buttons('edit-comment-'.$comment->id);
	//$html .= '<div style="clear: right">';

	$vars = compact('link', 'comment');
	$html .= Haanga::Load('comment_edit.html', $vars, true);

	$html .= '</form></div>';
	$data['html'] = $html;
	$data['error'] = '';
	echo json_encode($data);
}

function save_comment ($comment, $link) {
	global $db, $current_user, $globals, $site_key;

	$data = array();
	if ($comment->id == 0) {
		$res = Comment::save_from_post($link, false); // New comment
	} else {
		$res = check_and_save($comment, $link); // Edit, others requirements
	}

	if (is_a($res, "Comment", false) ) {
		$comment = Comment::from_db($res->id);
		//$data['html'] = "<b>$comment->id, $comment->read, $comment->link</b>";
		$data['html'] = $comment->print_summary(null, null, true, true);
		$data['error'] = '';
	} else {
		$data['html'] = '';
		$data['error'] = $res;
	}
	echo json_encode($data);
}

function check_and_save($comment, $link) {
	global $db, $current_user, $globals, $site_key;

	// Warning, trillion of checkings :-(
	// TODO: unify with Comment::save_from_post(), careful with the differences
	// Check image limits

	if (!empty($_FILES['image']['tmp_name'])) {
		$limit_exceded = Upload::current_user_limit_exceded($_FILES['image']['size']);
		if ($limit_exceded) {
			return $limit_exceded;
		}
	}

	$user_id = intval($_POST['user_id']);
	if(intval($_POST['id']) == $comment->id && $current_user->authenticated &&
		// Allow the author of the post
		(($user_id == $current_user->user_id
				&& $current_user->user_id == $comment->author
				&& time() - $comment->date < $globals['comment_edit_time'] * 1.5)
			|| (($comment->author != $current_user->user_id || $comment->type == 'admin')
				&& $current_user->user_level == 'god')) &&
		$_POST['key']  == md5($comment->randkey.$site_key)  &&
		strlen(trim($_POST['comment_content'])) > 2 ) {
		$comment->content=clean_text_with_tags($_POST['comment_content'], 0, false, 10000);

		if ($current_user->user_level == 'god') {
			if ($_POST['type'] == 'admin') {
				$comment->type = 'admin';
			} else {
				$comment->type = 'normal';
			}
		}

		if (! $current_user->admin) $comment->get_links();

		if ($current_user->user_id == $comment->author && $comment->banned
				&& $current_user->Date() > $globals['now'] - 86400) {
			syslog(LOG_NOTICE, "Meneame: editcomment not stored, banned link ($current_user->user_login)");
			return _('comentario no insertado, enlace a sitio deshabilitado (y usuario reciente)');
		}

		if (strlen($comment->content) > 0 ) {
			$comment->store();
		}

		// Check image upload or delete
		if ($_POST['image_delete']) {
			$comment->delete_image();
		} elseif (!empty($_POST['tmp_filename']) && !empty($_POST['tmp_filetype']) ) {
			$comment->move_tmp_image($_POST['tmp_filename'], $_POST['tmp_filetype']);
		} elseif (!empty($_FILES['image']['tmp_name'])) {
			$comment->store_image($_FILES['image']);
		}
		return $comment;

	}

	return _('error actualizando, probablemente tiempo de edición excedido');
}

