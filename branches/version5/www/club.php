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

	// no ads in club page
	$globals['ads'] = false;

// Check for a page number which has to come to the end, i.e. ?id=xxx/P or /story/uri/P
$last_arg = count($url_args)-1;
if ($last_arg > 0) {
	// Dirty trick to redirect to a comment' page
	if (preg_match('/^000/', $url_args[$last_arg])) {
		header ('HTTP/1.1 301 Moved Permanently');
		if ($url_args[$last_arg] > 0) {
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

// Set globals
$globals['link'] = $link;
$globals['link_id'] = $link->id;
$globals['link_permalink'] = $globals['link']->get_permalink();

// to avoid search engines penalisation
if ($tab_option != 1 || $link->status == 'discard') {
	$globals['noindex'] = true;
}

do_modified_headers($link->modified, $current_user->user_id.'-'.$globals['link_id'].'-'.$link->comments.'-'.$link->modified);

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

do_tabs("main",_('club'), true);

echo '<div id="newswrap">'."\n";
$link->print_summary_club();

switch ($tab_option) {
case 1:
	echo '<div class="comments">';

	if($tab_option == 1) do_comment_pages($link->comments, $current_page);

	$comments = $db->object_iterator("SELECT".Comment::SQL."WHERE comment_link_id=$link->id ORDER BY $order_field $limit", "Comment");
	if ($comments) {
		echo '<ol class="comments-list">';
		foreach($comments as $comment) {
			echo '<li>';
 			$comment->print_summary_club($link, 2500, true);
			echo '</li>';
			echo "\n";
		}
		echo "</ol>\n";
	}

	if($tab_option == 1) do_comment_pages($link->comments, $current_page);
	Comment::print_form($link);
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
?>
