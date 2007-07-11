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

$globals['ads'] = true;
$link = new Link;

if (!defined($_REQUEST['id']) && !empty($_SERVER['PATH_INFO'])) {
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
	array_shift($url_args); // The first element is always a "/"
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
			if (!empty($url_args[1])) $extra_url = '/' . $url_args[1];
			header('Location: ' . $link->get_permalink(). $extra_url);
			die;
		}
	} else {
		not_found();
	}
}

switch ($url_args[1]) {
	case '':
		$tab_option = 1;	
		$order_field = 'comment_order';

		// Geo check
		if($globals['google_maps_api']) {
			$link->geo = true;
			$link->latlng = $link->get_latlng();
			if ($link->latlng) {
				geo_init('geo_coder_load', $link->latlng, 5);
			} else {
				geo_init(null, null);
			}
		}
		break;
	case 'best-comments':
		$tab_option = 2;
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

// Set globals
$globals['link']=$link;
$globals['link_id']=$link->id;
$globals['category_id']=$link->category;
$globals['category_name']=$link->category_name;
$globals['link_permalink'] = $globals['link']->get_permalink();

// to avoid penalisation
if ($tab_option != 1 || $link->status == 'discard') {
	$globals['noindex'] = true;
}

if ($_POST['process']=='newcomment') {
	insert_comment();
}

do_modified_headers($link->modified, $current_user->user_id.'-'.$globals['link_id'].'-'.$link->comments.'-'.$link->modified);

// Enable user AdSense
// do_user_ad: 0 = noad, > 0: probability n/100
if ($link->status == 'published' && $link->user_karma > 7 && !empty($link->user_adcode)) {
	$globals['do_user_ad'] = $link->user_karma * 2;
	$globals['user_adcode'] = $link->user_adcode;
	$globals['user_adchannel'] = $user->adchannel;
}

if ($link->status != 'published') 
	$globals['do_vote_queue']=true;
if (!empty($link->tags))
	$globals['tags']=$link->tags;

do_header($link->title, 'post');

do_banner_top();
echo '<div id="container">'."\n";
do_sidebar(false);
echo '<div id="contents">';
do_tabs("main",_('noticia'), true);
$link->print_summary();

switch ($tab_option) {
case 1:
case 2:
	echo '<div class="comments">';

	// AdSense
	do_banner_story();

	// Print tabs
	print_story_tabs($tab_option);


	// If iotion is "normal comments", show also last trackbakcs and pingbacks
	if ($tab_option == 1) {
		$trackbacks = $db->get_col("SELECT trackback_id FROM trackbacks WHERE trackback_link_id=$link->id AND trackback_type='in' and trackback_status = 'ok' ORDER BY trackback_date DESC limit 10");
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

	$comments = $db->get_col("SELECT comment_id FROM comments WHERE comment_link_id=$link->id ORDER BY $order_field");
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

	
	if($link->date < $globals['now']-$globals['time_enabled_comments']) { 
		echo '<div class="commentform warn">'."\n";
		echo _('comentarios cerrados')."\n";
		echo '</div>'."\n";
	} elseif ($current_user->authenticated && ($current_user->user_karma > $globals['min_karma_for_comments'] || $current_user->user_id == $link->author)) {
		print_comment_form();
	} else {
		echo '<br/>'."\n";
		echo '<div class="commentform warn">'."\n";
		if ($current_user->authenticated && $current_user->user_karma <= $globals['min_karma_for_comments']) 
			echo _('No tienes el mínimo karma requerido')." (" . $globals['min_karma_for_comments'] . ") ". _('para comentar'). ": ".$current_user->user_karma ."\n";

		else
			echo '<a href="'.$globals['base_url'].'login.php?return='.$_SERVER['REQUEST_URI'].'">'._('Autentifícate si deseas escribir').'</a> '._('comentarios').'. '._('O regístrate'). ' <a href="'.$globals['base_url'].'register.php">aquí</a>.'."\n";
		echo '</div>'."\n";
	}

	echo '</div>' . "\n";
	break;

case 3:
	// Show voters
	echo '<div class="voters" id="voters">';

	// AdSense
	do_banner_story();

	print_story_tabs($tab_option);
	echo '<fieldset>';
	echo '<div id="voters-container">';
	if ($globals['link']->date < $globals['now'] - 7776000) { // older than 90 days
		echo _('Noticia antigua, datos de votos archivados');
	} else {
		include(mnmpath.'/backend/meneos.php');
	}
	echo '</div><br />';
	echo '</fieldset>';
	echo '</div>';
	break;

case 6:
	// Show favorited by
	echo '<div class="voters" id="voters">';

	// AdSense
	do_banner_story();

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

	// AdSense
	do_banner_story();

	print_story_tabs($tab_option);
	echo '<fieldset><legend>'._('registro de eventos de la noticia').'</legend>';

	echo '<div id="voters-container">';
	$logs = $db->get_results("select logs.*, user_id, user_login, user_avatar from logs, users where log_type in ('link_new', 'link_publish', 'link_discard', 'link_edit', 'link_geo_edit') and log_ref_id=$link->id and user_id= log_user_id order by log_date asc");
	if ($logs) {
		//echo '<div class="voters-list">';
		foreach ($logs as $log) {
			echo '<div style="width:100%; display: block; clear: both; border-bottom: 1px solid #FFE2C5;">';
			echo '<div style="width:30%; float: left;padding: 4px 0 4px 0;">'.$log->log_date.'</div>';
			echo '<div style="width:24%; float: left;padding: 4px 0 4px 0;"><strong>'.$log->log_type.'</strong></div>';
			echo '<div style="width:45%; float: left;padding: 4px 0 4px 0;">';
			echo '<a href="'.get_user_uri($log->user_login).'" title="'.$log->date.'">';
			echo '<img src="'.get_avatar_url($log->log_user_id, $log->user_avatar, 20).'" width="20" height="20" alt="'.$log->user_login.'"/>&nbsp;';
			echo $log->user_login;
			echo '</a>';
			echo '</div>';
			echo '</div>';

		}
		//echo '</div>';
	} else {
		echo _('no hay registros');
	}
	echo '</div><br />';
	echo '</fieldset>';
	echo '</div>';
	break;
case 5:
	// Micro sneaker
	echo '<div class="mini-sneaker">';
	// AdSense
	do_banner_story();

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
	// AdSense
	do_banner_story();
	print_story_tabs($tab_option);
	echo '<a href="'.$link->get_trackback().'" title="'._('URI para trackbacks').'" class="tab-trackback-url"><img src="'.$globals['base_url'].'img/common/permalink.gif" alt="'._('enlace trackback').'" width="16" height="9"/> '._('dirección de trackback').'</a>' . "\n";

	echo '<fieldset><legend>'._('lugares que enlazan esta noticia').'</legend>';
	echo '<ul class="tab-trackback">';

	$trackbacks = $db->get_col("SELECT trackback_id FROM trackbacks WHERE trackback_link_id=$link->id AND trackback_type='in' and trackback_status = 'ok' ORDER BY trackback_date DESC limit 50");
	if ($trackbacks) {
		require_once(mnminclude.'trackback.php');
		$trackback = new Trackback;
		foreach($trackbacks as $trackback_id) {
			$trackback->id=$trackback_id;
			$trackback->read();
			echo '<li class="tab-trackback-entry"><a href="'.$trackback->url.'" rel="nofollow">'.$trackback->title.'</a></li>' . "\n";
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
// echo '<div class="story-vertical-completion">&nbsp</div>';
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
	echo '<label for="comment">'. _('escribe el texto del comentario / no se admiten etiquetas HTML').'<br /><span class="comments-warning">'._('comentarios xenófobos, racistas o difamatorios causarán la anulación de la cuenta').'</span></label>'."\n";
	echo '<div><textarea name="comment_content" id="comment" cols="75" rows="8"></textarea></div>'."\n";
	echo '<input class="submit" type="submit" name="submit" value="'._('enviar el comentario').'" />'."\n";
	echo '<input type="hidden" name="process" value="newcomment" />'."\n";
	echo '<input type="hidden" name="randkey" value="'.rand(1000000,100000000).'" />'."\n";
	echo '<input type="hidden" name="link_id" value="'.$link->id.'" />'."\n";
	echo '<input type="hidden" name="user_id" value="'.$current_user->user_id.'" />'."\n";
	echo '</fieldset>'."\n";
	echo '</form>'."\n";
	echo "</div>\n";

}

function insert_comment () {
	global $link, $db, $current_user, $globals;
	// Check if is a POST of a comment
	if($link->votes > 0 && $link->date > $globals['now']-$globals['time_enabled_comments'] && 
			intval($_POST['link_id']) == $link->id && $current_user->authenticated && 
			intval($_POST['user_id']) == $current_user->user_id &&
			($current_user->user_karma > $globals['min_karma_for_comments'] || $current_user->user_id == $link->author) &&
			intval($_POST['randkey']) > 0 && 
			mb_strlen(trim($_POST['comment_content'])) > 2 ) {

		require_once(mnminclude.'comment.php');
		$comment = new Comment;
		$comment->link=$link->id;
		$comment->randkey=intval($_POST['randkey']);
		$comment->author=intval($_POST['user_id']);
		$comment->karma=intval($current_user->user_karma);
		$comment->content=clean_text($_POST['comment_content'], 0, false, 10000);
		if (mb_strlen($comment->content) > 0 && preg_match('/[a-zA-Z:-]/', $_POST['comment_content'])) { // Check there are at least a valid char
			$already_stored = intval($db->get_var("select count(*) from comments where comment_link_id = $comment->link and comment_user_id = $comment->author and comment_randkey = $comment->randkey"));
			// Check the comment wasn't already stored
			if (!$already_stored) {
				// Lower karma to comments' spammers
				$comment_count = (int) $db->get_var("select count(*) from comments where comment_user_id = $current_user->user_id and comment_date > date_sub(now(), interval 3 minute)");
				// Check the text is not the same
				$same_count = $comment->same_text_count() + $comment->same_links_count();
				if ($comment_count > 3 || $same_count > 2) {
					require_once(mnminclude.'user.php');
					$reduction = 0;
					if ($comment_count > 3) {
						$reduction += ($comment_count-3) * 0.1;
					}
					if($same_count > 1) {
						$reduction += $same_count * 0.25;
					}
					$user = new User;
					$user->id = $current_user->user_id;
					$user->read();
					$user->karma = $user->karma - $reduction;
					syslog(LOG_NOTICE, "Meneame: story decreasing $reduction of karma to $current_user->user_login (now $user->karma)");
					$user->store();
				}
				$comment->store();
				$comment->insert_vote();
				$link->update_comments();
				// Re read link data
				$link->read();
			}
		}
		// We don't redirect, Firefox show cache data instead of the new data since we send lastmodification time.
		//header('Location: '.$link->get_permalink());
		//die;
	}
}

function print_story_tabs($option) {
	global $globals;

	$active = array();
	$active[$option] = 'class="tabsub-this"';

	echo '<ul class="tabsub">'."\n";
	echo '<li><a '.$active[1].' href="'.$globals['link_permalink'].'">'._('comentarios'). '</a></li>'."\n";
	echo '<li><a '.$active[2].' href="'.$globals['link_permalink'].'/best-comments">'._('+ valorados'). '</a></li>'."\n";
	echo '<li><a '.$active[7].' href="'.$globals['link_permalink'].'/trackbacks">'._('trackbacks'). '</a></li>'."\n";
	if (!$globals['bot']) { // Don't show "empty" pages to bots, Google can penalize too
		if ($globals['link']->date > $globals['now'] - 7776000) { // newer than 90 days
			echo '<li><a '.$active[3].' href="'.$globals['link_permalink'].'/voters">'._('votos'). '</a></li>'."\n";
		}
		echo '<li><a '.$active[6].' href="'.$globals['link_permalink'].'/favorites">&nbsp;'.FAV_YES.'&nbsp;</a></li>'."\n";
		if ($globals['link']->date > $globals['now'] - $globals['time_enabled_comments']) {
			echo '<li><a '.$active[5].' href="'.$globals['link_permalink'].'/sneak">&micro;&nbsp;'._('fisgona'). '</a></li>'."\n";
			echo '<li><a '.$active[4].' href="'.$globals['link_permalink'].'/log">'._('log'). '</a></li>'."\n";
		}
	}
	echo '</ul>'."\n";
}

?>
