<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//				http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
include_once('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'geo.php');
include(mnminclude.'favorites.php');

$page_size = $globals['page_size'];

if ($globals['bot'] && get_current_page() > 2) {
	do_error('Pages exceeded', 404);
}

$offset=(get_current_page()-1)*$page_size;

if (!empty($_SERVER['PATH_INFO'])) {
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO'], 6, PREG_SPLIT_NO_EMPTY);
	array_shift($url_args);
	$_REQUEST['login'] = clean_input_string($url_args[0]);
	$_REQUEST['view'] = $url_args[1];
	$_REQUEST['uid'] = intval($url_args[2]);
	if (! $_REQUEST['uid'] && is_numeric($_REQUEST['view'])) {
		// This is a empty view but an user_id, change it
		$_REQUEST['uid'] = intval($_REQUEST['view']);
		$_REQUEST['view'] = '';
	}
} else {
	$_REQUEST['login'] = clean_input_string($_REQUEST['login']);
	$_REQUEST['uid'] = intval($_REQUEST['uid']);
	if (!empty($_REQUEST['login'])) {
		header('Location: ' . html_entity_decode(get_user_uri($_REQUEST['login'], clean_input_string($_REQUEST['view']))));
		die;
	}
}

$login = clean_input_string($_REQUEST['login']);
if(empty($login)){
	if ($current_user->user_id > 0) {
		header('Location: ' . html_entity_decode(get_user_uri($current_user->user_login)));
		die;
	} else {
		header('Location: '.$globals['base_url']);
		die;
	}
}


$uid = $_REQUEST['uid']; // Should be clean before

$user=new User();

if ($current_user->admin) {
		// Check if it's used UID
		if($uid) {
			$user->id = $uid;
		} else {
			redirect(html_entity_decode(get_user_uri_by_uid($login, $_REQUEST['view'])));
			die;
		}
} else {
		if($uid > 0) {
			// Avoid anonymous and non admins users to use the id, it's a "duplicated" page
			redirect(html_entity_decode(get_user_uri($login, $_REQUEST['view'])));
			die;
		}
		$user->username = $login;
}

if(!$user->read()) {
	do_error(_('usuario inexistente'), 404);
}
$login = $user->username; // Just in case, we user the database username

$globals['search_options'] = array('u' => $user->username);

$view = clean_input_string($_REQUEST['view']);
if(empty($view)) $view = 'profile';


// The profile's use marked the current one as friend
if ($current_user->user_id) {
	$user->friendship = User::friend_exists($user->id, $current_user->user_id);
} else {
	$user->friendship = 0;
}

// For editing notes and sending privates
if ($current_user->user_id == $user->id || $current_user->admin || $user->friendship) {
	$globals['extra_js'][] = 'jquery.form.min.js';
	$globals['extra_js'][] = 'ajaxupload.min.js';
}

// Enable user AdSense
// do_user_ad: 0 = noad, > 0: probability n/100
// 100 if the user is the current one
if($globals['external_user_ads'] && !empty($user->adcode)) {
	$globals['user_adcode'] = $user->adcode;
	$globals['user_adchannel'] = $user->adchannel;
	if ($current_user->user_id == $user->id || $current_user->admin) $globals['do_user_ad']  = 100;
	else $globals['do_user_ad'] = $user->karma * 2;
}

// Load Google GEO
if (! $user->disabled()
		&& $view == 'profile'
		&& $globals['google_maps_api']
		&& (($globals['latlng']=$user->get_latlng()) || $current_user->user_id == $user->id)) {
	if ($current_user->user_id == $user->id) {
		geo_init('geo_coder_editor_load', $globals['latlng'], 7, 'user');
	} else {
		geo_init('geo_coder_load', $globals['latlng'], 7, 'user');
	}
	$globals['do_geo'] = true;
}

// Check if it should be index AND if they are valids options, otherwise call do_error()
switch ($view) {
	case 'categories':
	case 'history':
	case 'shaken':
	case 'friends_shaken':
	case 'friends':
	case 'friends_new':
	case 'friend_of':
	case 'ignored':
	case 'favorites':
		$globals['noindex'] = true;
		breaK;
	case 'commented':
	case 'conversation':
	case 'shaken_comments':
	case 'favorite_comments':
		$globals['search_options']['w'] = 'comments';
		$globals['noindex'] = true;
		breaK;
	case 'profile':
	case 'subs':
		$globals['noindex'] = false;
		breaK;
	default:
		do_error(_('opción inexistente'), 404);
		break;
}

// Add canonical address
$globals['extra_head'] = '<link rel="canonical" href="http://'.get_server_name().get_user_uri($user->username).'" />'."\n";

if (!empty($user->names)) {
	do_header("$login ($user->names)", 'profile', User::get_menu_items($view, $login));
} else {
	do_header($login, 'profile', User::get_menu_items($view, $login));
}

// Used to show the user the number of unread answers to her comments
if ($current_user->user_id == $user->id) {
	$globals['extra_comment_conversation'] = ' ['.Comment::get_unread_conversations($user->id).']';
} else {
	$globals['extra_comment_conversation'] = '';
}

echo '<div id="singlewrap">'."\n";

$url_login = urlencode($login);

switch ($view) {
	case 'subs':
		do_subs();
		break;
	case 'history':
		do_history();
		if (! $globals['bot']) do_pages($rows, $page_size);
		break;
	case 'commented':
		do_commented();
		if (! $globals['bot']) do_pages($rows, $page_size, false);
		break;
	case 'shaken':
		do_shaken();
		if (! $globals['bot']) do_pages($rows, $page_size);
		break;
	case 'friends_shaken':
		do_friends_shaken();
		if (! $globals['bot']) do_pages(-1, $page_size);
		break;
	case 'friends':
		do_friends(0);
		break;
	case 'friend_of':
		do_friends(1);
		break;
	case 'ignored':
		do_friends(2);
		break;
	case 'friends_new':
		do_friends(3);
		break;
	case 'favorites':
		do_favorites();
		if (! $globals['bot']) do_pages($rows, $page_size);
		break;
	case 'favorite_comments':
		do_favorite_comments();
		if (! $globals['bot']) do_pages($rows, $page_size);
		break;
	case 'shaken_comments':
		do_shaken_comments();
		if (! $globals['bot']) do_pages($rows, $page_size);
		break;
	case 'categories':
		do_categories();
		break;
	case 'conversation':
		do_conversation();
		if (! $globals['bot']) do_pages($rows, $page_size, false);
		break;
	case 'profile':
		do_profile();
		break;
	default:
		do_error(_('opción inexistente'), 404);
		break;
}

echo '</div>'."\n";

do_footer();


function do_profile() {
	global $user, $current_user, $login, $db, $globals;

	$options = array();
	$options[$user->username] = get_user_uri($user->username);
	$options[_('categorías personalizadas')] = get_user_uri($user->username, 'categories');
	if ($current_user->user_id == $user->id || $current_user->user_level == 'god') {
		$options[_('modificar perfil').' &rarr;'] = $globals['base_url'].'profile.php?login='.urlencode($login);
		$globals['extra_js'][] = 'jquery.flot.min.js';
		$globals['extra_js'][] = 'jquery.flot.time.min.js';
	}

	$post = new Post;
	if (!$post->read_last($user->id)) {
		$post = NULL;
	}
	if(!empty($user->url)) {
		if ($user->karma < 10) $nofollow = 'rel="nofollow"';
		else $nofollow = '';

		if (!preg_match('/^http/', $user->url)) $url = 'http://'.$user->url;
		else $url = $user->url;
	}

	if ($current_user->user_id > 0 && $current_user->user_id != $user->id) {
		$friend_icon = User::friend_teaser($current_user->user_id, $user->id);
	}

	$selected  = 0;
	$rss	   = 'rss?sent_by='.$user->id;
	$rss_title = _('envíos en rss2');
	$geodiv    = $current_user->user_id > 0 && $current_user->user_id != $user->id && $globals['latlng'] && ($my_latlng = geo_latlng('user', $current_user->user_id));
	$show_email = $current_user->user_id > 0 && !empty($user->public_info) &&
			($current_user->user_id == $user->id || $current_user->user_level=='god');

	$clones_from = "and clon_date > date_sub(now(), interval 30 day)";
	if ($current_user->admin) {
			$nclones = $db->get_var("select count(distinct clon_to) from clones where clon_from = $user->id $clones_from");
	}

	$user->all_stats();
	if (! $user->bio) {
		$user->bio = '';
	}

	if ($user->total_links > 1) {
		$entropy = intval(($user->blogs() - 1) / ($user->total_links - 1) * 100);
	}

	if ($user->total_links > 0 && $user->published_links > 0) {
		$percent = intval($user->published_links/$user->total_links*100);
	} else {
		$percent = 0;
	}

	if($globals['do_geo'] && $current_user->user_id == $user->id) {
		ob_start();
		geo_coder_print_form('user', $current_user->user_id, $globals['latlng'], _('ubícate en el mapa (si te apetece)'), 'user');
		$geo_form = ob_get_clean();
	}

	$addresses	  = array();
	if ($current_user->user_id == $user->id || ($current_user->user_level == 'god' &&  ! $user->admin) ) { // gods and admins know each other for sure, keep privacy
		$dbaddresses = $db->get_results("select INET_NTOA(vote_ip_int) as ip from votes where vote_type='links' and vote_user_id = $user->id order by vote_date desc limit 30");

		// Try with comments
		if (! $dbaddresses) {
			$dbaddresses = $db->get_results("select comment_ip as ip from comments where comment_user_id = $user->id and comment_date > date_sub(now(), interval 30 day) order by comment_date desc limit 30");
		}

		if (! $dbaddresses) {
			// Use register IP
			$dbaddresses = $db->get_results("select user_ip as ip from users where user_id = $user->id");
		}

		$prev_address = '';
		foreach ($dbaddresses as $dbaddress) {
			$ip_pattern = preg_replace('/\.[0-9]+$/', '', $dbaddress->ip);
			if($ip_pattern != $prev_address) {
				$addresses[] = $ip_pattern;
				$clone_counter++;
				$prev_address = $ip_pattern;
				if ($clone_counter >= 30) break;
			}
		}
	}

	$prefs = array();
	$prefs['use_bar'] = User::get_pref($user->id, 'use_bar');
	$prefs['last_com_first'] = User::get_pref($user->id, 'last_com_first');
	$vars = compact(
		'post', 'options', 'selected', 'rss', 'rss_title', 'geodiv',
		'user', 'my_latlng', 'url', 'nofollow', 'nclones', 'show_email',
		'entropy', 'percent', 'geo_form', 'addresses', 'friend_icon', 'prefs'
	);

	return Haanga::Load('/user/profile.html', $vars);
}


function do_history () {
	global $db, $rows, $user, $offset, $page_size, $globals;

	do_user_subheader(array(_('envíos propios') => get_user_uri($user->username, 'history'), _('votados') => get_user_uri($user->username, 'shaken'), _('favoritos') => get_user_uri($user->username, 'favorites'), _('votados por amigos') => get_user_uri($user->username, 'friends_shaken')), 0,
		'rss?sent_by='.$user->id, _('envíos en rss2'));
	$link = new Link;
	$rows = $db->get_var("SELECT count(*) FROM links WHERE link_author=$user->id");
	$links = $db->get_col("SELECT link_id FROM links WHERE link_author=$user->id ORDER BY link_date DESC LIMIT $offset,$page_size");
	if ($links) {
		foreach($links as $link_id) {
			$link->id=$link_id;
			$link->read();
			if ($link->votes > 0) {
				$link->print_summary('short');
			}
		}
	}
}

function do_favorites () {
	global $db, $rows, $user, $offset, $page_size, $globals;

	do_user_subheader(array(_('envíos propios') => get_user_uri($user->username, 'history'), _('votados') => get_user_uri($user->username, 'shaken'), _('favoritos') => get_user_uri($user->username, 'favorites'), _('votados por amigos') => get_user_uri($user->username, 'friends_shaken')), 2,
		'rss?favorites='.$user->id.'&amp;option=favorites&amp;url=source', _('favoritos en rss2'));
	$link = new Link;
	$rows = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_user_id=$user->id AND favorite_type='link'");
	$links = $db->get_col("SELECT link_id FROM links, favorites WHERE favorite_user_id=$user->id AND favorite_type='link' AND favorite_link_id=link_id ORDER BY link_date DESC LIMIT $offset,$page_size");
	if ($links) {
		foreach($links as $link_id) {
			$link->id=$link_id;
			$link->read();
			$link->print_summary('short');
		}
	}
}

function do_shaken () {
	global $db, $rows, $user, $offset, $page_size, $globals;

	if ($globals['bot']) return;

	do_user_subheader(array(_('envíos propios') => get_user_uri($user->username, 'history'), _('votados') => get_user_uri($user->username, 'shaken'), _('favoritos') => get_user_uri($user->username, 'favorites'), _('votados por amigos') => get_user_uri($user->username, 'friends_shaken')), 1,
		'rss?voted_by='.$user->id, _('votadas en rss2'));
	$link = new Link;
	$rows = -1; //$db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_user_id=$user->id");
	$links = $db->get_results("SELECT vote_link_id as id, vote_value FROM votes WHERE vote_type='links' and vote_user_id=$user->id ORDER BY vote_date DESC LIMIT $offset,$page_size");
	if ($links) {
		foreach($links as $linkdb) {
			$link->id=$linkdb->id;
			$link->read();
			if ($link->author == $user->id) continue;
			echo '<div style="max-width: 60em">';
			$link->print_summary('short', 0, false);
			if ($linkdb->vote_value < 0) {
				echo '<div class="box" style="z-index:1;margin:0 0 -5x 0;background:#FF3333;position:relative;top:-5px;left:85px;width:8em;padding: 1px 1px 1px 1px;border-color:#f00;opacity:0.9;text-align:center;font-size:0.9em;color:#fff;text-shadow: 0 1px 0 #000">';
				echo get_negative_vote($linkdb->vote_value);
				echo "</div>\n";
			}
			echo "</div>\n";
		}
		echo '<br/><span style="color: #FF6400;"><strong>'._('Nota').'</strong>: ' . _('sólo se visualizan los votos de los últimos meses') . '</span><br />';
	}
}

function do_friends_shaken () {
	global $db, $rows, $user, $offset, $page_size, $globals;

	if ($globals['bot']) return;

	do_user_subheader(array(_('envíos propios') => get_user_uri($user->username, 'history'), _('votados') => get_user_uri($user->username, 'shaken'), _('favoritos') => get_user_uri($user->username, 'favorites'), _('votados por amigos') => get_user_uri($user->username, 'friends_shaken')), 3);

	$friends = $db->get_col("select friend_to from friends where friend_type = 'manual' and friend_from = $user->id and friend_value > 0");
	if ($friends) {
		$friends_list = implode(',', $friends);
		$sql = "select distinct vote_link_id as link_id from votes where vote_type = 'links' and vote_user_id in ($friends_list) and vote_value > 0 order by vote_link_id desc";

		$links = $db->get_results("$sql LIMIT $offset,$page_size");
	}

	if ($links) {
		foreach($links as $dblink) {
			$link = Link::from_db($dblink->link_id);
			$link->do_inline_friend_votes = true;
			$link->print_summary();
		}
	}

}


function do_commented () {
	global $db, $rows, $user, $offset, $page_size, $globals, $current_user;

	do_user_subheader(array($user->username => get_user_uri($user->username, 'commented'), _('conversación').$globals['extra_comment_conversation'] => get_user_uri($user->username, 'conversation'), _('votados') => get_user_uri($user->username, 'shaken_comments'), _('favoritos') => get_user_uri($user->username, 'favorite_comments')), 0,
		'comments_rss?user_id='.$user->id, _('comentarios en rss2'));
	$rows = -1; // $db->get_var("SELECT count(*) FROM comments WHERE comment_user_id=$user->id");
	$comments = $db->get_results("SELECT comment_id, link_id, comment_type FROM comments, links WHERE comment_user_id=$user->id and link_id=comment_link_id ORDER BY comment_date desc LIMIT $offset,$page_size");
	if ($comments) {
		print_comment_list($comments, $user);
	}
}

function do_conversation () {
	global $db, $rows, $user, $offset, $page_size, $globals, $current_user;

	do_user_subheader(array($user->username => get_user_uri($user->username, 'commented'), _('conversación').$globals['extra_comment_conversation'] => get_user_uri($user->username, 'conversation'), _('votados') => get_user_uri($user->username, 'shaken_comments'), _('favoritos') => get_user_uri($user->username, 'favorite_comments')), 1,
		'comments_rss?answers_id='.$user->id, _('conversación en rss2'));
	$rows = -1; //$db->get_var("SELECT count(distinct(conversation_from)) FROM conversations WHERE conversation_user_to=$user->id and conversation_type='comment'");
	$conversation = "SELECT distinct(conversation_from) FROM conversations WHERE conversation_user_to=$user->id and conversation_type='comment' ORDER BY conversation_time desc LIMIT $offset,$page_size";
	
	$comments = $db->get_results("SELECT comment_id, link_id, comment_type FROM comments INNER JOIN links ON (link_id = comment_link_id) INNER JOIN ($conversation) AS convs ON convs.conversation_from = comments.comment_id");
	if ($comments) {
		$last_read = print_comment_list($comments, $user);
	}
	if ($last_read > 0 && $current_user->user_id == $user->id) {
		Comment::update_read_conversation($timestamp_read);
	}
}

function do_favorite_comments () {
	global $db, $rows, $user, $offset, $page_size, $globals;

	do_user_subheader(array($user->username => get_user_uri($user->username, 'commented'), _('conversación').$globals['extra_comment_conversation'] => get_user_uri($user->username, 'conversation'), _('votados') => get_user_uri($user->username, 'shaken_comments'), _('favoritos') => get_user_uri($user->username, 'favorite_comments')), 3);
	$comment = new Comment;
	$rows = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_user_id=$user->id AND favorite_type='comment'");
	$comments = $db->get_col("SELECT comment_id FROM comments, favorites WHERE favorite_user_id=$user->id AND favorite_type='comment' AND favorite_link_id=comment_id ORDER BY comment_id DESC LIMIT $offset,$page_size");
	if ($comments) {
		echo '<ol class="comments-list">';
		foreach($comments as $comment_id) {
			$comment->id=$comment_id;
			$comment->read();
			echo '<li>';
			$comment->print_summary($link, 2000, false);
			echo '</li>';
		}
		echo "</ol>\n";
	}
}

function do_shaken_comments () {
	global $db, $rows, $user, $offset, $page_size, $globals;

	do_user_subheader(array($user->username => get_user_uri($user->username, 'commented'), _('conversación').$globals['extra_comment_conversation'] => get_user_uri($user->username, 'conversation'), _('votados') => get_user_uri($user->username, 'shaken_comments'), _('favoritos') => get_user_uri($user->username, 'favorite_comments')), 2);

	$comment = new Comment;
	$rows = -1; $db->get_var("SELECT count(*) FROM votes, comments WHERE vote_type='comments' and vote_user_id=$user->id and comment_id = vote_link_id and comment_user_id != vote_user_id");
	$comments = $db->get_results("SELECT vote_link_id as id, vote_value as value FROM votes, comments WHERE vote_type='comments' and vote_user_id=$user->id  and comment_id = vote_link_id and comment_user_id != vote_user_id ORDER BY vote_date DESC LIMIT $offset,$page_size");
	if ($comments) {
		echo '<ol class="comments-list">';
		foreach($comments as $c) {
			$comment->id=$c->id;
			if ($c->value > 0) $color = '#00d';
			else $color = '#f00';
			$comment->read();
			if ($comment->author != $user->id && ! $comment->admin) {
				echo '<li>';
				$comment->print_summary(false, 1000, false);
				echo '<div class="box" style="margin:0 0 -16px 0;background:'.$color.';position:relative;top:-34px;left:30px;width:30px;height:16px;border-color:'.$color.';opacity: 0.5"></div>';
				echo '</li>';
			}
		}
		echo "</ol>\n";
	}
}

function print_comment_list($comments, $user) {
	global $globals, $current_user;

	$comment = new Comment;
	$timestamp_read = 0;
	$last_link = 0;

	$ids = array();
	foreach ($comments as $dbcomment) {
		$comment = Comment::from_db($dbcomment->comment_id);
		// Don't show admin comment if it's her own profile.
		if ($comment->type == 'admin' && ! $current_user->admin && $user->id == $comment->author) continue;
		if ($last_link != $dbcomment->link_id) {
			$link = Link::from_db($dbcomment->link_id, null, false); // Read basic
			echo '<h4>';
			echo '<a href="'.$link->get_permalink().'">'. $link->title. '</a>';
			echo ' ['.$link->comments.']';
			echo '</h4>';
			$last_link = $link->id;
		}
		if ($comment->date > $timestamp_read) $timestamp_read = $comment->date;
		echo '<ol class="comments-list">';
		echo '<li>';
		$comment->print_summary($link, 2000, false);
		echo '</li>';
		echo "</ol>\n";
		$ids[] = $comment->id;
	}
	Haanga::Load('get_total_answers_by_ids.html', array('type' => 'comment', 'ids' => implode(',', $ids)));
	// Return the timestamp of the most recent comment
	return $timestamp_read;
}


function do_friends($option) {
	global $db, $user, $globals, $current_user;


	$header_options = array(_('amigos') => get_user_uri($user->username, 'friends'), _('elegido por') => get_user_uri($user->username, 'friend_of'));
	if ($user->id == $current_user->user_id) {
		$header_options[_('ignorados')] = get_user_uri($user->username, 'ignored');
		$header_options[_('nuevos')] = get_user_uri($user->username, 'friends_new');
	}


	$prefered_id = $user->id;
	$prefered_admin = $user->admin;
	switch ($option) {
		case 3:
			do_user_subheader($header_options, $option);
			$prefered_type = 'new';
			break;
		case 2:
			do_user_subheader($header_options, $option);
			$prefered_type = 'ignored';
			break;
		case 1:
			$prefered_type = 'to';
			do_user_subheader($header_options, $option);
			break;
		default:
			do_user_subheader($header_options, $option, 'rss?friends_of='.$user->id, _('envíos de amigos en rss2'));
			$prefered_type = 'from';
	}
	echo '<div style="padding: 5px 0px 10px 5px">';
	echo '<div id="'.$prefered_type.'-container">'. "\n";
	require('backend/get_friends_bars.php');
	echo '</div>'. "\n";
	echo '</div>'. "\n";

	// Post processing
	switch ($option) {
		case 3:
			if ($user->id == $current_user->user_id) {
				User::update_new_friends_date();
			}
			break;
		default:
	}
}

function do_categories() {
	global $globals, $current_user, $db, $user, $login;

	$options = array();
	$options[$user->username] = get_user_uri($user->username);
	$options[_('categorías personalizadas')] = get_user_uri($user->username, 'categories');
	if ($current_user->user_id == $user->id || $current_user->user_level == 'god') {
		$options[_('modificar perfil').' &rarr;'] = $globals['base_url'].'profile.php?login='.urlencode($login);
	}

	do_user_subheader($options, 1, 'rss?personal='.$user->id, _('categorías personalizadas en rss2'));

	if (is_array($_POST['categories'])) {
		$db->query("delete from prefs where pref_user_id = $current_user->user_id and pref_key = 'category_".SitesMgr::my_id()."'");
		$total = count(SitesMgr::get_categories());
		if (count($_POST['categories']) < $total) {
			for ($i=0; $i<count($_POST['categories']); $i++){
				$cat = intval($_POST['categories'][$i]);
				$db->query("insert into prefs (pref_user_id, pref_key, pref_value) values ($current_user->user_id, 'category_".SitesMgr::my_id()."', $cat)");
			}
		}
	}
	print_categories_checkboxes($user);
}

function print_categories_checkboxes($user) {
	global $db, $current_user;


	// Get selected categories
	$selected_set = $db->get_col("SELECT pref_value FROM prefs WHERE pref_user_id = $user->id and pref_key = 'category_".SitesMgr::my_id()."'");
	$selected = array();
	if ($selected_set) {
		foreach ($selected_set as $cat) {
			$selected[$cat] = true;
		}
	}

	$metas = SitesMgr::get_metas();
	$categories = array();
	foreach ($metas as $meta) {
		$categories[$meta->id] = SitesMgr::get_categories($meta->id);
		if ( $selected ) {
			// Check if all categories are selected for the current meta
			$all = true;
			foreach ($categories[$meta->id] as $sel) {
				if (!isset($selected[$sel->id])) $all = false;
			}
			if ($all) $selected[$meta->id] = true;
		}
	}

	Haanga::Load('user/categories.html', compact('user', 'metas', 'categories', 'selected'));
}

function do_user_subheader($options, $selected = false, $rss = false, $rss_title = '') {
	global $globals;

	// arguments: hash array with "button text" => "button URI"; Nº of the selected button

	$vars = compact(
		'options', 'selected', 'rss', 'rss_title'
	);
	return Haanga::Load('/user/subheader.html', $vars);
}

function do_subs() {
	global $db, $user, $current_user;

	$title = _('subs de') . " $user->username";
	if ($current_user->admin && $user->id == $current_user->user_id) {
		$sql = "select subs.* from subs where subs.sub = 1 and (subs.owner = $user->id or subs.owner = 0)";
	} else {
		$sql = "select subs.* from subs where subs.sub = 1 and subs.owner = $user->id";
	}
	$subs = $db->get_results($sql);
	if ($current_user->user_id > 0 && $user->id == $current_user->user_id && SitesMgr::can_edit(0)) $can_edit = true;
	else $can_edit = false;

	Haanga::Load('subs.html', compact('title', 'subs', 'can_edit'));
}

?>
