<?
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

$page_size = 50;
$comment = new Comment;

if (!isset($_REQUEST['id']) && $globals['base_comment_url'] && $_SERVER['PATH_INFO']) {
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
	array_shift($url_args); // The first element is always a "/"
	$comment->id = intval($url_args[0]);
} else {
	$url_args = preg_split('/\/+/', $_REQUEST['id']);
	$comment->id=intval($url_args[0]);
	if($comment->id > 0 && $globals['base_comment_url']) {
		// Redirect to the right URL if the link has a "semantic" uri
		header ('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $comment->get_relative_individual_permalink());
		die;
	}
}

if (!$comment->read()) {
	do_error(_('comentario no encontrado'), 404);
}

$link = Link::from_db($comment->link, null, false);
if ($link->is_discarded()) {
	$globals['ads'] = false;
}

$globals['link'] = $link;
$globals['permalink'] = 'http://'.get_server_name().$comment->get_relative_individual_permalink();

// Change to a min_value is times is changed for the current link_status
if ($globals['time_enabled_comments_status'][$link->status]) {
	$globals['time_enabled_comments'] = min($globals['time_enabled_comments_status'][$link->status],
											$globals['time_enabled_comments']);
}

// Check for comment post
if ($_POST['process']=='newcomment') {
	$new = new Comment;
	$new_comment_error = $new->save_from_post($link);
}


$username = $comment->type == 'admin'?'admin':$comment->username;
if ($comment->type != 'admin') $globals['search_options'] = array('w' => 'comments', 'u' => $comment->username);

$comment->check_visibility();
if (! $comment->hide_comment) {
	$globals['description'] = _('Autor') . ": $username, " . _('Resumen') . ': '. text_to_summary($comment->content, 250);
	if ($comment->avatar) {
		$globals['thumbnail'] = get_avatar_url($comment->author, $comment->avatar, 80);
	}
	$title = text_to_summary($comment->content, 120);
} else {
	$title = '';
}

if (isset($globals['canonical_server_name']) && !empty($globals['canonical_server_name'])) {
	$globals['extra_head'] = '<link rel="canonical" href="http://'.$globals['canonical_server_name'].$comment->get_relative_individual_permalink().'" />';
}
do_header($title. ' | ' . $globals['site_name']);
//do_subheader(_('comentario de') . ' ' . $username);
/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
//do_best_stories();
do_best_comments();
do_banner_promotions();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

echo '<h3 style="text-shadow: 0 1px #ccc"><a href="'.$link->get_permalink().'">'. $link->title. '</a></h3>';

echo '<ol class="comments-list">';
echo '<li>';
$comment->print_summary($link, 0, true);

echo '<div style="text-align:right">';
$vars = array('link' => $globals['permalink'],
			'title' => $title);
Haanga::Load('share.html', $vars);
echo '</div>';
echo "</li>\n";
echo "</ol>\n";

print_answers($comment->id, 1);

Comment::print_form($link, 8);
echo '</div>';
// Show the error if the comment couldn't be inserted
if (!empty($new_comment_error)) {
	echo '<script type="text/javascript">';
	echo '$(function(){mDialog.notify(\''._('Aviso'). ": $new_comment_error".'\')}, 5);';
	echo '</script>';
}
do_footer();
exit(0);

function print_answers($id, $level, $visited = false) {
	// Print answers to the comment
	global $db, $page_size;

	if (! $visited) {
		$visited = array();
		$visited[] = $id;
	}

	$printed = array();
	$sql = "SELECT conversation_from FROM conversations, comments WHERE conversation_type='comment' and conversation_to = $id and comment_id = conversation_from ORDER BY conversation_from asc LIMIT $page_size";
	$answers = $db->get_col($sql);
	if ($answers) {
		$type = 'comment';
		echo '<div style="padding-left: 6%">'."\n";
		echo '<ol class="comments-list">';
		foreach ($answers as $dbanswer) {
			if (in_array($dbanswer, $visited)) continue;
			$answer = Comment::from_db($dbanswer);
			$answer->url = $answer->get_relative_individual_permalink();
			echo '<li>';
			$answer->print_summary($link);
			if ($level > 0) {
				$res = print_answers($answer->id, $level-1, array_merge($visited, $answers));
				$visited = array_merge($visited, $res);
			}
			$printed[] = $answer->id;
			$visited[] = $answer->id;
			echo '</li>';
		}
		echo "</ol>\n";
		echo '</div>'."\n";
		if ($level == 0) {
			$ids = implode(',', $printed);
			Haanga::Load('get_total_answers_by_ids.html', compact('type', 'ids'));
		}
	}
	return $printed;
}

?>
