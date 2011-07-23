<?
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

include('config.php');
include(mnminclude.'html1.php');

mobile_redirect();

$globals['cache-control'][] = 'max-age=3';

if (!isset($_REQUEST['id']) && !empty($_SERVER['PATH_INFO'])) {
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO'],  3, PREG_SPLIT_NO_EMPTY);
	$link = Link::from_db($url_args[0], 'uri');
	if (! $link ) {
		do_error(_('noticia no encontrada'), 404);
	}
} else {
	$url_args = preg_split('/\/+/', $_REQUEST['id'], 3, PREG_SPLIT_NO_EMPTY);
	if(is_numeric($url_args[0]) && $url_args[0] > 0 && ($link = Link::from_db($url_args[0])) ) {
		// Redirect to the right URL if the link has a "semantic" uri
		if (!empty($link->uri) && !empty($globals['base_story_url'])) {
			header ('HTTP/1.1 301 Moved Permanently');
			if (!empty($url_args[1])) $extra_url = '/' . urlencode($url_args[1]);
			header('Location: ' . $link->get_permalink(). $extra_url);
			die;
		}
	} else {
		do_error(_('argumentos no reconocidos'), 404);
	}
}


if ($link->is_discarded()) {
	// Dont allow indexing of discarded links
	if ($globals['bot']) not_found();
} else {
	//Only shows ads in non discarded images
	$globals['ads'] = true;
}


// Check for a page number which has to come to the end, i.e. ?id=xxx/P or /story/uri/P
$last_arg = count($url_args)-1;
if ($last_arg > 0) {
	// Dirty trick to redirect to a comment' page
	if (preg_match('/^000/', $url_args[$last_arg])) {
		header ('HTTP/1.1 301 Moved Permanently');
		if ($url_args[$last_arg] > 0 && $url_args[$last_arg] <= $link->comments) {
			header('Location: ' . $link->get_permalink().get_comment_page_suffix($globals['comments_page_size'], (int) $url_args[$last_arg], $link->comments).'#c-'.(int) $url_args[$last_arg]);
		} else {
			header('Location: ' . $link->get_permalink());
		}
		die;
	}
	if ($url_args[$last_arg] > 0) {
		$requested_page = $current_page =  (int) $url_args[$last_arg];
		array_pop($url_args);
	}
}

// Change to a min_value is times is changed for the current link_status
if ($globals['time_enabled_comments_status'][$link->status]) {
	$globals['time_enabled_comments'] = min($globals['time_enabled_comments_status'][$link->status],
											$globals['time_enabled_comments']);
}

// Check for comment post
if ($_POST['process']=='newcomment') {
	$new_comment_error = Comment::save_from_post($link);
}

$offset = 0;
$limit = '';
switch ($url_args[1]) {
	case '':
		$tab_option = 1;
		$order_field = 'comment_order';

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
		if ($globals['comments_page_size'] && $link->comments > $globals['comments_page_size']*$globals['comments_page_threshold']) {
			if (!$current_page) $current_page = ceil($link->comments/$globals['comments_page_size']);
			$offset=($current_page-1)*$globals['comments_page_size'];
			$limit = "LIMIT $offset,".$globals['comments_page_size'];
		}
		break;
	case 'best-comments':
		$tab_option = 2;
		if ($globals['comments_page_size'] > 0 ) $limit = 'LIMIT ' . $globals['comments_page_size'];
		$order_field = 'comment_karma desc, comment_id asc';
		break;
	case 'voters':
		$tab_option = 3;
		break;
	case 'log':
		$tab_option = 4;
		break;
	case 'sneak':
		$tab_option = 5;
		break;
	case 'favorites':
		$tab_option = 6;
		break;
	case 'trackbacks':
		$tab_option = 7;
		break;
	case 'related':
		$tab_option = 8;
		break;
	case 'answered':
		$tab_option = 9;
		break;
	default:
		do_error(_('página inexistente'), 404);
}

// Set globals
$globals['link'] = $link;
$globals['link_id'] = $link->id;
$globals['link_permalink'] = $globals['link']->get_permalink();

