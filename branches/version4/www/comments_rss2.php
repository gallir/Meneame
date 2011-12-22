<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2009 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');

if(!empty($_REQUEST['rows'])) {
	$rows = intval($_REQUEST['rows']);
	if ($rows > 300) $rows = 200; //avoid abuses
} else $rows = 200;

// Bug in FeedBurner, it needs all items
if (preg_match('/feedburner/i', $_SERVER['HTTP_USER_AGENT'])) {
	$if_modified = 0;
} else {
	$if_modified = get_if_modified();
	if ($if_modified < time() - 3*86400) $if_modified = 0;
}

$individual_user = false;
if ($_REQUEST['q']) {
	include(mnminclude.'search.php');
	if ($if_modified) {
		$_REQUEST['t'] = $if_modified;
	}
	$_REQUEST['w'] = 'comments';
	$search_ids = do_search(true);
	if (!empty($search_ids['ids'])) {
		$ids = implode(",", $search_ids['ids']);
		$sql = "SELECT comment_id FROM comments WHERE comment_id in ($ids) ORDER BY comment_id DESC LIMIT $rows";
		$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comment_date) FROM comments WHERE comment_id in ($ids) ORDER BY comment_id DESC LIMIT 1");
	}
	$title = $globals['site_name'].': '._('búsqueda en comentarios') . ': ' . htmlspecialchars(strip_tags($_REQUEST['q']));
	$globals['redirect_feedburner'] = false;
} elseif(!empty($_GET['id'])) {
	//
	// Link comments
	//
	$id = intval($_GET['id']);
	if ($if_modified) {
		$extra_sql = "AND comment_date > FROM_UNIXTIME($if_modified) ";
	}
	if (isset($_GET['min_karma'])) {
		$extra_sql .= ' AND comment_karma >= '.intval($_GET['min_karma']);
	}
	$sql = "SELECT comment_id FROM comments WHERE comment_link_id=$id $extra_sql ORDER BY comment_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comment_date) FROM comments WHERE comment_link_id=$id ORDER BY comment_date DESC LIMIT 1");
	$title = $globals['site_name'].': '._('comentarios') . " [$id]";
	$globals['redirect_feedburner'] = false;
} elseif(!empty($_GET['user_id'])) {
	//
	// Users comments
	//
	$individual_user = true;
	$id = guess_user_id($_GET['user_id']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	if ($if_modified > 0)
		$from_time = "AND comment_date > FROM_UNIXTIME($if_modified)";
	$sql = "SELECT comment_id FROM comments WHERE comment_user_id=$id $from_time ORDER BY comment_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comment_date) FROM comments WHERE comment_user_id=$id ORDER BY comment_date DESC LIMIT 1");
	$title = $globals['site_name'].': '.sprintf(_('comentarios de %s'), $username);
	$globals['redirect_feedburner'] = false;
} elseif(!empty($_GET['conversation_id'])) {
	//
	// Comments in news where the user has commented
	//
	$id = guess_user_id($_GET['conversation_id']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	if ($if_modified > 0 && $if_modified > time() - 86400*3 )
		$from_time = "FROM_UNIXTIME($if_modified)";
	else
		$from_time = "date_sub(now(), interval 5 day)";
	$sql = "SELECT DISTINCT comments1.comment_id FROM comments AS comments1  INNER JOIN comments AS comments2 WHERE comments1.comment_link_id = comments2.comment_link_id AND comments2.comment_user_id=$id AND comments2.comment_date > $from_time order by comments1.comment_id desc LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comments1.comment_date) FROM comments AS comments1  INNER JOIN comments AS comments2 WHERE comments1.comment_link_id = comments2.comment_link_id AND comments2.comment_user_id=$id AND comments2.comment_date > $from_time order by comments1.comment_id desc LIMIT 1");
	$title = $globals['site_name'].': '.sprintf(_('conversación de %s'), $username);
	$globals['redirect_feedburner'] = false;
} elseif(!empty($_GET['author_id'])) {
	//
	// User's link comments
	//
	$individual_user = true;
	$id = guess_user_id($_GET['author_id']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	if ($if_modified > 0)
		$from_time = "AND comment_date > FROM_UNIXTIME($if_modified)";
	$sql = "SELECT comment_id FROM comments, links  WHERE link_author=$id and comment_link_id=link_id $from_time ORDER BY comment_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comment_date) FROM comments, links WHERE link_author=$id and comment_link_id=link_id ORDER BY comment_date DESC LIMIT 1");
	$title = $globals['site_name'].': '.sprintf(_('comentarios noticias de %s'), $username);
	$globals['redirect_feedburner'] = false;
} elseif(!empty($_GET['answers_id'])) {
	//
	// Answers to this user's comments
	//
	$individual_user = true;
	$id = guess_user_id($_GET['answers_id']);
	$username = $db->get_var("select user_login from users where user_id=$id");
	if ($if_modified > 0)
		$from_time = "AND conversation_time > FROM_UNIXTIME($if_modified)";
	$sql = "SELECT conversation_from FROM conversations  WHERE  conversation_user_to=$id and conversation_type='comment' $from_time ORDER BY conversation_time DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(conversation_time) FROM conversations WHERE conversation_user_to=$id and conversation_type='comment'  ORDER BY conversation_time DESC LIMIT 1");
	$title = $globals['site_name'].': '.sprintf(_('respuestas a %s'), $username);
	$globals['redirect_feedburner'] = false;
} else {
	//
	// All comments
	//
	$id = 0;
	if ($if_modified > 0)
		$from_time = "AND comment_date > FROM_UNIXTIME($if_modified)";
	else
		$from_time = "AND comment_date > date_sub(now(), interval 2 day)";
	$sql = "SELECT comment_id FROM comments, links WHERE link_id = comment_link_id ".$globals['allowed_categories_sql']." $from_time ORDER BY comment_date DESC LIMIT $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comment_date) FROM comments ORDER BY comment_date DESC LIMIT 1");
	$title = $globals['site_name'].': '._('comentarios');
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



