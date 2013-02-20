<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2009 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');

// Use the alternate server for api, if it exists
$globals['alternate_db_server'] = 'api';

include(mnminclude.'geo.php');

include(mnminclude.'ban.php');
if (check_ip_noaccess() || check_ban_proxy()) {
	die;
}

if(!empty($_REQUEST['rows'])) {
	$rows = intval($_REQUEST['rows']);
	if ($rows > 300) $rows = 100; //avoid abuses
} else $rows = 100;
	
$if_modified = get_if_modified();
if ($if_modified) {
	if ($if_modified < time() - 250000) { // Last 3 days at most
		$if_modified = time() - 250000;
	}
	$from_time = "post_date > FROM_UNIXTIME($if_modified)";
	$from_time_conversation = "conversation_date > FROM_UNIXTIME($if_modified)";
} $from_time = 'True'; // Trick to avoid sql errors with empty "and's"



if ($_REQUEST['q']) {
	include(mnminclude.'search.php');
	if ($if_modified) {
		$_REQUEST['t'] = $if_modified;
	}
	$_REQUEST['w'] = 'posts';
	$search_ids = do_search(true);
	if ($search_ids['ids']) {
		$ids = implode(",", $search_ids['ids']);
		$sql = "SELECT post_id FROM posts WHERE post_id in ($ids) ORDER BY post_id DESC LIMIT $rows";
		$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(post_date) FROM posts WHERE post_id in ($ids) ORDER BY post_id DESC LIMIT 1");
	}
	$title = _('Nótame').': '._('búsqueda en notas') . ': ' . htmlspecialchars(strip_tags($_REQUEST['q']));
	$globals['redirect_feedburner'] = false;
} elseif (!empty($_GET['user_id'])) {
	//
	// Users posts
	//
	$id = guess_user_id($_GET['user_id']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	$sql = "SELECT post_id FROM posts WHERE post_user_id=$id and $from_time ORDER BY post_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(post_date) FROM posts WHERE post_user_id=$id ORDER BY post_date DESC LIMIT 1");
	$title = _('Nótame').': '.sprintf(_('notas de %s'), $username);
} elseif(!empty($_REQUEST['friends_of'])) {
	//
	// User's friend posts
	//
	$id = guess_user_id($_GET['friends_of']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	$sql = "SELECT post_id FROM posts, friends WHERE friend_type='manual' and friend_from = $id and friend_to=post_user_id and friend_value > 0 and $from_time ORDER BY post_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(post_date) FROM posts, friends WHERE friend_type='manual' and friend_from = $id and friend_to=post_user_id and friend_value > 0 ORDER BY post_date DESC LIMIT 1");
	$title = _('Nótame').': '.sprintf(_('amigos de %s'), $username);
} elseif (!empty($_REQUEST['favorites_of'])) {
	/////
	// users' favorites
	/////
	$user_id = guess_user_id($_REQUEST['favorites_of']);
	$username = $db->get_var("select user_login from users where user_id=$user_id");
	$sql = "SELECT post_id FROM posts, favorites WHERE favorite_user_id=$user_id AND favorite_type='post' AND favorite_link_id=post_id ORDER BY favorite_date DESC limit $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(max(favorite_date)) from favorites where favorite_user_id=$user_id AND favorite_type='post'");
	$title = _('Nótame').': '.sprintf(_('favoritos de %s'), $username);
} elseif(!empty($_REQUEST['conversation_of'])) {
	//
	// Conversation posts
	//
	$id = guess_user_id($_GET['conversation_of']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	$sql = "SELECT conversation_from as post_id FROM conversations WHERE conversation_user_to=$id and conversation_type='post' ORDER BY conversation_time desc LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(conversation_time) FROM conversations WHERE conversation_user_to=$id and conversation_type='post' ORDER BY conversation_time DESC LIMIT 1");
	$title = _('Nótame').': '.sprintf(_('conversación de %s'), $username);
} else {
	//
	// All posts
	//
	$id = 0;
	$sql = "SELECT post_id FROM posts WHERE $from_time ORDER BY post_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(post_date) FROM posts ORDER BY post_date DESC LIMIT 1");
	$title = _('Nótame').': '._('notas');
}


do_header($title);

$post = new Post;
if ($sql) $posts = $db->get_col($sql);
if ($posts) {
	foreach($posts as $post_id) {
		$post = Post::from_db($post_id);
		if (!$post) continue;
		$title = text_to_summary($post->clean_content(), 40);
		$title = $post->username.': ' . htmlentities2unicodeentities($title);
		$content = htmlentities2unicodeentities(put_smileys($post->to_html($post->clean_content())));
		echo "	<item>\n";
		echo "		<title>$title</title>\n";
		echo "		<link>http://".get_server_name().post_get_base_url($post->username).'/'.$post->id."</link>\n";
		echo "		<pubDate>".date("r", $post->date)."</pubDate>\n";
		echo "		<dc:creator>$post->username</dc:creator>\n";
		echo "		<guid>http://".get_server_name().post_get_base_url($post->username).'/'.$post->id."</guid>\n";
		// Insert GEO
		if (($latlng = geo_latlng('user', $post->author))) {
			echo "		<georss:point>$latlng->lat $latlng->lng</georss:point>\n";
		}
		echo "		<description><![CDATA[$content";
		echo '</p><p>&#187;&nbsp;'._('autor').': <strong>'.$post->username.'</strong></p>';
		echo "]]></description>\n";
		echo "	</item>\n\n";
	}
}

do_footer();

function do_header($title) {
	global $if_modified, $last_modified, $dblang, $globals;

	if (!$last_modified > 0) { 
		if ($if_modified > 0)
			$last_modified = $if_modified;
		else 
			$last_modified = time();
	}
	header('X-If-Modified: '. gmdate('D, d M Y H:i:s',$if_modified));
	header('X-Last-Modified: '. gmdate('D, d M Y H:i:s',$last_modified));
	if ($last_modified <= $if_modified) {
		header('HTTP/1.1 304 Not Modified');
		exit();
	}
	header('Last-Modified: ' .	gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
	header('Content-type: text/xml; charset=UTF-8', true);
	echo '<?xml version="1.0" encoding="UTF-8"?'.'>' . "\n";
	echo '<rss version="2.0" '."\n";
	echo '	xmlns:atom="http://www.w3.org/2005/Atom"'."\n";
	echo '	xmlns:content="http://purl.org/rss/1.0/modules/content/"'."\n";
	echo '	xmlns:wfw="http://wellformedweb.org/CommentAPI/"'."\n";
	echo '	xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
	echo '	xmlns:georss="http://www.georss.org/georss"'."\n";
	echo ' >'. "\n";
	echo '<channel>'."\n";
	echo '	<title>'.$title.'</title>'."\n";
	echo '	<atom:link href="http://'.get_server_name().htmlentities(clean_input_url($_SERVER['REQUEST_URI'])).'" rel="self" type="application/rss+xml" />'."\n";
	echo '	<link>http://'.get_server_name().post_get_base_url().'</link>'."\n";
	echo "	<image><title>".$title."</title><link>http://".get_server_name().post_get_base_url()."</link><url>http://".get_static_server_name().$globals['base_url']."img/common/eli-rss.png</url></image>\n";
	echo '	<description>'._('Sitio colaborativo de publicación y comunicación entre blogs').'</description>'."\n";
	echo '	<pubDate>'.date("r", $last_modified).'</pubDate>'."\n";
	echo '	<generator>http://blog.meneame.net/</generator>'."\n";
	echo '	<language>'.$dblang.'</language>'."\n";
}

function do_footer() {
	echo "</channel>\n</rss>\n";
}

?>