// to avoid search engines penalisation
if ($tab_option != 1 || $link->status == 'discard') {
	$globals['noindex'] = true;
}

do_modified_headers($link->modified, $current_user->user_id.'-'.$globals['link_id'].'-'.$link->comments.'-'.$link->modified);

// Enable user AdSense
// do_user_ad: 0 = noad, > 0: probability n/100
if ($link->status == 'published' && $link->user_karma > 7 && !empty($link->user_adcode)) {
	$globals['do_user_ad'] = $link->user_karma;
	$globals['user_adcode'] = $link->user_adcode;
	$globals['user_adchannel'] = $user->adchannel;
}

if ($link->status != 'published')
	$globals['do_vote_queue']=true;
if (!empty($link->tags))
	$globals['tags']=$link->tags;

// Add canonical address
$globals['extra_head'] = '<link rel="canonical" href="'.$globals['link_permalink'].'" />'."\n";

// add also a rel to the comments rss
$globals['extra_head'] .= '<link rel="alternate" type="application/rss+xml" title="'._('comentarios esta noticia').'" href="http://'.get_server_name().$globals['base_url'].'comments_rss2.php?id='.$link->id.'" />'."\n";

$globals['thumbnail'] = $link->has_thumb();

$globals['description'] = _('Autor') . ": $link->username, " . _('Resumen') . ': '. text_to_summary($link->content, 250);

do_header($link->title, 'post');

// Show the error if the comment couldn't be inserted
if (!empty($new_comment_error)) {
	echo '<script type="text/javascript">';
	echo '$(function(){mDialog.notify(\''._('Aviso'). ": $new_comment_error".'\', 5)});';
	echo '</script>';
}

do_tabs("main",_('noticia'), true);
print_story_tabs($tab_option);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
// GEO
if ($link->latlng) {
	echo '<div id="map" style="width:300px;height:200px;margin-bottom:25px;">&nbsp;</div>'."\n";
}
/*
if ($link->comments > 15) {
	do_best_story_comments($link);
}
*/
if (! $current_user->user_id) {
	do_best_stories();
}
do_rss_box();
do_banner_promotions();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";
$link->print_summary();

switch ($tab_option) {
case 1:
case 2:
	echo '<div class="comments">';

	print_relevant_comments($link);
	if($tab_option == 1) {
		do_comment_pages($link->comments, $current_page);
	}

	$comments = $db->object_iterator("SELECT".Comment::SQL."WHERE comment_link_id=$link->id ORDER BY $order_field $limit", "Comment");
	if ($comments) {
		echo '<ol class="comments-list">';
		foreach($comments as $comment) {
			echo '<li>';
			$comment->print_summary($link, 2500, true);
			echo '</li>';
			echo "\n";
		}
		echo "</ol>\n";
	}

	if($tab_option == 1) {
		do_comment_pages($link->comments, $current_page);
	}

	if ($link->comments > 5) {
		echo '<script type="text/javascript">';
		echo '$(window).load(get_total_answers("comment","'.$order_field.'",'.$link->id.','.$offset.','.$globals['comments_page_size'].'));';
		echo '</script>';
	}


	Comment::print_form($link);
	echo '</div>' . "\n";
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



case 7:
	// Show trackback
	echo '<div class="voters" id="voters">';

	echo '<a href="'.$link->get_trackback().'" title="'._('URI para trackbacks').'" class="tab-trackback-url"><img src="'.$globals['base_static'].'img/common/permalink.gif" alt="'._('enlace trackback').'" width="16" height="9"/> '._('dirección de trackback').'</a>' . "\n";

	echo '<fieldset><legend>'._('lugares que enlazan esta noticia').'</legend>';
	echo '<ul class="tab-trackback">';

	$trackbacks = $db->get_col("SELECT SQL_CACHE trackback_id FROM trackbacks WHERE trackback_link_id=$link->id AND trackback_type='in' and trackback_status = 'ok' ORDER BY trackback_date DESC limit 50");
	if ($trackbacks) {
		$trackback = new Trackback;
		foreach($trackbacks as $trackback_id) {
			$trackback->id=$trackback_id;
			$trackback->read();
			echo '<li class="tab-trackback-entry"><a href="'.$trackback->url.'" rel="nofollow">'.$trackback->title.'</a> ['.preg_replace('/https*:\/\/([^\/]+).*/', "$1", $trackback->url).']</li>' . "\n";
		}
	}
	echo '<li class="tab-trackback-technorati"><a href="http://technorati.com/search/'.urlencode($globals['link_permalink']).'">Technorati</a></li>' . "\n";
	echo '<li class="tab-trackback-google"><a href="http://blogsearch.google.com/blogsearch?hl=es&amp;q=link%3A'.urlencode($globals['link_permalink']).'">Google</a></li>' . "\n";

	echo '</ul>';
	echo '</fieldset>';
	echo '</div>';
	break;

case 8:
	$related = $link->get_related(10);
	if ($related) {
		Haanga::Load("story/related.html", compact('related', 'link'));
	}
	break;

case 9:
	echo '<div class="comments">';

	print_relevant_comments($link);

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
			$comment->print_summary($link, 2500, true);
			echo '</li>';
			echo "\n";
		}
		echo "</ol>\n";
		Haanga::Load('get_total_answers_by_ids.html', array('type' => 'comment', 'ids' => implode(',', $ids)));
		Comment::print_form($link);
	}

	echo '</div>' . "\n";
	break;

}
echo '</div>';

