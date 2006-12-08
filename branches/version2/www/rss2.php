<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'link.php');

if(!empty($_REQUEST['rows'])) {
	$rows = intval($_REQUEST['rows']);
	if ($rows > 200) $rows = 50; //avoid abuses
} else $rows = 50;
	
// Bug in FeedBurner, it needs all items
if (preg_match('/feedburner/i', $_SERVER['HTTP_USER_AGENT'])) {
	$if_modified = 0;
} else {
	$if_modified = get_if_modified();
}

if(!empty($_REQUEST['time'])) {
	// Prepare for times
	if(!($time = check_integer('time')))
		die;
	$sql = "SELECT link_id, link_votes as votes FROM links WHERE ";	
	if ($time > 0) {
		$from = time()-$time;
		$sql .= "link_date > FROM_UNIXTIME($from) AND ";
	}
	$sql .= "link_status != 'discard' ORDER BY link_votes DESC LIMIT $rows";
	$last_modified = time();
	$title = _('Menéame: más votadas en') . ' ' . txt_time_diff($from);
	//$link_date = "modified";
	$link_date = "";
} else {
	// All the others
	$search = get_search_clause('boolean');
	// The link_status to search
	if(!empty($_REQUEST['status'])) {
		$status = $db->escape(clean_input_string(trim($_REQUEST['status'])));
	} else {
		// By default it searches on all
		if($search) $status = 'all';
		else $status = 'published';
	}
	/*****  WARNING
		this function is to redirect to feed burner
		comment it out
		You have been warned ******/

	if (!$search && empty($_REQUEST['category'])) {
		check_redirect_to_feedburner($status);
	}
	
	/*****  END WARNING ******/
	
	
	switch ($status) {
		case 'published':
			$order_field = 'link_published_date';
			$link_date = 'published_date';
			$title = _('Menéame: publicadas');
			break;
		case 'queued':
			$title = _('Menéame: en cola');
			$order_field = 'link_date';
			$link_date = "date";
			$home = "/shakeit.php";
			break;
		case 'all':
			$title = _('Menéame: todas');
			$order_field = 'link_date';
			$link_date = "date";
			break;
	}
	
	
	if($status == 'all') {
		$from_where = "FROM links WHERE link_status!='discard' ";
	} else {
		$from_where = "FROM links WHERE link_status='$status' ";
	}
	if(($cat=check_integer('category'))) {
		$from_where .= " AND link_category=$cat ";
		$category_name = $db->get_var("SELECT category_name FROM categories WHERE category_id = $cat AND category_lang='$dblang'");
		$title .= " -$category_name-";
	}
	
	if($search) {
		$from_where .= "AND $search";
		$title = _('Menéame') . ": " . htmlspecialchars(strip_tags($_REQUEST['search']));
	}
	
	$order_by = " ORDER BY $order_field DESC ";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(max($order_field)) links $from_where");
	if ($if_modified > 0) {
		$from_where .= " AND $order_field > FROM_UNIXTIME($if_modified)";
	}
	$sql = "SELECT link_id $from_where $order_by LIMIT $rows";
}

if ($last_modified <= $if_modified) {
	header('HTTP/1.1 304 Not Modified');
	exit();
}



do_header($title);

$link = new Link;
$links = $db->get_col($sql);
if ($links) {
	foreach($links as $link_id) {
		$link->id=$link_id;
		$link->read();
		$category_name = $db->get_var("SELECT category_name FROM categories WHERE category_id = $link->category AND category_lang='$dblang'");
		$content = text_to_html($link->content);
		echo "	<item>\n";
		// Title must not carry htmlentities
		echo "		<title><![CDATA[".html_entity_decode($link->title)."]]></title>\n";
		echo "		<link>".$link->get_permalink()."</link>\n";
		echo "		<comments>".$link->get_permalink()."</comments>\n";
		if (!empty($link_date))
			echo "		<pubDate>".date("r", $link->$link_date)."</pubDate>\n";
		else echo "      <pubDate>".date("r", time())."</pubDate>\n";
		echo "		<dc:creator>$link->username</dc:creator>\n";
		echo "		<category>$category_name</category>\n";
		// Add tags as categories
		if (!empty($link->tags)) {
			$tags_array = explode(",", $link->tags);
			foreach ($tags_array as $tag_item) {
				$tag_item=trim($tag_item);
				echo "		<category>$tag_item</category>\n";
			}
		}
		echo "		<guid>".$link->get_permalink()."</guid>\n";
		echo '		<description><![CDATA[';
		// In case of meta, only sends votes and karma
		// developed for alianzo.com
		echo '<p>'.$content.'</p>';
		echo '<p><img src="http://'. get_server_name() .$globals['base_url'].'backend/vote_com_img.php?id='. $link->id .'" alt="votes" width=200, height=16 /></p>';
		
		if ($link->status == 'published') {
			echo "<p>&#187;&nbsp;<a href='".htmlspecialchars($link->url)."'>"._('noticia original')."</a></p>";
		}
		echo "]]></description>\n";
		//echo '<wfw:comments>'.$link->comments().'</wfw:comments>';
		// echo "		<trackback:ping>".get_trackback($link->id)."</trackback:ping>\n";  // no standard
		//echo "<content:encoded><![CDATA[ ]]></content:encoded>\n";
		echo '<wfw:commentRss>http://'.get_server_name().$globals['base_url'].'comments_rss2.php?id='.$link->id.'</wfw:commentRss>';
		echo "	</item>\n\n";
	}
}

do_footer();

function do_header($title) {
	global $last_modified, $dblang, $home, $globals;

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

function check_redirect_to_feedburner($status) {
	global $globals; 

	if (!$globals['redirect_feedburner'] || preg_match('/feedburner/', htmlspecialchars($_SERVER['PHP_SELF'])) || preg_match('/feedburner/i', $_SERVER['HTTP_USER_AGENT'])) return;

	switch ($status) {
		case 'published':
			header("Location: http://feeds.feedburner.com/meneame/published");
			exit();
			break;
		case 'queued':
			header("Location: http://feeds.feedburner.com/meneame/queued");
			exit();
			break;
		case 'all':
			header("Location: http://feeds.feedburner.com/meneame/all");
			exit();
			break;
	}
	
}
?>
