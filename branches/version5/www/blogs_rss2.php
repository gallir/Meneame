<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2009 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


// Use the alternate server for api, if it exists
$globals['alternate_db_server'] = 'api';
include('config.php');

if(!empty($_REQUEST['rows'])) {
	$rows = intval($_REQUEST['rows']);
	if ($rows > 300) $rows = 100; //avoid abuses
} else $rows = 100;
	
$if_modified = get_if_modified();

// All comments
//
$from_time = '';
if ($if_modified > 0) {
	$from_time = "rss.date > FROM_UNIXTIME($if_modified) AND ";
}


$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(comment_date) FROM comments ORDER BY comment_date DESC LIMIT 1");
$entries = $db->get_results("select rss.blog_id, rss.user_id, unix_timestamp(rss.date_parsed) as date, title, url, user_login, user_avatar, blogs.blog_url, blogs.blog_title from rss, users, blogs where $from_time rss.blog_id = blogs.blog_id and rss.user_id = users.user_id order by rss.date_parsed desc limit $rows");

$title = _('Menéame').': '._('blogs');

if ( !$entries && $if_modified) {
	header('HTTP/1.1 304 Not Modified');
	exit();
}

do_header($title);

if ($entries) {
	foreach($entries as $entry) {
		echo "	<item>\n";
		echo "		<meneame:user>$entry->user_login</meneame:user>\n";
		echo "		<meneame:url>".'http://'.get_server_name().$globals['base_url'].'rsss.php'."</meneame:url>\n";

		// Title must not carry htmlentities
		echo "		<title>".htmlentities2unicodeentities(strip_tags($entry->title))."</title>\n";
		echo "		<link>".$entry->url."</link>\n";
		echo "		<pubDate>".date("r", $entry->date)."</pubDate>\n";
		echo "		<dc:creator>$entry->user_login</dc:creator>\n";
		echo "		<guid>".$entry->url."</guid>\n";
		echo "		<description></description>\n";
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
	if ($if_modified) {
		header('X-If-Modified: '. gmdate('D, d M Y H:i:s',$if_modified));
	}
	header('X-Last-Modified: '. gmdate('D, d M Y H:i:s',$last_modified));
	if ($last_modified <= $if_modified) {
		header('HTTP/1.1 304 Not Modified');
		exit();
	}
	header('Last-Modified: ' .  gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
	header('Content-type: text/xml; charset=UTF-8', true);
	echo '<?xml version="1.0" encoding="UTF-8"?'.'>' . "\n";
	echo '<rss version="2.0" '."\n";
	echo '     xmlns:atom="http://www.w3.org/2005/Atom"'."\n";
	echo '     xmlns:content="http://purl.org/rss/1.0/modules/content/"'."\n";
	echo '     xmlns:wfw="http://wellformedweb.org/CommentAPI/"'."\n";
	echo '     xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
	echo '     xmlns:meneame="http://meneame.net/faq-es.php"'."\n";
	echo ' >'. "\n";
	echo '<channel>'."\n";
	echo '	<atom:link href="http://'.get_server_name().$globals['base_url'].'blogs_rss2.php" rel="self" type="application/rss+xml" />'."\n";
	echo'	<title>'.$title.'</title>'."\n";
	echo'	<link>http://'.get_server_name().$globals['base_url'].'rsss.php</link>'."\n";
	echo"	<image><title>".$title."</title><link>http://".get_server_name().$globals['base_url']."rsss.php</link><url>http://".get_static_server_name().$globals['base_url']."img/mnm/eli-rss.png</url></image>\n";
	echo'	<description>'._('blogs de usuarios de Menéame').'</description>'."\n";
	echo'	<pubDate>'.date("r", $last_modified).'</pubDate>'."\n";
	echo'	<generator>http://blog.meneame.net/</generator>'."\n";
	echo'	<language>'.$dblang.'</language>'."\n";
}

function do_footer() {
	echo "</channel>\n</rss>\n";
}

?>
