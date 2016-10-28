<?php
// The Meneame source code is Free Software, Copyright (C) 2005-2011 by
// Ricardo Galli <gallir at gmail dot com> and Menéame Comunicacions S.L.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.

// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include_once(__DIR__.'/config.php');
include(mnminclude.'html1.php');

$globals['cache-control'][] = 'max-age=3';

$url_args = $globals['path'];

if ($url_args[0] == 'story') {
	array_shift($url_args); // Discard "story", TODO: but it should be discarded in dispatch and submnm
}

$argc = 0;
if (!isset($_REQUEST['id']) && $url_args[0] && !ctype_digit($url_args[0])) { // Compatibility with story.php?id=x and /story/x
	$link = Link::from_db($url_args[0], 'uri');
	if (! $link ) {
		do_error(_('noticia no encontrada'), 404);
	}
} else {
	if (isset($_REQUEST['id'])) $id = intval($_REQUEST['id']);
	else $id = intval($url_args[0]);
	if($id > 0 && ($link = Link::from_db($id)) ) {
		// Redirect to the right URL if the link has a "semantic" uri
		if (!empty($link->uri)) {
			header ('HTTP/1.1 301 Moved Permanently');
			header('Location: ' . $link->get_permalink());
			die;
		}
	} else {
		do_error(_('noticia no encontrada'), 404);
	}
}

// Check the link belong to the current site
$site_id = SitesMgr::my_id();
if ($link->is_sub && $site_id != $link->sub_id && (empty($link->sub_status) || ! $link->allow_main_link) ) {
	// The link does not correspond to the current site, find one
	header ('HTTP/1.1 301 Moved Permanently');
	header ('Location: ' . $link->get_canonical_permalink());
	die;
}


if ($link->is_discarded()) {
	// Dont allow indexing of discarded links, nor anonymous users after 90 days
	if ($globals['bot'] || (! $current_user->authenticated && $globals['now'] - $link->sent_date > 86400 * 90) ) not_found();
	$globals['ads'] = false;
	$globals['noindex'] = true;
}

$total_pages = 1 + intval($link->comments / $globals['comments_page_size']);
// Check for a page number which has to come to the end, i.e. ?id=xxx/P or /story/uri/P
$no_page = true;
$show_relevants = true; // Show highlighted comments
if (($argc = count($url_args)) > 1) {
	// Dirty trick to redirect to a comment' page
	if (preg_match('/^c0\d+$/', $url_args[1])) {
		// Link to comment in its page
		$c = intval(substr($url_args[1], 2));
		if (! $c > 0 || $c > $link->comments) {
			header ('HTTP/1.1 303 Load');
			header('Location: ' . $link->get_permalink());
			die;
		}

		$globals['referenced_comment'] = $c; // This comment has to be displayed
		$no_page = false;
		unset($url_args[1]);
	} elseif ((int) $url_args[$argc-1] > 0) {
		$current_page = intval($url_args[$argc-1]);
		if ($current_page > $total_pages) {
			do_error(_('página inexistente'), 404);
		}
		if ($argc == 2) {
			// If there is no other previous option, this the canonical "page"
			$canonical_page = $current_page;
		}
		array_pop($url_args);
		$no_page = false;
		$show_relevants = false;
	}
}

// Change to a min_value is times is changed for the current link_status
if ($globals['time_enabled_comments_status'][$link->status]) {
	$globals['time_enabled_comments'] = min($globals['time_enabled_comments_status'][$link->status],
											$globals['time_enabled_comments']);
}

// Check for comment post
// TODO: don't redirect, force to show the comment if it threaded
if ($_POST['process']=='newcomment') {
	$new_comment_error = Comment::save_from_post($link);
}

$offset = 0;
$limit = '';

if (empty($url_args[1])) {
	if ($current_user->user_id && User::get_pref($current_user->user_id, 'com_order')) {
		// Check the preference of the user
		$url_args[1] = 'standard';
	} else {
		// Use the mode defined in the sub
		$url_args[1] = $link->page_mode;
	}
	$globals['page_base'] = '';
} else {
	$globals['page_base'] = '/'.$url_args[1];
}

