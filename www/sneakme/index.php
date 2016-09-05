<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


// TODO: this page just became unreadable
//		Must add printing the threads with CommentTree and split the
//		page with only one post.


include_once('../config.php');
include('common.php');


$argv = $globals['path'];
$argv[0] = clean_input_string($argv[0]);



if ($argv[0] == _priv) {
	// Load priv.php
	include('priv.php');
	die;
}

include(mnminclude.'html1.php');
include(mnminclude.'favorites.php');

$globals['search_options'] = array('w' => 'posts');

$user=new User();

$min_date = date("Y-m-d H:00:00", time() - 192800); //  about 48 hours
$page_size = 50;
$offset=(get_current_page()-1)*$page_size;
$page_title = _('nótame') . ' | '. $globals['site_name'];
$view = false;
$short_content = false;

switch ($argv[0]) {
	case '_best':
		$tab_option = 2;
		$page_title = _('mejores notas') . ' | ' . _('menéame');
		$min_date = date("Y-m-d H:00:00", time() - 86400); //  about 24 hours
		$where = "post_date > '$min_date'";
		$order_by = "ORDER BY post_karma desc";
		$limit = "LIMIT $offset,$page_size";
		$rows = $db->get_var("SELECT count(*) FROM posts where post_date > '$min_date'");
		break;

	case '':
	case '_all':
		$tab_option = 1;
		//$sql = "SELECT SQL_CACHE post_id FROM posts ORDER BY post_id desc limit $offset,$page_size";
		$where = "post_id > 0";
		$order_by = "ORDER BY post_id desc";
		$limit = "LIMIT $offset,$page_size";
		//$rows = $db->get_var("SELECT count(*) FROM posts");
		$rows = Post::count();
		$min_date = date("Y-m-d 00:00:00", time() - 86400*10);
		//$rows = $db->get_var("SELECT SQL_CACHE count(*) FROM posts where post_date > '$min_date'");
		$rss_option="sneakme_rss";
		break;

	default:
		$tab_option = 4;
		if ( (is_numeric($argv[0]) && ($post_id = intval($argv[0])) > 0)
				|| (is_numeric($argv[1]) && ($post_id = intval($argv[1])) > 0)  ) {
			// Individual post
			$user->id = $db->get_var("select post_user_id from posts where post_id=$post_id");
			if(!$user->read()) {
				do_error(_('usuario no encontrado'), 404);
			}
			$globals['permalink'] = 'http://'.get_server_name().post_get_base_url($post_id);
			// Fill title
			$summary = text_to_summary($db->get_var("SELECT post_content from posts where post_id = $post_id"), 250);
			$globals['description'] = _('Autor') . ": $user->username, " . _('Resumen') . ': '. $summary;
			$page_title = text_to_summary($summary, 120);
			if ($user->avatar) {
				$globals['thumbnail'] = get_avatar_url($user->id, $user->avatar, 80);
			}

			//$page_title = sprintf(_('nota de %s'), $user->username) . " ($post_id)";
			$globals['search_options']['u'] = $user->username;
			$where = "post_id = $post_id";
			$order_by = "";
			$limit = "";
			$rows = 1;
			$answers = $db->get_var("SELECT count(conversation_from) FROM conversations WHERE conversation_type='post' and conversation_to = $post_id");
			if ($answers < 5) {
				$short_content = true;
			}
		} else {
			// User is specified
			$user->username = $db->escape($argv[0]);
			if(!$user->read() || $user->disabled()) {
				do_error(_('usuario no encontrado'), 404);
			}
			$globals['noindex'] = true;
			switch($argv[1]) {
				case '_friends':
					$view = 1;
					$page_title = sprintf(_('amigos de %s'), $user->username);
					$from = ", friends";
					$where = "friend_type='manual' and friend_from = $user->id and friend_to=post_user_id and friend_value > 0";
					$order_by = "ORDER BY post_id desc";
					$limit = "LIMIT $offset,$page_size";
					$rows = $db->get_var("SELECT count(*) FROM posts, friends WHERE friend_type='manual' and friend_from = $user->id and friend_to=post_user_id and friend_value > 0");
					$rss_option="sneakme_rss?friends_of=$user->id";
					break;

				case '_favorites':
					$view = 2;
					$page_title = sprintf(_('favoritas de %s'), $user->username);
					$ids = $db->get_col("SELECT favorite_link_id FROM favorites WHERE favorite_user_id=$user->id AND favorite_type='post' ORDER BY favorite_link_id DESC LIMIT $offset,$page_size");
					$from = "";
					$where = "post_id in (".implode(',', $ids).")";
					$order_by = "ORDER BY post_id desc";
					$limit = "";
					$rows = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_user_id=$user->id AND favorite_type='post'");
					$rss_option="sneakme_rss?favorites_of=$user->id";
					break;

				case '_conversation':
					$view = 3;
					$page_title = sprintf(_('conversación de %s'), $user->username);
					$ids = $db->get_col("SELECT distinct conversation_from FROM conversations WHERE conversation_user_to=$user->id and conversation_type='post' ORDER BY conversation_time desc LIMIT $offset,$page_size");
					$where = "post_id in (".implode(',', $ids).")";
					$from = "";
					$order_by = "ORDER BY post_id desc ";
					$limit = "";
					$rows = -1; // $db->get_var("SELECT count(distinct(conversation_from)) FROM conversations, posts WHERE conversation_user_to=$user->id and conversation_type='post' and post_id = conversation_from ");
					$rss_option="sneakme_rss?conversation_of=$user->id";
					break;

				case '_votes':
					$view = 4;
					$page_title = sprintf(_('votos de %s'), $user->username);
					$rows = -1; // $rows = -1; //$db->get_var("SELECT count(*) FROM votes, posts WHERE vote_type='posts' and vote_user_id=$user->id and post_id = vote_link_id and post_user_id != vote_user_id");
					$rss_option = false;
					break;

				default:
					$view = 0;
					$page_title = sprintf(_('notas de %s'), $user->username);
					$globals['search_options']['u'] = $user->username;
					$where = "post_user_id=$user->id";
					$order_by = "ORDER BY post_id desc";
					$limit = "LIMIT $offset,$page_size";
					$rows = $db->get_var("SELECT count(*) FROM posts WHERE post_user_id=$user->id");
					$rss_option="sneakme_rss?user_id=$user->id";
			}
		}
}

