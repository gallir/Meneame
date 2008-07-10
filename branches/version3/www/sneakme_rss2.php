<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'post.php');
include(mnminclude.'geo.php');

stats_increment('rss', true);
	
if(!empty($_REQUEST['rows'])) {
	$rows = intval($_REQUEST['rows']);
	if ($rows > 300) $rows = 100; //avoid abuses
} else $rows = 100;
	
$if_modified = get_if_modified();
if ($if_modified < time() - 250000) { // Last 3 days at most
	$if_modified = time() - 250000;
}
$from_time = "post_date > FROM_UNIXTIME($if_modified)";



if(!empty($_GET['user_id'])) {
	//
	// Users posts
	//
	$id = guess_user_id($_GET['user_id']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	$sql = "SELECT post_id FROM posts WHERE post_user_id=$id and $from_time ORDER BY post_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(post_date) FROM posts WHERE post_user_id=$id ORDER BY post_date DESC LIMIT 1");
	$title = _('Nótame: notas de ') . $username;
} elseif(!empty($_REQUEST['friends_of'])) {
	//
	// User's friend posts
	//
	$id = guess_user_id($_GET['friends_of']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	$sql = "SELECT post_id FROM posts, friends WHERE friend_type='manual' and friend_from = $id and friend_to=post_user_id and friend_value > 0 and $from_time ORDER BY post_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(post_date) FROM posts, friends WHERE friend_type='manual' and friend_from = $id and friend_to=post_user_id and friend_value > 0 ORDER BY post_date DESC LIMIT 1");
	$title = _('Nótame: notas amigos de ') . $username;
} else {
	//
	// All posts
	//
	$id = 0;
	$sql = "SELECT post_id FROM posts WHERE $from_time ORDER BY post_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(post_date) FROM posts ORDER BY post_date DESC LIMIT 1");
	$title = _('Nótame: notas');
}


do_header($title);

$post = new Post;
$posts = $db->get_col($sql);
if ($posts) {
	foreach($posts as $post_id) {
		$post->id=$post_id;
		$post->read();
		$title = strip_tags(text_to_summary($post->content, 40));
		$title = $post->username.': ' . htmlentities2unicodeentities($title);
		$content = htmlentities2unicodeentities(put_smileys(save_text_to_html($post->content)));
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
	header('Last-Modified: ' .  gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
	header('Content-type: text/xml; charset=UTF-8', true);
	echo '<?xml version="1.0" encoding="UTF-8"?'.'>' . "\n";
	echo '<rss version="2.0" '."\n";
	echo '  xmlns:atom="http://www.w3.org/2005/Atom"'."\n";
	echo '	xmlns:content="http://purl.org/rss/1.0/modules/content/"'."\n";
	echo '	xmlns:wfw="http://wellformedweb.org/CommentAPI/"'."\n";
	echo '	xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
	echo '	xmlns:georss="http://www.georss.org/georss"'."\n";
	echo ' >'. "\n";
	echo '<channel>'."\n";
	echo '	<title>'.$title.'</title>'."\n";
	echo '  <atom:link href="http://'.get_server_name().htmlentities(clean_input_url($_SERVER['REQUEST_URI'])).'" rel="self" type="application/rss+xml" />'."\n";
	echo '	<link>http://'.get_server_name().post_get_base_url().'</link>'."\n";
	echo "	<image><title>".$title."</title><link>http://".get_server_name().post_get_base_url()."</link><url>http://".get_server_name().$globals['base_url']."img/common/logo01-rss.gif</url></image>\n";
	echo '	<description>'._('Sitio colaborativo de publicación y comunicación entre blogs').'</description>'."\n";
	echo '	<pubDate>'.date("r", $last_modified).'</pubDate>'."\n";
	echo '	<generator>http://blog.meneame.net/</generator>'."\n";
	echo '	<language>'.$dblang.'</language>'."\n";
}

function do_footer() {
	echo "</channel>\n</rss>\n";
}

?>