// Increase click counter if it's without external link.
if (empty($link->url)) {
	$link->add_click(true); // Called with true so the probably nonexistent k is not checked
}

switch ($url_args[1]) {
	case '':
	case 'interview':
	case 'threads':
		$tab_option = 10;
		break;
	case 'default':
	case 'standard':
		$tab_option = 1;
		$order_field = 'comment_order';


		if (!empty($globals['referenced_comment'])) {
			$canonical_page = $current_page = intval(($globals['referenced_comment']-1)/$globals['comments_page_size']) + 1;
		}

		if ($current_user->user_id > 0 && User::get_pref($current_user->user_id, 'last_com_first')) {
			$last_com_first = true;
		} else {
			$last_com_first = false;
		}

		if ($globals['comments_page_size'] && $link->comments > $globals['comments_page_size']) {
			if ($no_page) {
				if ($last_com_first) {
					$canonical_page = $current_page = ceil($link->comments/$globals['comments_page_size']);
				} else {
					$canonical_page = $current_page = 1;
				}
			}
			$offset=($current_page-1)*$globals['comments_page_size'];
			$limit = "LIMIT $offset,".$globals['comments_page_size'];
		} else {
			$canonical_page = 1;
		}

		if ($canonical_page > 1) {
			$globals['extra_head'] .= '<link rel="prev" href="'.$link->get_canonical_permalink($canonical_page-1).'" />';
		}
		if ($canonical_page < $total_pages) {
			$globals['extra_head'] .= '<link rel="next" href="'.$link->get_canonical_permalink($canonical_page+1).'" />';
		}

		// Geo check
		// Don't show it if it's a mobile browser
		if(!$globals['mobile'] && $globals['google_maps_in_links'] && $globals['google_maps_api']) {
			$link->geo = true;
			$link->latlng = $link->get_latlng();
			if ($link->latlng) {
				geo_init('geo_coder_load', $link->latlng, 5, $link->status);
			} elseif ($link->is_map_editable()) {
				geo_init(null, null);
			}
		}
		break;
	case 'best-comments':
		$tab_option = 2;
		$order_field = 'comment_karma desc, comment_id asc';
		if (!$current_page) $current_page = 1;
		$offset=($current_page-1)*$globals['comments_page_size'];
		$limit = "LIMIT $offset,".$globals['comments_page_size'];
		break;
	case 'voters':
		$tab_option = 3;
		$globals['noindex'] = true;
		break;
	case 'log':
		$tab_option = 4;
		break;
	case 'votes_raw':
		$globals['noindex'] = true;
		print_votes_raw($link);
		die;
	case 'sneak':
		$tab_option = 5;
		$globals['noindex'] = true;
		break;
	case 'favorites':
		$tab_option = 6;
		$globals['noindex'] = true;
		break;
	case 'related':
		$tab_option = 8;
		break;
	case 'answered':
		$tab_option = 9;
		$globals['noindex'] = true;
		break;
	case 'qa':
		$tab_option = 100;
		$globals['noindex'] = true;
		$globals['ads'] = false;
		do_qanda_text($link);
		exit(0);
		break;
	default:
		do_error(_('página inexistente'), 404);
}

// Set globals
$globals['link'] = $link;
$globals['link_id'] = $link->id;
$globals['permalink'] = $globals['link']->get_permalink();

// to avoid search engines penalisation
if ($link->status != 'published' && $globals['now'] - $link->date > 864000) {
	$globals['noindex'] = true;
}

if ($globals['ads'] && preg_match('/nsfw/i', $link->title)) $globals['ads'] = false;

do_modified_headers($link->modified, $current_user->user_id.'-'.$globals['link_id'].'-'.$link->status.'-'.$link->comments.'-'.$link->modified);

// Enable user AdSense
// do_user_ad: 0 = noad, > 0: probability n/100
if ($globals['ads'] && $link->status == 'published' && $link->user_karma > 6 && !empty($link->user_adcode)) {
	$globals['do_user_ad'] = $link->user_karma;
	$globals['user_adcode'] = $link->user_adcode;
	$globals['user_adchannel'] = $link->user_adchannel;
}