echo '<!--'."\n".'<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"'."\n";
echo '	xmlns:dc="http://purl.org/dc/elements/1.1/"' . "\n";
echo '	xmlns:trackback="http://madskills.com/public/xml/rss/module/trackback/">'."\n";
echo '	<rdf:Description rdf:about="'.$globals['link_permalink'].'"'."\n";
echo '		dc:identifier="'.$globals['link_permalink'].'"'."\n";
echo '		dc:title="'.$link->title.'"'."\n";
echo '	trackback:ping="'.$link->get_trackback().'" />'."\n";
echo '</rdf:RDF>'."\n".'-->'."\n";


$globals['tag_status'] = $globals['link']->status;
do_footer();
exit(0);


function print_story_tabs($option) {
	global $globals, $db, $link;

	$active = array();
	$active[$option] = ' class="selected"';

	echo '<ul class="subheader">'."\n";
	echo '<li'.$active[1].'><a href="'.$globals['link_permalink'].'">'._('comentarios'). '</a></li>'."\n";
	echo '<li'.$active[2].'><a href="'.$globals['link_permalink'].'/best-comments">'._('+ valorados'). '</a></li>'."\n";
	echo '<li'.$active[9].'><a href="'.$globals['link_permalink'].'/answered">'._('+ respondidas'). '</a></li>'."\n";
	if (!$globals['bot']) { // Don't show "empty" pages to bots, Google can penalize too
		if ($globals['link']->sent_date > $globals['now'] - 86400*60) { // newer than 60 days
			echo '<li'.$active[3].'><a href="'.$globals['link_permalink'].'/voters">'._('votos'). '</a></li>'."\n";
		}
		if ($globals['link']->sent_date > $globals['now'] - 86400*30) { // newer than 30 days
			echo '<li'.$active[4].'><a href="'.$globals['link_permalink'].'/log">'._('registros'). '</a></li>'."\n";
		}
		if ($globals['link']->date > $globals['now'] - $globals['time_enabled_comments']) {
			echo '<li'.$active[5].'><a href="'.$globals['link_permalink'].'/sneak">&micro;&nbsp;'._('fisgona'). '</a></li>'."\n";
		}

	}
	if (($c = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_type = 'link' and favorite_link_id=$link->id")) > 0) {
		echo '<li'.$active[6].'><a href="'.$globals['link_permalink'].'/favorites">'._('favoritos')."&nbsp;($c)</a></li>\n";
	}
	if (($c = $db->get_var("SELECT count(*) FROM trackbacks WHERE trackback_link_id=$link->id AND trackback_type='in' and trackback_status = 'ok'")) > 0) {
		echo '<li'.$active[7].'><a href="'.$globals['link_permalink'].'/trackbacks">'._('trackbacks'). "&nbsp;($c)</a></li>\n";
	}
	echo '<li'.$active[8].'><a href="'.$globals['link_permalink'].'/related">'._('relacionadas'). '</a></li>';
	echo '</ul>'."\n";
}

function do_comment_pages($total, $current, $reverse = true) {
	global $db, $globals;

	if ( ! $globals['comments_page_size'] || $total <= $globals['comments_page_size']*$globals['comments_page_threshold']) return;

	if ( ! empty($globals['base_story_url'])) {
		$query = $globals['link_permalink'];
	} else {
		$query=preg_replace('/\/[0-9]+(#.*)*$/', '', $_SERVER['QUERY_STRING']);
		if(!empty($query)) {
			$query = htmlspecialchars($query);
			$query = "?$query";
		}
	}

	$total_pages=ceil($total/$globals['comments_page_size']);
	if (! $current) {
		if ($reverse) $current = $total_pages;
		else $current = 1;
	}

	echo '<div class="pages">';

	if($current==1) {
		echo '<span class="nextprev">&#171; '._('anterior'). '</span>';
	} else {
		$i = $current-1;
		echo '<a href="'.get_comment_page_url($i, $total_pages, $query).'">&#171; '._('anterior').'</a>';
	}



	$dots_before = $dots_after = false;
	for ($i=1;$i<=$total_pages;$i++) {
		if($i==$current) {
			echo '<span class="current">'.$i.'</span>';
		} else {
			if ($total_pages < 7 || abs($i-$current) < 3 || $i < 3 || abs($i-$total_pages) < 2) {
				echo '<a href="'.get_comment_page_url($i, $total_pages, $query).'" title="'._('ir a página')." $i".'">'.$i.'</a>';
			} else {
				if ($i<$current && !$dots_before) {
					$dots_before = true;
					echo '<span>...</span>';
				} elseif ($i>$current && !$dots_after) {
					$dots_after = true;
					echo '<span>...</span>';
				}
			}
		}
	}

	if($current<$total_pages) {
		$i = $current+1;
		echo '<a href="'.get_comment_page_url($i, $total_pages, $query).'">'._('siguiente').' &#187;</a>';
	} else {
		echo '<span class="nextprev">'._('siguiente'). ' &#187;</span>';
	}
	echo "</div>\n";

}

function get_comment_page_url($i, $total, $query) {
	global $globals;
	if ($i == $total) return $query;
	else return $query.'/'.$i;
}

function print_relevant_comments($link) {
	global $globals, $db;

	if ($globals['bot'] || $link->comments < 25 ) return;
	if ($link->comments > 30 && $globals['now'] - $link->date < 86400*4) $do_cache = true;
	else $do_cache = false;

	if($do_cache) {
		$key = 'relevant_story_comments_'.$globals['css_main'].$link->id;
		if(memcache_mprint($key)) return;
	}


	$min_karma = intval($globals['comment_highlight_karma']/2);
	$limit = min(15, intval($link->comments/10));
	$min_len = 32;

	$res = $db->get_results("select comment_id, comment_order, comment_karma + comment_order * 0.7 as val, user_id, user_avatar from comments, users where comment_link_id = $link->id and comment_karma > $min_karma and length(comment_content) > $min_len and comment_user_id = user_id order by val desc limit $limit");
	if ($res) {
		$objects = array();
		$link_url = $link->get_relative_permalink();
		foreach ($res as $comment) {
			$obj = new stdClass();
			$obj->order = $comment->comment_order;
			$obj->link_id = $link->id;
			$obj->link = $link_url.'/000'.$comment->comment_order;
			$obj->user_id = $comment->user_id;
			$obj->avatar = $comment->user_avatar;
			$objects[] = $obj;
		}
		$output = Haanga::Load('relevant_comments.html', compact('objects', 'link_url'), true);
		echo $output;
		if($do_cache) {
			memcache_madd($key, $output, 300);
		}
	}
}
?>
