<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'comment.php');
include(mnminclude.'link.php');
	
if(!empty($_REQUEST['rows'])) {
	$rows = intval($_REQUEST['rows']);
	if ($rows > 300) $rows = 100; //avoid abuses
} else $rows = 100;
	
// Bug in FeedBurner, it needs all items
if (preg_match('/feedburner/i', $_SERVER['HTTP_USER_AGENT'])) {
	$if_modified = 0;
} else {
	$if_modified = get_if_modified();
}


if(!empty($_GET['id'])) {
	//
	// Link comments
	//
	$id = intval($_GET['id']);
	if ($if_modified > 0) 
		$from_time = "AND comment_date > FROM_UNIXTIME($if_modified)";
	$sql = "SELECT comment_id FROM comments WHERE comment_link_id=$id $from_time ORDER BY comment_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comment_date) FROM comments WHERE comment_link_id=$id ORDER BY comment_date DESC LIMIT 1");
	$title = _('Menéame: comentarios') . " [$id]";
	$globals['redirect_feedburner'] = false;
} elseif(!empty($_GET['user_id'])) {
	//
	// Users comments
	//
	$id = intval($_GET['user_id']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	if ($if_modified > 0) 
		$from_time = "AND comment_date > FROM_UNIXTIME($if_modified)";
	$sql = "SELECT comment_id FROM comments WHERE comment_user_id=$id $from_time ORDER BY comment_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comment_date) FROM comments WHERE comment_user_id=$id ORDER BY comment_date DESC LIMIT 1");
	$title = _('Menéame: comentarios de ') . $username;
	$globals['redirect_feedburner'] = false;
} elseif(!empty($_GET['conversation_id'])) {
	// 
	// Comments in news where the user has commented
	//
	$id = intval($_GET['conversation_id']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	if ($if_modified > 0 && $if_modified > time() - 86400*3 ) 
		$from_time = "FROM_UNIXTIME($if_modified)";
	else 
		$from_time = "date_sub(now(), interval 48 hour)";
	$sql = "SELECT DISTINCT comments1.comment_id FROM comments AS comments1  INNER JOIN comments AS comments2 WHERE comments1.comment_link_id = comments2.comment_link_id AND comments2.comment_user_id=$id AND comments2.comment_date > $from_time order by comments1.comment_id desc LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comments1.comment_date) FROM comments AS comments1  INNER JOIN comments AS comments2 WHERE comments1.comment_link_id = comments2.comment_link_id AND comments2.comment_user_id=$id AND comments2.comment_date > $from_time order by comments1.comment_id desc LIMIT 1");
	$title = _('Menéame: conversación de ') . $username;
	$globals['redirect_feedburner'] = false;
} elseif(!empty($_GET['author_id'])) {
	//
	// User's link comments
	//
	$id = intval($_GET['author_id']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	if ($if_modified > 0) 
		$from_time = "AND comment_date > FROM_UNIXTIME($if_modified)";
	$sql = "SELECT comment_id FROM comments, links  WHERE link_author=$id and comment_link_id=link_id $from_time ORDER BY comment_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comment_date) FROM comments, links WHERE link_author=$id and comment_link_id=link_id ORDER BY comment_date DESC LIMIT 1");
	$title = _('Menéame: comentarios noticias de ') . $username;
	$globals['redirect_feedburner'] = false;
} else {
	//
	// All comments
	//
	$id = 0;
	if ($if_modified > 0) 
		$from_time = "WHERE comment_date > FROM_UNIXTIME($if_modified)";
	$sql = "SELECT comment_id FROM comments $from_time ORDER BY comment_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comment_date) FROM comments ORDER BY comment_date DESC LIMIT 1");
	$title = _('Menéame: comentarios');
	$globals['redirect_feedburner'] = false;
}

	/*****  WARNING
		this function is to redirect to feed burner
		comment it out
		You have been warned 
	***/

	if (!$search && empty($_REQUEST['category'])) {
		check_redirect_to_feedburner();
	}
	
	/****
	END WARNING ******/


do_header($title);

$comment = new Comment;
$link = new Link;
$comments = $db->get_col($sql);
if ($comments) {
	foreach($comments as $comment_id) {
		$comment->id=$comment_id;
		$comment->read();
		$content = save_text_to_html($comment->content);
		echo "	<item>\n";
		$link_id = $link->id = $comment->link;
		$link->read();
		$link_title = $db->get_var("select link_title from links where link_id = $link_id");
		// Title must not carry htmlentities
		echo "		<title><![CDATA[".html_entity_decode($link_title)." ]]></title>\n";
		echo "		<link>".$link->get_permalink()."#comment-".$comment->order."</link>\n";
		echo "		<pubDate>".date("r", $comment->date)."</pubDate>\n";
		echo "		<dc:creator>$comment->username</dc:creator>\n";
		echo "		<guid>".$link->get_permalink()."#comment-".$comment->order."</guid>\n";
		echo "		<description><![CDATA[<p><strong>#$comment->order</strong>&nbsp;&nbsp;$content";
		echo '</p><p>&#187;&nbsp;'._('autor').': <strong>'.$comment->username.'</strong></p>';
//		echo '<p><img src="http://'. get_server_name() .$globals['base_url'].'backend/vote_com_img.php?id='. $link->id .'" alt="votes" width=200, height=16 /></p>';
		echo "]]></description>\n";
		echo "	</item>\n\n";
	}
}

do_footer();

function do_header($title) {
	global $if_modified, $last_modified, $dblang, $home, $globals;

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
	echo '     xmlns:content="http://purl.org/rss/1.0/modules/content/"'."\n";
	echo '     xmlns:wfw="http://wellformedweb.org/CommentAPI/"'."\n";
	echo '     xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
	echo ' >'. "\n";
	echo '<channel>'."\n";
	echo'	<title>'.$title.'</title>'."\n";
	echo'	<link>http://'.get_server_name().$home.'</link>'."\n";
	echo"	<image><title>".get_server_name()."</title><link>http://".get_server_name()."</link><url>http://".get_server_name().$globals['base_url']."img/es/logo01-rss.gif</url></image>\n";
	echo'	<description>'._('Sitio colaborativo de publicación y comunicación entre blogs').'</description>'."\n";
	echo'	<pubDate>'.date("r", $last_modified).'</pubDate>'."\n";
	echo'	<generator>http://blog.meneame.net/</generator>'."\n";
	echo'	<language>'.$dblang.'</language>'."\n";
}

function do_footer() {
	echo "</channel>\n</rss>\n";
}

function check_redirect_to_feedburner() {
	global $globals; 

	if (!$globals['redirect_feedburner'] || preg_match('/feedburner/', htmlspecialchars($_SERVER['PHP_SELF'])) || preg_match('/feedburner/i', $_SERVER['HTTP_USER_AGENT'])) return;

	header("Location: http://feeds.feedburner.com/meneame/comments");
	exit();
}
?>