if ($link->status != 'published')
	$globals['do_vote_queue']=true;
if (!empty($link->tags))
	$globals['tags']=$link->tags;

// Add canonical address
$globals['extra_head'] .= '<link rel="canonical" href="'.$link->get_canonical_permalink($canonical_page).'" />';

// add also a rel to the comments rss
$globals['extra_head'] .= '<link rel="alternate" type="application/rss+xml" title="'._('comentarios esta noticia').'" href="'.$globals['scheme'].'//'.get_server_name().$globals['base_url'].'comments_rss?id='.$link->id.'" />';

if ($link->has_thumb()) {
	$globals['thumbnail'] = $link->media_url;
}

$globals['description'] = text_to_summary($link->content, 250);

do_header($link->title, 'post');

// Show the error if the comment couldn't be inserted
if (!empty($new_comment_error)) {
	add_javascript('mDialog.notify("'._('Aviso'). ": $new_comment_error".'", 5);');
}

do_tabs("main",_('noticia'), true);
print_story_tabs($tab_option);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_sub_message_right();
do_banner_right();
// GEO
if ($link->latlng) {
	echo '<div id="map" style="width:300px;height:200px;margin-bottom:25px;">&nbsp;</div>';
}
if (! $current_user->user_id) {
	do_most_clicked_stories();
}
do_banner_promotions();
if (! $current_user->user_id) {
	do_best_stories();
}
do_rss_box();
echo '</div>';
/*** END SIDEBAR ***/

echo '<div id="newswrap">';
$link->print_summary();

