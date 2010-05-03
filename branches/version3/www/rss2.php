<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'geo.php');
include(mnminclude.'ban.php');

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

// Compatibility with the old "search" query string
if($_REQUEST['search']) $_REQUEST['q'] = $_REQUEST['search'];

if(!empty($_REQUEST['time'])) {
	/////
	// Prepare for times
	/////
	if(!($time = check_integer('time')))
		die;
	$sql = "SELECT link_id, link_votes as votes FROM links WHERE ";	
	if ($time < 0 || $time > 86400*5) $time = 86400*2;
	$from = time()-$time;
	$sql .= "link_date > FROM_UNIXTIME($from) AND ";
	$sql .= "link_status = 'published' ORDER BY link_votes DESC LIMIT $rows";
	$last_modified = time();
	$title = _('Menéame: más votadas en') . ' ' . txt_time_diff($from);
} elseif (!empty($_REQUEST['favorites'])) {
	/////
	// RSS for users' favorites
	/////
	$user_id = guess_user_id($_REQUEST['favorites']);
	$sql = "SELECT link_id FROM links, favorites WHERE favorite_user_id=$user_id AND favorite_type='link' AND favorite_link_id=link_id ORDER BY favorite_date DESC limit $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(max(favorite_date)) from favorites where favorite_user_id=$user_id AND favorite_type='link'");
	$user_login = $db->get_var("select user_login from users where user_id=$user_id");
	$title = _('Menéame: favoritas de') . ' ' . $user_login;
	$globals['redirect_feedburner'] = false;
} elseif (!empty($_REQUEST['voted_by'])) {
	// RSS for voted links
	$user_id = guess_user_id($_REQUEST['voted_by']);
	if (! $user_id > 0) die;
	$sql = "SELECT vote_link_id FROM votes WHERE vote_type='links' and vote_user_id = $user_id and vote_value > 0 ORDER BY vote_date DESC limit $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(vote_date) FROM votes WHERE vote_type='links' and vote_user_id = $user_id and vote_value > 0 ORDER BY vote_date DESC limit 1");
	$user_login = $db->get_var("select user_login from users where user_id=$user_id");
	$title = _('Menéame: votadas por') . ' ' . $user_login;
	$globals['redirect_feedburner'] = false;
} elseif (!empty($_REQUEST['friends_of'])) {
	/////
	// RSS for users' friends
	/////
	$user_id = guess_user_id($_REQUEST['friends_of']);
	$sql = "SELECT link_id FROM links, friends WHERE friend_type='manual' and friend_from = $user_id and friend_to=link_author and friend_value > 0 and link_status in ('queued', 'published') ORDER BY link_date DESC limit $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(link_date) FROM links, friends WHERE friend_type='manual' and friend_from = $user_id and friend_to=link_author and friend_value > 0 and link_status in ('queued', 'published') ORDER BY link_date DESC limit 1");
	$user_login = $db->get_var("select user_login from users where user_id=$user_id");
	$title = _('Menéame: amigos de') . ' ' . $user_login;
	$globals['redirect_feedburner'] = false;
} elseif (!empty($_REQUEST['sent_by'])) {
	/////
	// RSS for users' sent links
	/////
	$user_id = guess_user_id($_REQUEST['sent_by']);
	$sql = "SELECT link_id FROM links WHERE link_author=$user_id and link_votes > 0 ORDER BY link_id DESC limit $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(max(link_date)) from links where link_author=$user_id and link_votes > 0");
	$user_login = $db->get_var("select user_login from users where user_id=$user_id");
	$title = _('Menéame: noticias de') . ' ' . $user_login;
	$globals['redirect_feedburner'] = false;
} else {
	/////
	// All the others
	/////
	// The link_status to search
	if(!empty($_REQUEST['status'])) {
		$status = $db->escape(clean_input_string(trim($_REQUEST['status'])));
	} else {
		// By default it searches on all
		if($_REQUEST['q']) {
			$status = 'all';
			include(mnminclude.'search.php');
			$search_ids = do_search(true);
			if ($search_ids['ids']) {
				$search = ' link_id in (';
				foreach ($search_ids['ids'] as $lid) {
					$search .= $lid . ',';
				}
				$search = preg_replace('/,$/', '', $search);
				$search .= ')';
			}
		} else {
			$status = 'published';
		}
	}
	
	switch ($status) {
		case 'published':
			$order_field = 'link_date';
			$link_date = 'date';
			$title = _('Menéame: publicadas');
			break;
		case 'queued':
			$title = _('Menéame: en cola');
			$order_field = 'link_date';
			$link_date = "date";
			$home = "/shakeit.php";
			// disable feedburner for queued
			$globals['redirect_feedburner'] = false;
			break;
		case 'all':
		case 'all_local':
		default:
			$title = _('Menéame: todas');
			$order_field = 'link_date';
			$link_date = "date";
			break;
	}
	/*****  WARNING
		this function is to redirect to feed burner
		comment it out
		You have been warned ******/

	if (!$_REQUEST['q'] && empty($_REQUEST['category']) && empty($_REQUEST['meta']) && empty($_REQUEST['personal'])) {
		if ($status == 'published') {
			$globals['main_published_rss'] = true;
		}
		check_redirect_to_feedburner($status);
	}
	
	/*****  END WARNING ******/
	
	
	
	if($status == 'all' || $status == 'all_local') {
		$from_where = "FROM links WHERE link_status in  ('published', 'queued') AND link_date > date_sub(now(), interval 7 day) ";
	} else {
		$from_where = "FROM links WHERE link_status='$status' AND link_date > date_sub(now(), interval 7 day) ";
	}

	// Check if it's search
	if($_REQUEST['q']) {
		if($search) {
			$from_where = "FROM links WHERE $search ";
		} else {
			$from_where .= "AND false"; // Force to return empty set
		}
		$title = _('Menéame') . ": " . htmlspecialchars(strip_tags($_REQUEST['q']));
	}
	

	if(($meta=check_integer('meta'))) {
		$cat_list = meta_get_categories_list($meta);
		if (!$cat_list) not_found();
		$from_where .= " AND link_category in ($cat_list)";
		$meta_name = $db->get_var("SELECT category_name FROM categories WHERE category_id = $meta AND category_parent=0");
		$title .= " -$meta_name-";
	} elseif(($cat=check_integer('category'))) {
		$from_where .= " AND link_category=$cat ";
		$category_name = $db->get_var("SELECT category_name FROM categories WHERE category_id = $cat AND category_lang='$dblang'");
		$title .= " -$category_name-";
	} elseif(($uid=check_integer('personal'))) {
		$categories = $db->get_col("SELECT pref_value FROM prefs WHERE pref_user_id = $uid and pref_key = 'category' ");
		$user_login = $db->get_var("select user_login from users where user_id=$uid");
		$title .= " -$user_login-";
		if ($categories) {
			$cats = implode(',', $categories);
			$from_where .= " AND link_category in ($cats) ";
		}
	}
	
	$order_by = " ORDER BY $order_field DESC ";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP($order_field) $from_where $order_by LIMIT 1");
	if ($if_modified > 0) {
		$from_where .= " AND $order_field > FROM_UNIXTIME($if_modified)";
	}
	$sql = "SELECT link_id $from_where $order_by LIMIT $rows";
}

