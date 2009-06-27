<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'link.php');
include(mnminclude.'html1.php');

$link = new Link;


if (!isset($_REQUEST['id']) && !empty($_SERVER['PATH_INFO'])) {
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
	array_shift($url_args); // The first element is always a "/"

	// If the first argument are only numbers, redirect to the story with that id
	if (preg_match('/^0\d+$/', $url_args[0])) {
			$link->id = intval($url_args[0]);
			if ($link->read('id')) {
				header('Location: ' . $link->get_permalink());
				die;
			}
	}

	$link->uri = $db->escape($url_args[0]);
	if (! $link->read('uri') ) {
		not_found();
	}
} else {
	$url_args = preg_split('/\/+/', $_REQUEST['id']);
	$link->id=intval($url_args[0]);
	if(is_numeric($url_args[0]) && $link->read('id') ) {
		// Redirect to the right URL if the link has a "semantic" uri
		if (!empty($link->uri) && !empty($globals['base_story_url'])) {
			if (!empty($url_args[1])) $extra_url = '/' . urlencode($url_args[1]);
			header('Location: ' . $link->get_permalink(). $extra_url);
			die;
		}
	} else {
		not_found();
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

switch ($url_args[1]) {
	case '':
		$tab_option = 1;	
		$order_field = 'comment_order';

		// Geo check
		// Don't show it if it's a mobile browser
		if(!$globals['mobile'] && $globals['google_maps_api']) {
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
	default:
		not_found();
}

// Check for comment post
if ($_POST['process']=='newcomment') {
	require_once(mnminclude.'comment.php');
	$comment = new Comment;
	$new_comment_error = $comment->save_from_post($link);
}

// Set globals
$globals['link'] = $link;
$globals['link_id'] = $link->id;
$globals['link_permalink'] = $globals['link']->get_permalink();
// Change to a min_value is times is changed for the current link_status
if ($globals['time_enabled_comments_status'][$link->status]) {
	$globals['time_enabled_comments'] = min($globals['time_enabled_comments_status'][$link->status], 
											$globals['time_enabled_comments']);
}

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

// add also a rel to the comments rss
$globals['extra_head'] = '<link rel="alternate" type="application/rss+xml" title="'._('comentarios esta noticia').'" href="http://'.get_server_name().$globals['base_url'].'comments_rss2.php?id='.$link->id.'" />'."\n";

if ($link->has_thumb()) {
	$globals['thumbnail'] = $link->thumb;
}

do_header($link->title, 'post');

// Show the error if the comment couldn't be inserted
if (!empty($new_comment_error)) {
	echo '<script type="text/javascript">';
	echo '$(function(){alert(\''._('Aviso'). ": $new_comment_error".'\')});';
	echo '</script>';
}

do_tabs("main",_('noticia'), true);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
// GEO
if ($link->latlng) {
	echo '<div id="map" style="width:300px;height:200px;margin-bottom:25px;">&nbsp;</div>'."\n";
}
if ($link->comments > 15) {
	do_best_story_comments($link);
}
if (! $current_user->user_id) {
	do_best_stories();
}
do_rss_box();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";
$link->print_summary();

switch ($tab_option) {
case 1:
case 2:
	echo '<div class="comments">';

	// Print tabs
	print_story_tabs($tab_option);


	// If option is "normal comments", show also last trackbakcs and pingbacks
	// TB are shown only in the last page
	if ($tab_option == 1 && ! $requested_page) {
		$trackbacks = $db->get_col("SELECT SQL_CACHE trackback_id FROM trackbacks WHERE trackback_link_id=$link->id AND trackback_type='in' and trackback_status = 'ok' ORDER BY trackback_date DESC limit 10");
		if ($trackbacks) {
			echo '<fieldset><legend><a href="'.$globals['link_permalink'].'/trackbacks">'._('últimas relacionadas').'</a></legend>';
			echo '<ul class="tab-trackback">';
			require_once(mnminclude.'trackback.php');
			$trackback = new Trackback;
			foreach($trackbacks as $trackback_id) {
				$trackback->id=$trackback_id;
				$trackback->read();
				echo '<li class="tab-trackback-entry"><a href="'.$trackback->url.'" rel="nofollow">'.$trackback->title.'</a> ['.preg_replace('/https*:\/\/([^\/]+).*/', "$1", $trackback->url).']</li>' . "\n";
			}
			echo '</ul>';
			echo '</fieldset>';
		}
	}

	if($tab_option == 1) do_comment_pages($link->comments, $current_page);

	$comments = $db->get_col("SELECT SQL_CACHE comment_id FROM comments WHERE comment_link_id=$link->id ORDER BY $order_field $limit");
	if ($comments) {
		echo '<ol class="comments-list">';
		require_once(mnminclude.'comment.php');
		$comment = new Comment;
		foreach($comments as $comment_id) {
			$comment->id=$comment_id;
			$comment->read();
			$comment->print_summary($link, 700, true);
			echo "\n";
		}
		echo "</ol>\n";
	}

	if($link->date < $globals['now']-$globals['time_enabled_comments'] || $link->comments >= $globals['max_comments']) {
		// Comments already closed
		if($tab_option == 1) do_comment_pages($link->comments, $current_page);
		echo '<div class="commentform warn">'."\n";
		echo _('comentarios cerrados')."\n";
		echo '</div>'."\n";
	} elseif ($current_user->authenticated 
				&& (($current_user->user_karma > $globals['min_karma_for_comments'] 
						&& $current_user->user_date < $globals['now'] - $globals['min_time_for_comments']) 
					|| $current_user->user_id == $link->author)) {
		// User can comment
		print_comment_form();
		if($tab_option == 1) do_comment_pages($link->comments, $current_page);
	} else {
		// Not enough karma or anonymous user
		if($tab_option == 1) do_comment_pages($link->comments, $current_page);
		if ($current_user->authenticated) {
			if ($current_user->user_date >= $globals['now'] - $globals['min_time_for_comments']) {
				$remaining = txt_time_diff($globals['now'], $current_user->user_date+$globals['min_time_for_comments']);
				$msg = _('Debes esperar') . " $remaining " . _('para escribir el primer comentario');
			}
			if ($current_user->user_karma <= $globals['min_karma_for_comments']) {
				$msg = _('No tienes el mínimo karma requerido')." (" . $globals['min_karma_for_comments'] . ") ". _('para comentar'). ": ".$current_user->user_karma;
			}
			echo '<div class="commentform warn">'."\n";
			echo $msg . "\n";
			echo '</div>'."\n";
		} elseif (!$globals['bot']){
			echo '<div class="commentform warn">'."\n";
			echo '<a href="'.$globals['base_url'].'login.php?return='.$_SERVER['REQUEST_URI'].'">'._('Autentifícate si deseas escribir').'</a> '._('comentarios').'. '._('O crea tu cuenta'). ' <a href="'.$globals['base_url'].'register.php">aquí</a>'."\n";
			echo '</div>'."\n";
		}
	}
	echo '</div>' . "\n";

	// Highlight a comment if it is referenced by the URL.
	// currently double border, width must be 3 at least
	echo '<script type="text/javascript">';
	echo 'if(location.href.match(/#(c-\d+)$/)){$("#"+RegExp.$1+">:first").css("border-style","double").css("border-width","3px")}';
	echo "</script>\n";
	break;

case 3:
	// Show voters
	echo '<div class="voters" id="voters">';

	print_story_tabs($tab_option);
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

	print_story_tabs($tab_option);

	echo '<fieldset>';
	echo '<div id="voters-container">';
	include(mnmpath.'/backend/get_link_favorites.php');
	echo '</div><br />';
	echo '</fieldset>';
	echo '</div>';
	break;

case 4:
	// Show logs
	echo '<div class="voters" id="voters">';

	print_story_tabs($tab_option);
	echo '<fieldset><legend>'._('registro de eventos de la noticia').'</legend>';

	echo '<div id="voters-container">';
	$logs = $db->get_results("select logs.*, UNIX_TIMESTAMP(logs.log_date) as ts, user_id, user_login, user_avatar from logs, users where log_type in ('link_new', 'link_publish', 'link_discard', 'link_edit', 'link_geo_edit', 'link_depublished') and log_ref_id=$link->id and user_id= log_user_id order by log_date desc");
	if ($logs) {
		foreach ($logs as $log) {
			echo '<div style="width:100%; display: block; clear: both; border-bottom: 1px solid #FFE2C5;">';
			echo '<div style="width:30%; float: left;padding: 4px 0 4px 0;">'.get_date_time($log->ts).'</div>';
			echo '<div style="width:24%; float: left;padding: 4px 0 4px 0;"><strong>'.$log->log_type.'</strong></div>';
			echo '<div style="width:45%; float: left;padding: 4px 0 4px 0;">';
			if ($log->log_type == 'link_discard' &&  $link->author != $log->user_id) { // It was discarded by an admin
				echo '<img src="'.get_no_avatar_url(20).'" width="20" height="20" alt="'.$log->user_login.'"/>&nbsp;';
				echo ('admin');
				if ($current_user->user_level == 'god' || $current_user->user_level == 'admin') {
					echo '&nbsp;('.$log->user_login.')';
				}
			} else {
				echo '<a href="'.get_user_uri($log->user_login).'" title="'.$log->date.'">';
				echo '<img src="'.get_avatar_url($log->log_user_id, $log->user_avatar, 20).'" width="20" height="20" alt="'.$log->user_login.'"/>&nbsp;';
				echo $log->user_login;
				echo '</a>';
			}
			echo '</div>';
			echo '</div>';

		}
	} else {
		echo _('no hay registros');
	}
	echo '</div>';
	echo '</fieldset>';
	echo '</div>';


	// Show karma logs from annotations
	if ( ($array = $link->read_annotation("link-karma")) != false ) {
		echo '<div class="voters">';
		echo '<fieldset><legend>'._('registro de cálculos de karma').'</legend>';
		echo "<table><tr class='thead'><th>hora</th><th>pos, anon, neg</th><th>coef</th><th>karma</th><th>notas</th></tr>\n";
		foreach ($array as $log) {
			echo "<tr><td>".get_date_time($log['time'])."</td><td>".$log['positives'].', '.$log['anonymous'].', '.$log['negatives']."</td><td>".$log['coef']."</td><td>";
			if ($log['old_karma'] > 0)
				echo $log['old_karma']. " -&gt; ";
			echo $log['karma']."</td><td>".$log['annotation']."</td></tr>\n";

		}
		echo "</table>\n";
		echo '</fieldset>';
		echo '</div>';
	}


	break;
case 5:
	// Micro sneaker
	echo '<div class="mini-sneaker">';

	print_story_tabs($tab_option);
	echo '<fieldset>';
	include(mnmpath.'/libs/link_sneak.php');
	echo '</fieldset>';
	echo '</div>';
	echo '<script type="text/javascript">$(function(){start_link_sneak()});</script>' . "\n";
	break;
case 7:
	// Show trackback
	echo '<div class="voters" id="voters">';

	print_story_tabs($tab_option);
	echo '<a href="'.$link->get_trackback().'" title="'._('URI para trackbacks').'" class="tab-trackback-url"><img src="'.$globals['base_static'].'img/common/permalink.gif" alt="'._('enlace trackback').'" width="16" height="9"/> '._('dirección de trackback').'</a>' . "\n";

	echo '<fieldset><legend>'._('lugares que enlazan esta noticia').'</legend>';
	echo '<ul class="tab-trackback">';

	$trackbacks = $db->get_col("SELECT SQL_CACHE trackback_id FROM trackbacks WHERE trackback_link_id=$link->id AND trackback_type='in' and trackback_status = 'ok' ORDER BY trackback_date DESC limit 50");
	if ($trackbacks) {
		require_once(mnminclude.'trackback.php');
		$trackback = new Trackback;
		foreach($trackbacks as $trackback_id) {
			$trackback->id=$trackback_id;
			$trackback->read();
			echo '<li class="tab-trackback-entry"><a href="'.$trackback->url.'" rel="nofollow">'.$trackback->title.'</a> ['.preg_replace('/https*:\/\/([^\/]+).*/', "$1", $trackback->url).']</li>' . "\n";
		}
	}
	echo '<li class="tab-trackback-technorati"><a href="http://technorati.com/search/'.urlencode($globals['link_permalink']).'">'._('Technorati').'</a></li>' . "\n";
	echo '<li class="tab-trackback-google"><a href="http://blogsearch.google.com/blogsearch?hl=es&amp;q=link%3A'.urlencode($globals['link_permalink']).'">'._('Google').'</a></li>' . "\n";
	echo '<li class="tab-trackback-askcom"><a href="http://es.ask.com/blogsearch?q='.urlencode($globals['link_permalink']).'&amp;t=a&amp;search=Buscar&amp;qsrc=2101&amp;bql=any">'._('Ask.com').'</a></li>' . "\n";

	echo '</ul>';
	echo '</fieldset>';
	echo '</div>';
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

function print_comment_form() {
	global $link, $current_user, $globals;

	if (!$link->votes > 0) return;
	echo '<div class="commentform">'."\n";
	echo '<form action="" method="post">'."\n";
	echo '<fieldset>'."\n";
	echo '<legend>'._('envía un comentario').'</legend>'."\n";
	print_simpleformat_buttons('comment');
	echo '<label for="comment">'. _('texto del comentario / no se admiten etiquetas HTML').'<br /><span class="note">'._('comentarios xenófobos, racistas o difamatorios causarán la anulación de la cuenta').'</span></label>'."\n";
	echo '<div><textarea name="comment_content" id="comment" cols="75" rows="12"></textarea></div>'."\n";
	echo '<input class="button" type="submit" name="submit" value="'._('enviar el comentario').'" />'."\n";
	// Allow gods to put "admin" comments which does not allow votes
	if ($current_user->user_level == 'god') {
		echo '&nbsp;&nbsp;&nbsp;&nbsp;<label><strong>'._('admin').' </strong><input name="type" type="checkbox" value="admin"/></label>'."\n";
	}
	echo '<input type="hidden" name="process" value="newcomment" />'."\n";
	echo '<input type="hidden" name="randkey" value="'.rand(1000000,100000000).'" />'."\n";
	echo '<input type="hidden" name="link_id" value="'.$link->id.'" />'."\n";
	echo '<input type="hidden" name="user_id" value="'.$current_user->user_id.'" />'."\n";
	echo '</fieldset>'."\n";
	echo '</form>'."\n";
	echo "</div>\n";

}


function print_story_tabs($option) {
	global $globals;

	$active = array();
	$active[$option] = ' class="tabsub-this"';

	echo '<ul class="tabsub">'."\n";
	echo '<li'.$active[1].'><a href="'.$globals['link_permalink'].'">'._('comentarios'). '</a></li>'."\n";
	echo '<li'.$active[2].'><a href="'.$globals['link_permalink'].'/best-comments">'._('+ valorados'). '</a></li>'."\n";
	echo '<li'.$active[7].'><a href="'.$globals['link_permalink'].'/trackbacks">'._('trackbacks'). '</a></li>'."\n";
	if (!$globals['bot']) { // Don't show "empty" pages to bots, Google can penalize too
		if ($globals['link']->sent_date > $globals['now'] - 86400*60) { // newer than 60 days
			echo '<li'.$active[3].'><a href="'.$globals['link_permalink'].'/voters">'._('votos'). '</a></li>'."\n";
		}
		echo '<li'.$active[6].'><a href="'.$globals['link_permalink'].'/favorites">&nbsp;'.FAV_YES.'&nbsp;</a></li>'."\n";
		if ($globals['link']->date > $globals['now'] - $globals['time_enabled_comments']) {
			echo '<li'.$active[5].'><a href="'.$globals['link_permalink'].'/sneak">&micro;&nbsp;'._('fisgona'). '</a></li>'."\n";
		}
		if ($globals['link']->sent_date > $globals['now'] - 86400*30) { // newer than 30 days
			echo '<li'.$active[4].'><a href="'.$globals['link_permalink'].'/log">'._('log'). '</a></li>'."\n";
		}
	}
	echo '</ul>'."\n";
}

function do_comment_pages($total, $current, $reverse = true) {
	global $db, $globals;

	if ( ! $globals['comments_page_size'] || $total <= $globals['comments_page_size']*$globals['comments_page_threshold']) return;
	
	if ($globals['base_story_url'] = 'story/') {
		$query = $globals['link_permalink'];
	} else {
		$query=preg_replace('/\/[0-9]+(#.*)$/', '', $_SERVER['QUERY_STRING']);
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
		echo '<a href="'.get_comment_page_url($i, $total_pages, $query).'">&#187; '._('siguiente').'</a>';
	} else {
		echo '<span class="nextprev">&#187; '._('siguiente'). '</span>';
	}
	echo "</div>\n";

}

function get_comment_page_url($i, $total, $query) {
	global $globals;
	if ($i == $total) return $query;
	else return $query.'/'.$i;
}
?>