switch ($tab_option) {
case 1:
case 2:
	echo '<div class="comments">';

	if($tab_option == 1) {
		print_external_analysis($link);
		if ($show_relevants || $no_page) {
			print_relevant_comments($link);
		}
	} else {
		$last_com_first = false;
	}
	do_comment_pages($link->comments, $current_page, $last_com_first);

	$update_comments = false;
	$comments = $db->get_results("SELECT".Comment::SQL."WHERE comment_link_id=$link->id ORDER BY $order_field $limit", "Comment");
	if ($comments) {
		$order = $offset + 1;
		$prev = false;
		echo '<ol class="comments-list">';
		foreach($comments as $comment) {
			// Check the comment order is correct, otherwise, force an update
			if ($tab_option == 1) {
				if ($comment->order != $order) {
					if ($prev) {
						$prev->update_order();
					}
					syslog(LOG_INFO, "Updating order for $comment->id, order: $comment->order -> $order");
					$comment->update_order();
					$update_comments = true;
					$prev = false;
				} else {
					$prev = $comment;
				}
			}

			echo '<li>';
			$comment->link_object = $link;
			$comment->print_summary(2500, true);
			echo '</li>';
			$order++;
		}
		echo '</ol>';
	}

	if($tab_option == 1) {
		if ($update_comments) {
			$link->update_comments();
		}
	}

	/* Force to show the last ad for anonymous users only */
	if (! $current_user->user_id) {
		$counter = $page_size = $globals['comments_page_size'];
		Haanga::Safe_Load('private/ad-interlinks.html', compact('counter', 'page_size'));
	}

	do_comment_pages($link->comments, $current_page, $last_com_first);

	if ($link->comments > 5) {
		add_javascript('get_total_answers("comment","'.$order_field.'",'.$link->id.','.$offset.','.$globals['comments_page_size'].');');
	}

	Comment::print_form($link);
	echo '</div>';
	break;



case 3:
	// Show voters
	echo '<div class="voters" id="voters">';

	echo '<div id="voters-container" style="padding: 10px;">';
	if ($globals['link']->sent_date < $globals['now'] - 60*86400) { // older than 60 days
		echo _('Noticia antigua, datos de votos archivados');
	} else {
		include(mnmpath.'/backend/meneos.php');
	}
	echo '</div><br />';
	echo '</div>';
	break;



case 6:
	// Show favorited by
	echo '<div class="voters" id="voters">';

	echo '<div id="voters-container">';
	include(mnmpath.'/backend/get_link_favorites.php');
	echo '</div><br />';
	echo '</div>';
	break;



case 4:
	// Show logs

	$globals['extra_js'][] = 'jquery.flot.min.js';
	$globals['extra_js'][] = 'jquery.flot.time.min.js';

	$logs = $db->get_results("select logs.*, UNIX_TIMESTAMP(logs.log_date) as ts, user_id, user_login, user_level, user_avatar from logs, users where log_type in ('link_new', 'link_publish', 'link_discard', 'link_edit', 'link_geo_edit', 'link_depublished') and log_ref_id=$link->id and user_id= log_user_id order by log_date desc");

	foreach ($logs as $log) {
		$log->annotation = Log::has_annotation($log->log_id);
	}

	// Show karma logs from annotations
	$annotations = $link->read_annotation("link-karma");

	$vars = compact('link', 'logs', 'annotations');
	Haanga::Load("story/link_logs.html", $vars);
	break;



case 5:
	// Micro sneaker
	Haanga::Load('story/link_sneak.html', compact('link'));
	break;

case 8:
	$related = $link->get_related(10);
	if ($related) {
		Haanga::Load("story/related.html", compact('related', 'link'));
	}
	break;

case 9:
	echo '<div class="comments">';

	$sql = "SELECT conversation_to as id, count(*) as t FROM conversations, comments WHERE comment_link_id = $link->id AND comment_id = conversation_to AND conversation_type='comment' GROUP BY conversation_to ORDER BY t desc, id asc LIMIT ".$globals['comments_page_size'] ;

	$results = $db->get_results($sql);
	if ($results) {
		$ids = array();
		echo '<ol class="comments-list">';
		$max = 0;
		foreach($results as $res) {
			if ($res->t > $max) $max = $res->t;
			if ($max > 1 && $res->t < 2) break;
			$ids[] = $res->id;
			$comment = Comment::from_db($res->id);
			echo '<li>';
			$comment->link_object = $link;
			$comment->print_summary(2500, true);
			echo '</li>';
		}
		echo '</ol>';
		Haanga::Load('get_total_answers_by_ids.html', array('type' => 'comment', 'ids' => implode(',', $ids)));
		Comment::print_form($link);
	}

	echo '</div>';
	break;

/////////////// TODO: in progress
case 10:
	echo '<div class="comments">';
	include_once(mnminclude.'commenttree.php');
	$tree = new CommentTree();

	if (!$current_page) $current_page = 1;
	$offset=($current_page-1)*$globals['comments_page_size'];
	$limit = $globals['comments_page_size'];
	$global_limit = $limit * 2; // The limit including references

	if ($show_relevants || $no_page) {
		print_external_analysis($link);
		print_relevant_comments($link);
	}

	if ($link->page_mode == 'interview') {
		$sql = "select t1.comment_id as parent, t1.w1 as w1, t2.comment_id as child, t2.comment_karma + 200 * (t2.comment_user_id = $link->author) as w2 FROM comments as t0 INNER JOIN (select comment_id, comment_karma + 200 * (comment_user_id = $link->author) as w1 from comments WHERE comment_link_id = $link->id order by w1 desc LIMIT $offset, $limit) t1 ON t1.comment_id = t0.comment_id LEFT JOIN (conversations as c, comments as t2) ON conversation_type='comment' and conversation_to = t0.comment_id and c.conversation_from = t2.comment_id order by w1 desc, w2 desc LIMIT $global_limit";

		$res = $db->get_results($sql);
		if ($res) {
			foreach ($res as $c) {
				$tree->addByIds($c->parent, $c->child);
			}
		}
		$sort_roots = true;
	} else {
		$sql = "select t1.comment_id as parent, c.conversation_from as child FROM comments as t0 INNER JOIN (select comment_id from comments WHERE comment_link_id = $link->id order by comment_id asc LIMIT $offset, $limit) t1 ON t1.comment_id = t0.comment_id LEFT JOIN conversations c ON c.conversation_type='comment' and c.conversation_to = t0.comment_id order by t0.comment_id, c.conversation_from LIMIT $global_limit";
		$res = $db->get_results($sql);
		if ($res) {
			foreach ($res as $c) {
				$tree->addByIds($c->parent, $c->child);
			}
		}
		$sort_roots = false;
	}

	// A /url/c0#comment_order all, we add it
	if (!empty($globals['referenced_comment'])) {
		$order = intval($globals['referenced_comment']);
		$pair = $db->get_row("select comment_id as child, conversation_to as parent FROM comments LEFT JOIN (conversations) ON conversation_type='comment' and conversation_from = comment_id WHERE comment_link_id = $link->id and comment_order = $order");
		if ($pair) {
			$tree->addByIds($pair->parent, $pair->child);
		}
	}

	Comment::print_tree($tree, $link, 500, $sort_roots);

	/* Force to show the last ad for anonymous users only */
	if (! $current_user->user_id) {
		$counter = $page_size = $globals['comments_page_size'];
		Haanga::Safe_Load('private/ad-interlinks.html', compact('counter', 'page_size'));
	}

	do_comment_pages($link->comments, $current_page, false);
	Comment::print_form($link);

	echo '</div>';
	break;
}

