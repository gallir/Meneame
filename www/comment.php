<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include_once(mnminclude.'commenttree.php');

$page_size = $globals['page_size'] * 3;
$comment = Comment::from_db(intval($globals['path'][1]));

if (!$comment) {
	do_error(_('comentario no encontrado'), 404);
}

$link = Link::from_db($comment->link, null, false);
if ($link->is_discarded()) {
	$globals['ads'] = false;
	$globals['noindex'] = true;
} elseif ($comment->karma < 50 || mb_strlen($comment->content) < 100 ) {
	$globals['noindex'] = true;
}


$globals['link'] = $link;
$globals['permalink'] = $globals['scheme'].'//'.get_server_name().$comment->get_relative_individual_permalink();

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
	$description = text_to_summary($comment->content, 250);
	$title = text_to_summary($description, 117);

	$globals['description'] = _('Autor') . ": $username, " . _('Resumen') . ': '. $description;
	if ($globals['media_public'] && $comment->media_size > 0) {
		 $globals['thumbnail'] = Upload::get_url('comment', $comment->id, 0, $comment->media_date, $comment->media_mime);
	} elseif ($comment->avatar) {
		$globals['thumbnail'] = get_avatar_url($comment->author, $comment->avatar, 80);
	}
} else {
	$title = '';
	$globals['noindex'] = true;
}

// Canonical url
if (isset($globals['canonical_server_name']) && !empty($globals['canonical_server_name'])) {
	$canonical_server = $globals['canonical_server_name'];
} else {
	$canonical_server = $link->server_name;
}
$canonical_base = $link->base_url;
if ($link->is_sub) $canonical_base .= 'm/'.$link->sub_name.'/';
$canonical_base .= 'c/';
$globals['extra_head'] = '<link rel="canonical" href="'.$globals['scheme'].'//'.$canonical_server.$canonical_base.$comment->id.'"/>';


do_header($title);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
//do_best_stories();
do_best_comments();
do_banner_promotions();
echo '</div>';
/*** END SIDEBAR ***/

echo '<div id="newswrap">';
echo '<h3><a href="'.$link->get_permalink().'">'. $link->title. '</a></h3>';


$tree = new CommentTree();
$tree->addByIds($comment->id);

for ($i = 0; $i < 6; $i++) {
	if (! fill_tree($tree) || $tree->size() > 30) {
		break;
	}
}

echo '<div class="comments">';
echo '<div style="text-align:right">';
$vars = array('link' => $globals['permalink'],
			'title' => $title);
Haanga::Load('share.html', $vars);
echo '</div>';

Comment::print_tree($tree, $link);
echo '</div></div>';


do_footer();
exit(0);

function fill_tree($tree, $limit = 30) {
	global $globals, $db;

	if (empty($tree->nodesIds)) {
		return 0;
	}

	$nodesKeys = array_keys($tree->nodesIds);

	if (!empty($tree->previous_keys)) {
		$parents = array_diff_key($nodesKeys, $tree->previous_keys); // To avoid requesting for the same parents
	} else {
		$parents = $nodesKeys;
	}

	if (empty($parents)) {
		return 0;
	}

	$tree->previous_keys = $nodesKeys;
	$inserted = 0;
	$ids = implode(',', $parents);
	$sql = "SELECT conversation_to as parent, conversation_from as child FROM conversations WHERE conversation_type='comment' and conversation_to in ($ids) ORDER BY conversation_from asc LIMIT $limit";
	$res = $db->get_results($sql);

	if ($res) {
		foreach ($res as $n) {
			if (! $tree->in($n->parent) || ! $tree->in($n->parent)) {
				$tree->addByIds($n->parent, $n->child);
				$inserted++;
			}
		}
	}

	return $inserted;
}