do_header($title);

// Don't allow banned IPs o proxies
if(! check_ban($globals['user_ip'], 'ip', true) && ! check_ban_proxy() ) {
	$links = $db->get_col($sql);
} else {
	$links = false;
}

if ($links) {
	foreach($links as $link_id) {
		$link = Link::from_db($link_id);
		$category_name = $db->get_var("SELECT category_name FROM categories WHERE category_id = $link->category AND category_lang='$dblang'");
		$content = text_to_html(htmlentities2unicodeentities($link->content));
		$permalink = $link->get_short_permalink();
		/*
		if (isset($_REQUEST['local']) || $globals['bot']) {
			$permalink = $link->get_permalink();
		} else {
			$permalink = $link->get_short_permalink();
		}
		*/
		echo "	<item>\n";

		// Meneame own namespace
		echo "		<meneame:link_id>$link->id</meneame:link_id>\n";
		echo "		<meneame:user>$link->username</meneame:user>\n";
		echo "		<meneame:votes>".intval($link->votes+$link->anonymous)."</meneame:votes>\n";
		echo "		<meneame:negatives>$link->negatives</meneame:negatives>\n";
		echo "		<meneame:karma>".intval($link->karma)."</meneame:karma>\n";
		echo "		<meneame:comments>$link->comments</meneame:comments>\n";
		echo "		<meneame:url>".htmlspecialchars($link->url)."</meneame:url>\n";

		// Title must not carry htmlentities
		echo "		<title>".htmlentities2unicodeentities($link->title)."</title>\n";
		echo "		<link>$permalink</link>\n";
		echo "		<comments>$permalink</comments>\n";
		if (!empty($link_date))
			echo "		<pubDate>".date("r", $link->$link_date)."</pubDate>\n";
		else echo "      <pubDate>".date("r", $link->date)."</pubDate>\n";
		echo "		<dc:creator>$link->username</dc:creator>\n";
		echo "		<category><![CDATA[$category_name]]></category>\n";
		// Add tags as categories
		if (!empty($link->tags)) {
			$tags_array = explode(",", $link->tags);
			foreach ($tags_array as $tag_item) {
				$tag_item=trim($tag_item);
				echo "		<category><![CDATA[".$tag_item."]]></category>\n";
			}
		}
		echo "		<guid>$permalink</guid>\n";
		// Insert GEO
		if (($latlng = geo_latlng('link', $link->id))) {
			echo "		<georss:point>$latlng->lat $latlng->lng</georss:point>\n";
		}
		echo '		<description><![CDATA[';
		// In case of meta, only sends votes and karma
		// developed for alianzo.com
		if (($thumb = $link->has_thumb())) {
			echo "<img src='$thumb' width='$link->thumb_x' height='$link->thumb_y' alt='' class='thumbnail' style='float:right;margin-left: 3px' align='right' hspace='3'/>";
		}
		echo '<p>'.$content.'</p>';
		echo '<p><strong>' . _('etiquetas') . '</strong>: ' . preg_replace('/,([^ ])/', ', $1', $link->tags) . '</p>';

		if (time() - $link->date < 172800) { // Only add the votes/comments image if the link has less than two days
			echo '<p><a href="'.$permalink.'"><img src="http://'. get_server_name() .$globals['base_url'].'backend/vote_com_img.php?id='. $link->id .'" alt="votes" width="200" height="16"/></a></p>';
		}
		
		if ($link->status != 'published') $rel = 'rel="nofollow"';
		else $rel = '';
		echo "<p>&#187;&nbsp;<a href='".htmlspecialchars($link->url)."' $rel>"._('noticia original')."</a></p>";
		echo "]]></description>\n";
		if ($thumb) {
			echo '		<media:thumbnail url="'.$thumb."\" width='$link->thumb_x' height='$link->thumb_y' />\n";
		}
		//echo '<wfw:comments>'.$link->comments().'</wfw:comments>';
		// echo "		<trackback:ping>".get_trackback($link->id)."</trackback:ping>\n";  // no standard
		//echo "<content:encoded><![CDATA[ ]]></content:encoded>\n";
		echo '		<wfw:commentRss>http://'.get_server_name().$globals['base_url'].'comments_rss2.php?id='.$link->id.'</wfw:commentRss>';
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
	if ($last_modified <= $if_modified) {
		header('HTTP/1.1 304 Not Modified');
		exit();
	}

	header('Last-Modified: ' .  gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
	header('Content-type: text/xml; charset=UTF-8', true);
	echo '<?xml version="1.0" encoding="UTF-8"?'.'>' . "\n";
	echo '<rss version="2.0" '."\n";
	echo '	xmlns:atom="http://www.w3.org/2005/Atom"'."\n";
	echo '	xmlns:content="http://purl.org/rss/1.0/modules/content/"'."\n";
	echo '	xmlns:wfw="http://wellformedweb.org/CommentAPI/"'."\n";
	echo '	xmlns:dc="http://purl.org/dc/elements/1.1/"'."\n";
	echo '	xmlns:georss="http://www.georss.org/georss"'."\n";
	echo '	xmlns:media="http://search.yahoo.com/mrss/"'."\n";
	echo '	xmlns:meneame="http://meneame.net/faq-es.php"'."\n";
	echo ' >'. "\n";
	echo '<channel>'."\n";
	echo'	<title>'.$title.'</title>'."\n";
	echo '	<atom:link href="http://'.get_server_name().htmlentities(clean_input_url($_SERVER['REQUEST_URI'])).'" rel="self" type="application/rss+xml" />'."\n";
	echo'	<link>http://'.get_server_name().$home.'</link>'."\n";
	echo"	<image><title>$title</title><link>http://".get_server_name().$home."</link><url>http://".get_static_server_name().$globals['base_url']."img/mnm/eli-rss.png</url></image>\n";
	echo'	<description>'._('Sitio colaborativo de publicación y comunicación entre blogs').'</description>'."\n";
	echo'	<pubDate>'.date("r", $last_modified).'</pubDate>'."\n";
	echo'	<generator>http://blog.meneame.net/</generator>'."\n";
	echo'	<language>'.$dblang.'</language>'."\n";
	if ($globals['pubsub'] && $globals['main_published_rss']) {
		echo '	<atom:link rel="hub" href="'.$globals['pubsub'].'"/>'."\n";
	}
}

function do_footer() {
	echo "</channel>\n</rss>\n";
}

function check_redirect_to_feedburner($status) {
	global $globals;

	if (isset($_REQUEST['local']) || $globals['bot'] || !$globals['redirect_feedburner'] || preg_match('/feedburner/', htmlspecialchars($_SERVER['PHP_SELF'])) || preg_match('/feedburner/i', $_SERVER['HTTP_USER_AGENT']) ) return;
	/*|| preg_match('/technoratibot/i', $_SERVER['HTTP_USER_AGENT']) */

	switch ($status) {
		case 'published':
			//header("Location: http://feeds.feedburner.com/meneame/published");
			header("Location: http://feedproxy.google.com/MeneamePublicadas");
			exit();
			break;
		case 'queued':
			//header("Location: http://feeds.feedburner.com/meneame/queued");
			header("Location: http://feedproxy.google.com/MeneameEnCola");
			exit();
			break;
		case 'all':
			//header("Location: http://feeds.feedburner.com/meneame/all");
			header("Location: http://feedproxy.google.com/MeneameEnCola");
			exit();
			break;
	}
	
}
?>