if (isset($globals['canonical_server_name']) && $globals['canonical_server_name'] != get_server_name()) {
	$globals['noindex'] = true;
}

do_header($page_title, _('nótame'), get_posts_menu($tab_option, $user->username));

$conversation_extra = '';
if ($tab_option == 4) {
	if ($current_user->user_id == $user->id) {
		//$conversation_extra = ' ['.Post::get_unread_conversations($user->id).']';
		$conversation_extra = ' [<span id="p_c_counter">0</span>]';
		$whose = _('mías');
	} else {
		$whose = _('suyas');
	}
	$options = array(
		$whose => post_get_base_url($user->username),
		_('amigos') => post_get_base_url("$user->username/_friends"),
		_('favoritos') => post_get_base_url("$user->username/_favorites"),
		_('conversación').$conversation_extra => post_get_base_url("$user->username/_conversation"),
		_('votos') => post_get_base_url("$user->username/_votes"),
		sprintf(_('debates con %s'), $user->username) =>
				$globals['base_url'] . "between?type=posts&amp;u1=$current_user->user_login&amp;u2=$user->username",
		sprintf(_('perfil de %s'), $user->username) => get_user_uri($user->username),

	);
} elseif ($tab_option == 1 && $current_user->user_id > 0) {
	//$conversation_extra = ' ['.Post::get_unread_conversations($user->id).']';
	$conversation_extra = ' [<span id="p_c_counter">0</span>]';
	$view = 0;

	$options = array(
		_('todas') => post_get_base_url(''),
		_('amigos') => post_get_base_url("$current_user->user_login/_friends"),
		_('favoritos') => post_get_base_url("$current_user->user_login/_favorites"),
		_('conversación').$conversation_extra => post_get_base_url("$current_user->user_login/_conversation"),
		_('últimas imágenes') => "javascript:fancybox_gallery('post');",
		_('debates').'&nbsp;&rarr;' => $globals['base_url'] . "between?type=posts&amp;u1=$current_user->user_login",
	);
} else $options = false;
do_post_subheader($options, $view, $rss_option);


/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
//do_best_stories();
if (! $short_content) {
	do_best_posts();
	do_best_comments();
	do_banner_promotions();
	if ($tab_option < 4) {
		do_last_subs('published');
		do_last_blogs();
	}
}
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";
do_pages($rows, $page_size);

