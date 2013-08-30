<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
	include('../config.php');
	include(mnminclude.'html1.php');
}

array_push($globals['cache-control'], 'no-cache');
http_cache();

if (!empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
	$comment = new Comment;
	$comment->id = intval($_REQUEST['id']);
	if (! $comment->read()) die;
} else {
	header('Location: http://'.get_server_name().$globals['base_url']);
	die;
}

$link = new Link;
$link->id = $comment->link;
if (! $link->read_basic() ) {
	header('Location: http://'.get_server_name().$globals['base_url']);
	die;
}

//$globals['link']=$link;
//$globals['link_id']=$link->id;

if ($_POST['process']=='editcomment') {
	save_comment();
} else {
	print_edit_form();
}

function print_edit_form() {
	global $link, $comment, $current_user, $site_key, $globals;

	if ($current_user->user_level != 'god' && time() - $comment->date > $globals['comment_edit_time']) die;

	// $rows = min(40, max(substr_count($comment->content, "\n") * 2, 8));
	echo '<div class="commentform">'."\n";
	echo '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" class="comment" method="post" enctype="multipart/form-data">'."\n";

	echo '<input type="hidden" name="process" value="editcomment" />'."\n";
	echo '<input type="hidden" name="key" value="'.md5($comment->randkey.$site_key).'" />'."\n";
	echo '<input type="hidden" name="id" value="'.$comment->id.'" />'."\n";

	echo '<fieldset><legend>'._('editar comentario').'</legend>'."\n";
	print_simpleformat_buttons('edit-comment-'.$comment->id);
	echo '<div style="clear: right"><textarea name="comment_content" class="droparea" id="edit-comment-'.$comment->id.'" rows="5">'.$comment->content.'</textarea></div>'."\n";


	echo '<input class="button" type="submit" name="submit" value="'._('modificar comentario').'" />'."\n";
	// Allow gods to put "admin" comments which does not allow votes
	if ($current_user->user_level == 'god') {
		if ($comment->type == 'admin') $checked = 'checked="true"';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;<label><strong>'._('admin').' </strong><input name="type" type="checkbox" value="admin" '.$checked.'/></label>'."\n";
	}


	$vars = compact('link', 'comment');
	Haanga::Load('comment_edit.html', $vars);


	echo '</fieldset>'."\n";
	echo '</form>'."\n";
	echo "</div>\n";
}

function save_comment () {
	global $link, $db, $comment, $current_user, $globals, $site_key;



	// Warning, trillion of checkings :-(


	// Check image limits
	if (!empty($_FILES['image']['tmp_name'])) {
		$limit_exceded = Upload::current_user_limit_exceded($_FILES['image']['size']);
		if ($limit_exceded) {
			echo $limit_exceded;
			die;
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
			echo _('comentario no insertado, enlace a sitio deshabilitado (y usuario reciente)');
			die;
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


		header('Location: '.$link->get_permalink() . '#c-'.$comment->c_order);
		die;
	} else {
		echo _('error actualizando, probablemente tiempo de edición excedido');
		die;
	}
}

?>