if (!empty($sql)) $comments = $db->get_col($sql);

if ( !$comments && $if_modified) {
	header('HTTP/1.1 304 Not Modified');
	exit();
}
do_header($title);

if ($comments) {
	foreach($comments as $comment_id) {
		$comment = Comment::from_db($comment_id);
		if (!$comment) continue;

		if ($comment->type == 'admin') {
			if ($individual_user) continue;
			else $comment->username = get_server_name();
		}
		if ($comment->user_level == 'disabled') {
			$content = '['._('Usuario deshabilitado').']';
		} else {
			$content = htmlentities2unicodeentities($comment->to_html($comment->content));
		}
		echo "	<item>\n";
		$link = Link::from_db($comment->link);
		echo "		<meneame:comment_id>$comment->id</meneame:comment_id>\n";
		echo "		<meneame:link_id>$comment->link</meneame:link_id>\n";
		echo "		<meneame:order>$comment->c_order</meneame:order>\n";
		echo "		<meneame:user>$comment->username</meneame:user>\n";
		echo "		<meneame:votes>".intval($comment->votes)."</meneame:votes>\n";
		echo "		<meneame:karma>".intval($comment->karma)."</meneame:karma>\n";
		echo "		<meneame:url>".'http://'.get_server_name().$comment->get_relative_individual_permalink()."</meneame:url>\n";

		// Title must not carry htmlentities
		echo "		<title>#$comment->order ".htmlentities2unicodeentities($link->title)."</title>\n";
		echo "		<link>".$link->get_permalink()."/000".$comment->order."</link>\n";
		echo "		<pubDate>".date("r", $comment->date)."</pubDate>\n";
		echo "		<dc:creator>$comment->username</dc:creator>\n";
		echo "		<guid>".$link->get_permalink()."/000".$comment->order."</guid>\n";
		echo "		<description><![CDATA[<p>$content";
		echo '</p><p>&#187;&nbsp;'._('autor').': <strong>'.$comment->username.'</strong></p>';
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
	echo '     xmlns:content="http://purl.org/rss/1.0/modules/content/"'."\n";
	echo '     xmlns:wfw="http://wellformedweb.org/CommentAPI/"'."\n";
	echo '     xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
	echo '     xmlns:meneame="http://meneame.net/faq-es.php"'."\n";
	echo ' >'. "\n";
	echo '<channel>'."\n";
	echo'	<title>'.$title.'</title>'."\n";
	echo'	<link>http://'.get_server_name().'</link>'."\n";
	echo"	<image><title>".get_server_name()."</title><link>http://".get_server_name()."</link><url>http://".get_static_server_name().$globals['base_url']."img/mnm/eli-rss.png</url></image>\n";
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