echo '<div class="notes">';


if ($current_user->user_id > 0) {
	echo '<div id="addpost"></div>';
	echo '<ol class="comments-list"><li id="newpost"></li></ol>'."\n";
}

if ($view != 4) {
	$posts = $db->object_iterator("SELECT".Post::SQL."INNER JOIN (SELECT post_id FROM posts $from WHERE $where $order_by $limit) as id USING (post_id)", 'Post');
	if ($posts) {
		$ids = array();
		echo '<ol class="comments-list">';
		$time_read = 0;
		foreach ($posts as $post) {
			if ($post_id > 0 && $user->id > 0 && $user->id != $post->author) {
				echo '<li>' . _('Error: nota no existente') . '</li>';
			} else {
				echo '<li>';
				$post->print_summary();
				if ($post->date > $time_read) $time_read = $post->date;
				echo '</li>';
				if (!$post_id) $ids[] = $post->id;
			}
		}

		echo "</ol>\n";

		if ($post_id > 0) {
			// Print share button
			echo '<div style="text-align:right">';
			$vars = array('link' => $globals['permalink'],
				'title' => $page_title);
			Haanga::Load('share.html', $vars);
			echo '</div>';

			print_answers($post_id, 1);

		} else {
			Haanga::Load('get_total_answers_by_ids.html', array('type' => 'post', 'ids' => implode(',', $ids)));
		}

		// Update conversation time
		if ($view == 3 && $time_read > 0 && $user->id == $current_user->user_id) {
			Post::update_read_conversation($time_read);
		}
		echo '</div>';
	}
} else {
	do_voted_posts();
}
do_pages($rows, $page_size);

echo '</div>';
if ($rows > 15) do_footer_menu();
do_footer();

function print_answers($id, $level, $visited = false) {
	// Print "conversation" for a given note
	global $db;

	if (! $visited) {
		$visited = array();
		$visited[] = $id;
	}
	$printed = array();

	$answers = $db->get_col("SELECT conversation_from FROM conversations WHERE conversation_type='post' and conversation_to = $id ORDER BY conversation_from asc LIMIT 100");
	$parent_reference = "/@\S+,$id/ui"; // To check that the notes references to $id

	if ($answers) {
		echo '<div style="padding-left: 5%; padding-top: 5px;">';
		echo '<ol class="comments-list">';
		foreach ($answers as $post_id) {
			if (in_array($post_id, $visited)) continue;
			$answer = Post::from_db($post_id);
			if (! $answer) continue;
			if ( $answer->user_level == 'autodisabled' || $answers->user_level == 'disabled') continue;

			// Check the post has a real reference to the parent (with the id), ignore othewrise
			if (! preg_match($parent_reference, $answer->content)) continue;

			echo '<li>';
			$answer->print_summary();
			echo '</li>';
			if ($level > 0) {
				$res = print_answers($answer->id, $level-1, array_merge($visited, $answers));
				$visited = array_merge($visited, $res);
			}
			$printed[] = $answer->id;
			$visited[] = $answer->id;
		}
		echo '</ol>';
		echo '</div>';
		if ($level == 0) {
			Haanga::Load('get_total_answers_by_ids.html', array('type' => 'post', 'ids' => implode(',', $printed)));
		}
	}
	return $printed;
}

function do_voted_posts() {

	global $db, $user, $offset, $page_size, $globals;

	$posts = $db->get_results("SELECT vote_link_id as id, vote_value as value FROM votes, posts WHERE vote_type='posts' and vote_user_id=$user->id  and post_id = vote_link_id and post_user_id != vote_user_id ORDER BY vote_date DESC LIMIT $offset,$page_size");

	echo '<ol class="comments-list">';
	$time_read = 0;
	foreach ($posts as $p) {
		$post = Post::from_db($p->id);
		if ($p->value > 0) $color = '#00d';
		else $color = '#f00';
		echo '<li>';
		$post->print_summary();
		if ($post->date > $time_read) $time_read = $post->date;
		echo '<div class="box" style="margin:0 0 -16px 0;background:'.$color.';position:relative;top:-34px;left:30px;width:30px;height:16px;border-color:'.$color.';opacity: 0.5"></div>';
		echo '</li>';
	}

	echo "</ol>\n";
	echo '</div>';
}