echo '</div>';

$globals['tag_status'] = $globals['link']->status;
do_footer();
exit(0);


function print_story_tabs($option) {
	global $globals, $db, $link, $current_user;

	$active = array();
	$active[$option] = 'selected ';

	echo '<ul class="subheader">';
	echo '<li class="'.$active[1].'"><a href="'.$globals['permalink'].'/standard">'._('ordenados'). '</a></li>';
	echo '<li class="'.$active[10].'"><a href="'.$globals['permalink'].'/threads">'._('hilos'). '</a></li>';
	echo '<li class="'.$active[2].'"><a href="'.$globals['permalink'].'/best-comments">'._('+ valorados'). '</a></li>';
	//echo '<li class="'.$active[9].'wideonly"><a href="'.$globals['permalink'].'/answered">'._('+ respondidos'). '</a></li>';
	if (!$globals['bot']) { // Don't show "empty" pages to bots, Google can penalize too
		if ($globals['link']->sent_date > $globals['now'] - 86400*60) { // newer than 60 days
			echo '<li class="'.$active[3].'"><a href="'.$globals['permalink'].'/voters">'._('votos'). '</a></li>';
		}
		if ($globals['link']->sent_date > $globals['now'] - 86400*30) { // newer than 30 days
			echo '<li class="'.$active[4].'"><a href="'.$globals['permalink'].'/log">'._('registros'). '</a></li>';
		}
		if ($globals['link']->date > $globals['now'] - $globals['time_enabled_comments']) {
			echo '<li class="'.$active[5].'wideonly"><a href="'.$globals['permalink'].'/sneak">&micro;&nbsp;'._('fisgona'). '</a></li>';
		}

	}
	if ($current_user->user_id > 0) {
		if (($c = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_type = 'link' and favorite_link_id=$link->id")) > 0) {
			echo '<li class="'.$active[6].'wideonly"><a href="'.$globals['permalink'].'/favorites">'._('favoritos')."&nbsp;($c)</a></li>";
		}
	}
	echo '<li class="'.$active[8].'wideonly"><a href="'.$globals['permalink'].'/related">'._('relacionadas'). '</a></li>';
	echo '</ul>';
}

function do_comment_pages($total, $current, $reverse = true) {
	global $db, $globals;

	if ( ! $globals['comments_page_size'] || $total <= $globals['comments_page_size']) return;

	$query = $globals['permalink'] . $globals['page_base'];

	$total_pages=ceil($total/$globals['comments_page_size']);
	if (! $current) {
		if ($reverse) $current = $total_pages;
		else $current = 1;
	}

	echo '<div class="pages">';

	if($current==1) {
		echo '<span class="nextprev">&#171;</span>';
	} else {
		$i = $current-1;
		echo '<a href="'.get_comment_page_url($i, $total_pages, $query, $reverse).'" rel="prev">&#171;</a>';
	}


	$dots_before = $dots_after = false;
	for ($i=1;$i<=$total_pages;$i++) {
		if($i==$current) {
			echo '<span class="current">'.$i.'</span>';
		} else {
			if ($total_pages < 7 || abs($i-$current) < 1 || $i < 3 || abs($i-$total_pages) < 2) {
				echo '<a href="'.get_comment_page_url($i, $total_pages, $query, $reverse).'" title="'._('ir a página')." $i".'">'.$i.'</a>';
			} else {
				if ($i<$current && !$dots_before) {
					$dots_before = true;
					echo '<span>&hellip;</span>';
				} elseif ($i>$current && !$dots_after) {
					$dots_after = true;
					echo '<span>&hellip;</span>';
				}
			}
		}
	}

	if($current<$total_pages) {
		$i = $current+1;
		echo '<a href="'.get_comment_page_url($i, $total_pages, $query, $reverse).'" rel="next">&#187;</a>';
	} else {
		echo '<span class="nextprev">&#187;</span>';
	}
	echo '</div>';

}

function get_comment_page_url($i, $total, $query, $reverse = false) {
	global $globals;
	if ($i == $total && $reverse) return $query;
	elseif ($i == 1 && ! $reverse) return $query;
	else return $query.'/'.$i;
}

function print_external_analysis($link) {
	$data = Annotation::from_db("analysis_$link->id");
	if ($data) {
		$objects = json_decode($data->text);
		Haanga::Load('link_external_analysis.html', compact('objects'));
	}
}

function print_relevant_comments($link) {
	global $globals, $db;

	if ($link->comments < 10 ) return;
	if ($link->comments > 30 && $globals['now'] - $link->date < 86400*4) $do_cache = true;
	else $do_cache = false;

	if($do_cache) {
		$key = 'r_s_c_'.$globals['v'].'_'.$link->id;
		if(memcache_mprint($key)) return;
	}

	$karma = intval($globals['comment_highlight_karma']/2);
	$limit = min(15, intval($link->comments/10));

	// For the SQL
	$extra_limit = $limit * 2;
	$min_len = 32;
	$min_karma = max(20, $karma/2);
	$min_votes = 4;
	$check_vote = $link->date - ($globals['now'] - $globals['time_enabled_votes']);

	$now = intval($globals['now']/60) * 60;
	$res = $db->get_results("select comment_id, comment_order, comment_karma, comment_karma + comment_order * 0.7 as val, length(comment_content) as comment_len, user_id, user_avatar, vote_value from comments LEFT JOIN votes ON ($check_vote > 0 and vote_type = 'links' and vote_link_id = comment_link_id and vote_user_id = comment_user_id), users where comment_link_id = $link->id and comment_votes >= $min_votes and comment_karma > $min_karma and length(comment_content) > $min_len and comment_user_id = user_id order by val desc limit $extra_limit");

	function cmp_comment_val($a, $b) {
		if ($a->val == $b->val) return 0;
		return ($a->val < $b->val) ? 1 : -1;
	}

	if ($res) {
		$objects = array();
		$self = false;
		$link_url = $link->get_relative_permalink();
		foreach ($res as $comment) {
			// The commenter has voted negative
			if ($comment->vote_value < 0 && $comment->comment_len > 60) {
				$comment->val *= 2;
				// If the link has many negatives ("warned"), add little more weight to criticism
				if ($link->has_warning) {
					$comment->val *= 1.5;
				}
			}
			// Gives a little advantage to larger comments
			$comment->val *=  min(1.5, log($comment->comment_len, 10) / 1.8);
		}
		usort($res, "cmp_comment_val");
		foreach ($res as $comment) {
			$obj = new stdClass();
			$obj->id = $comment->comment_id;
			$obj->order = $comment->comment_order;
			$obj->link_id = $link->id;
			$obj->link_url = $link_url;
			$obj->user_id = $comment->user_id;
			$obj->avatar = $comment->user_avatar;
			$obj->vote = $comment->vote_value;
			$obj->val = $comment->val;
			$obj->karma = $comment->comment_karma;
			$objects[] = $obj;
			if (! $self
					&& $obj->vote < 0
					&& $link->negatives < $link->votes * 0.5 // Don't show negative comment if already has many
					&& (count($objects) < 6 || $comment->comment_karma > $globals['comment_highlight_karma'])
					&& count($res) >= count($objects)
				) {
				// Show the most negative relevant comment
				$self = get_highlighted_comment($obj);
				$obj->summary = true;
			}
			if (count($objects) > $limit) break;
		}
		if (! $self && count($objects) > 5 && $objects[0]->val > $globals['comment_highlight_karma'] * 1.5) {
			$self = get_highlighted_comment($objects[0]);
			$objects[0]->summary = true;
		}
		$output = Haanga::Load('relevant_comments.html', compact('objects', 'link_url', 'self'), true);
		echo $output;
		if($do_cache) {
			memcache_madd($key, $output, 300);
		}
	}
}

function get_highlighted_comment($obj) {
	// Read the object for printing the summary
	$self = Comment::from_db($obj->id);
	$self->link_id = $obj->link_id;
	$self->link_permalink =  $obj->link_url;
	// Simplify text of the comment
	$self->prepare_summary_text(1000);
	if ($self->is_truncated) {
		$self->txt_content .= '...';
		$self->is_truncated = false;
	}
	$self->media_size= 0;
	$self->vote = $obj->vote;
	$self->can_edit = false;
	return $self;
}

function print_votes_raw($link) {
	global $globals, $db;

	header("Content-Type: text/plain");

	$votes = $db->get_results("SELECT vote_value, user_login, user_karma, UNIX_TIMESTAMP(vote_date) as ts FROM votes LEFT JOIN users on (user_id = vote_user_id) WHERE vote_type='links' and vote_link_id=$link->id ORDER BY vote_date");

	if (! $votes) return;

	foreach ($votes as $v) {
		printf("%s\t%d\t%s\t%3.1f\n", date("c", $v->ts), $v->vote_value, $v->user_login, $v->user_karma);
	}
}

/* Get a list of the answers and their questions */
function get_qanda($link) {
	include_once(mnminclude.'commenttree.php');
	global $db;

	$results = array();
	$a_ids = $db->get_col("select comment_id from comments where comment_link_id = $link->id and comment_user_id = $link->author order by comment_id asc");
	if ($a_ids) {
		foreach ($a_ids as $a_id) {
			$a = Comment::from_db($a_id);
			$qa = new CommentQA($a);
			$q_ids = $db->get_col("select conversation_to from conversations where conversation_type = 'comment' and conversation_from = $a_id and conversation_to > 0 order by conversation_to asc");
			if ($q_ids) {
				foreach ($q_ids as $q_id) {
					$q = Comment::from_db($q_id);
					$qa->add_question($q);
				}
			}
			$results[] = $qa;
		}
	}
	return $results;
}

/* Show a very simple list of questions and answers
   ready to copy&paste for eldiario.es
*/
function do_qanda_text($link) {
	global $globals, $db;

	$cleaner = function ($comment) use ($link) {
		$comment->content = preg_replace('/{.{1,10}?}|^( *#\d+)+/', '', $comment->content);
		$comment->content = preg_replace_callback('/#(\d+)/', function ($matches) use ($link) {
			global $db;

			$order = $matches[1];
			if ($order == 0) {
				return "<em>@$link->username</em>";
			}

			$username = $db->get_var("select user_login from users, comments where user_id = comment_user_id and comment_link_id = $link->id and comment_order = $order");
			return "<em>@$username</em>";

		}, $comment->content);
		$comment->content = preg_replace('/[\n]{3,}/', "\n", $comment->content);
		$comment->content = $comment->to_html($comment->content);
	};

	$qas = get_qanda($link);

	do_header(_('Q&A simple').": $link->title", 'post');

	foreach ($qas as $qa) {
		$a = $qa->answer;
		foreach ($qa->questions as $q) {
			$cleaner($q);
		}
		$cleaner($a);
	}

	$link->permalink = $link->get_permalink();
	Haanga::Load('comment_qa_simple.html', compact('qas', 'link'));
	do_footer();
}
