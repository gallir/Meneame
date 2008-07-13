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
	include(mnminclude.'link.php');
	require_once(mnminclude.'comment.php');
} 

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

	$rows = min(40, max(substr_count($comment->content, "\n") * 2, 8));
	echo '<div class="commentform">'."\n";
	echo '<form action="'.htmlspecialchars($_SERVER['PHP_SELF']).'" method="post">'."\n";
	echo '<fieldset><legend>'._('editar comentario').'</legend>'."\n";
	print_simpleformat_buttons('edit-comment-'.$comment->id);
	echo '<div><textarea name="comment_content" id="edit-comment-'.$comment->id.'" rows="'.$rows.'" cols="75">'.$comment->content.'</textarea></div>'."\n";
	echo '<input class="submit" type="submit" name="submit" value="'._('modificar comentario').'" />'."\n";
	echo '<input type="hidden" name="process" value="editcomment" />'."\n";
	echo '<input type="hidden" name="key" value="'.md5($comment->randkey.$site_key).'" />'."\n";
	echo '<input type="hidden" name="id" value="'.$comment->id.'" />'."\n";
	echo '<input type="hidden" name="link_id" value="'.$link->id.'" />'."\n";
	echo '<input type="hidden" name="user_id" value="'.$current_user->user_id.'" />'."\n";
	echo '</fieldset>'."\n";
	echo '</form>'."\n";
	echo "</div>\n";
}

function save_comment () {
	global $link, $db, $comment, $current_user, $globals, $site_key;


	if(intval($_POST['id']) == $comment->id && $current_user->authenticated && 
		// Allow the author of the post
		((intval($_POST['user_id']) == $current_user->user_id &&
		$current_user->user_id == $comment->author &&
		time() - $comment->date < $globals['comment_edit_time'] * 1.1) || $current_user->user_level == 'god') &&
		$_POST['key']  == md5($comment->randkey.$site_key)  && 
		strlen(trim($_POST['comment_content'])) > 2 ) {
		$comment->content=clean_text($_POST['comment_content'], 0, false, 10000);
		if (strlen($comment->content) > 0 ) {
			$comment->store();
		}
		header('Location: '.$link->get_permalink() . '#comment-'.$comment->order);
		die;
	} else {
		echo _('error actualizando, probablemente tiempo de edición excedido');
		die;
	}
}

?>
