<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


// Use the alternate server for api, if it exists
$globals['alternate_db_server'] = 'api';
include('config.php');

include(mnminclude.'geo.php');

include_once(mnminclude.'ban.php');
if (check_ban_proxy()) {
	die;
}

if(!empty($_REQUEST['rows'])) {
	$rows = min(2000, intval($_REQUEST['rows']));
} else {
	$rows = 50;
}

// Bug in FeedBurner, it needs all items
if (preg_match('/feedburner/i', $_SERVER['HTTP_USER_AGENT'])) {
	$if_modified = 0;
} else {
	$if_modified = get_if_modified();
}

// Compatibility with the old "search" query string
if($_REQUEST['search']) $_REQUEST['q'] = $_REQUEST['search'];

$site_id = SitesMgr::my_id();
$site_info = SitesMgr::get_info();

if ($site_info->sub && $site_info->owner > 0) {
	$globals['site_name'] = $site_info->name;
}


if(!empty($_REQUEST['time'])) {
	/////
	// Prepare for times
	/////
	if(!($time = check_integer('time')))
		die;
	$sql = "SELECT link_id, link_votes as votes FROM links, sub_statuses WHERE id = $site_id AND link_id = link AND ";
	if ($time < 0 || $time > 86400*5) $time = 86400*2;
	$from = time()-$time;
	$sql .= "date > FROM_UNIXTIME($from) AND ";
	$sql .= "status = 'published' ORDER BY link_votes DESC LIMIT $rows";
	$last_modified = time();
	$title = $globals['site_name'].': '.sprintf(_('más votadas en %s'), txt_time_diff($from));
} elseif (!empty($_REQUEST['favorites'])) {
	/////
	// RSS for users' favorites
	/////
	$user_id = guess_user_id($_REQUEST['favorites']);
	$sql = "SELECT link_id FROM links, favorites WHERE favorite_user_id=$user_id AND favorite_type='link' AND favorite_link_id=link_id ORDER BY favorite_date DESC limit $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(max(favorite_date)) from favorites where favorite_user_id=$user_id AND favorite_type='link'");
	$user_login = $db->get_var("select user_login from users where user_id=$user_id");
	$title = $globals['site_name'].': '.sprintf(_('favoritas de %s'), $user_login);
	$globals['redirect_feedburner'] = false;
} elseif (!empty($_REQUEST['voted_by'])) {
	// RSS for voted links
	$user_id = guess_user_id($_REQUEST['voted_by']);
	if (! $user_id > 0) die;
	$sql = "SELECT vote_link_id FROM votes WHERE vote_type='links' and vote_user_id = $user_id and vote_value > 0 ORDER BY vote_date DESC limit $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(vote_date) FROM votes WHERE vote_type='links' and vote_user_id = $user_id and vote_value > 0 ORDER BY vote_date DESC limit 1");
	$user_login = $db->get_var("select user_login from users where user_id=$user_id");
	$title = $globals['site_name'].': '.sprintf(_('votadas por %s'), $user_login);
	$globals['redirect_feedburner'] = false;
} elseif (!empty($_REQUEST['friends_of'])) {
	/////
	// RSS for users' friends
	/////
	$user_id = guess_user_id($_REQUEST['friends_of']);
	$sql = "SELECT link_id FROM links, friends WHERE friend_type='manual' and friend_from = $user_id and friend_to=link_author and friend_value > 0 and link_status in ('queued', 'published') ORDER BY link_date DESC limit $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(link_date) FROM links, friends WHERE friend_type='manual' and friend_from = $user_id and friend_to=link_author and friend_value > 0 and link_status in ('queued', 'published') ORDER BY link_date DESC limit 1");
	$user_login = $db->get_var("select user_login from users where user_id=$user_id");
	$title = $globals['site_name'].': '.sprintf(_('amigos de %s'), $user_login);
	$globals['redirect_feedburner'] = false;
} elseif (!empty($_REQUEST['sent_by'])) {
	/////
	// RSS for users' sent links
	/////
	$user_id = guess_user_id($_REQUEST['sent_by']);
	$sql = "SELECT link_id FROM links WHERE link_author=$user_id and link_votes > 0 ORDER BY link_id DESC limit $rows";
	$last_modified = $db->get_var("SELECT UNIX_TIMESTAMP(max(link_date)) from links where link_author=$user_id and link_votes > 0");
	$user_login = $db->get_var("select user_login from users where user_id=$user_id");
	$title = $globals['site_name'].': '.sprintf(_('noticias de %s'), $user_login);
	$globals['redirect_feedburner'] = false;
} elseif (isset($_REQUEST['active'])) {
	$globals['redirect_feedburner'] = false;
	$title = $globals['site_name'].': '._('más activas');
	$top = new Annotation('top-actives-'.$globals['site_shortname']);
	/*
	if ($top->read()) {
		$sql = "SELECT link_id FROM links WHERE link_id in ($top->text)";
		syslog(LOG_INFO, $sql);
		$last_modified = $top->time;
	}
	*/
	if ($top->read()) {
		$links = explode(',',$top->text);
		$last_modified = $top->time;
	}
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
				$search = ' link_id in (' . implode(',', $search_ids['ids']) . ')';
			}
		} else {
			$status = 'published';
		}
	}

	switch ($status) {
		case 'published':
			$order_field = 'date';
			$link_date = 'date';
			$title = $globals['site_name'].': '._('publicadas');
			break;
		case 'queued':
			$title = $globals['site_name'].': '._('en cola');
			$order_field = 'date';
			$link_date = "date";
			$home = "/queue";
			// disable feedburner for queued
			$globals['redirect_feedburner'] = false;
			break;
		case 'all':
		case 'all_local':
		default:
			$title = $globals['site_name'].': '._('todas');
			$order_field = 'date';
			$link_date = "date";
			break;
	}
	/*****  WARNING
		this function is to redirect to feed burner
		comment it out
		You have been warned ******/

	if (!$_REQUEST['q'] && empty($_REQUEST['meta']) && empty($_REQUEST['subs'])) {
		if ($status == 'published') {
			$globals['main_published_rss'] = true;
		}
		check_redirect_to_feedburner($status);
	}

	/*****  END WARNING ******/



	$from_where = '';
	if ($_REQUEST['q']) {
		$order_field = 'link_date'; // Because sub_statuses is not used
		if($search) {
			$from_where = "FROM links WHERE $search ";
		} else {
			$from_where = "FROM links WHERE false "; // Force to return empty set
		}
		$title = $globals['site_name'] . ": " . htmlspecialchars(strip_tags($_REQUEST['q']));
	} elseif ($status == 'all' || $status == 'all_local') {
		$from_where = "FROM links, sub_statuses WHERE id = $site_id AND status in ('published', 'queued') AND date > date_sub(now(), interval 7 day) AND link_id = link";
 	} elseif (($uid=check_integer('subs'))) {
		$subs = $db->get_col("SELECT pref_value FROM prefs WHERE pref_user_id = $uid and pref_key = 'sub_follow' order by pref_value");
		$user_login = $db->get_var("select user_login from users where user_id=$uid");
		$title .= " -$user_login-";
		if ($subs) {
			$subs = implode(',', $subs);
			$from_where = "FROM sub_statuses, links WHERE sub_statuses.id in ($subs) AND status='$status' AND date > date_sub(now(), interval 7 day) AND link_id = link";
		}
	}
	if (empty($from_where)) {
		$from_where = "FROM sub_statuses, links WHERE id = $site_id AND status='$status' AND date > date_sub(now(), interval 7 day) AND link_id = link";
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
if (! empty($sql)) {
	$links = $db->get_col($sql);
}

if ($links) {
	foreach($links as $link_id) {
		$link = Link::from_db($link_id);
		if (!$link) continue;
		$permalink = $link->get_permalink();
		echo "	<item>\n";

		// Meneame own namespace
		echo "		<meneame:link_id>$link->id</meneame:link_id>\n";
		echo "		<meneame:sub>$link->sub_name</meneame:sub>\n";
		echo "		<meneame:status>$link->status</meneame:status>\n";
		echo "		<meneame:user>$link->username</meneame:user>\n";
		echo "		<meneame:clicks>".$link->clicks."</meneame:clicks>\n";
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
		echo "		<category><![CDATA[$link->sub_name]]></category>\n";
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
		if (isset($_REQUEST['nohtml'])) {
			$content = htmlentities2unicodeentities(strip_tags($link->content));
			echo "		<description>$content</description>\n";
		} else {
			$content = htmlentities2unicodeentities($link->to_html($link->content));
			echo '		<description><![CDATA[';
			// In case of meta, only sends votes and karma
			// developed for alianzo.com
			if (($thumb = $link->has_thumb())) {
				echo "<img src='$thumb' width='$link->thumb_x' height='$link->thumb_y' alt='' class='thumbnail' style='float:right;margin-left: 3px' align='right' hspace='3'/>";
			}
			echo '<p>'.$content.'</p>';
			echo '<p><strong>' . _('etiquetas') . '</strong>: ' . preg_replace('/,([^ ])/', ', $1', $link->tags) . '</p>';

			if ($link->status != 'published') $rel = 'rel="nofollow"';
			else $rel = '';

			echo '<p>&#187;&nbsp;<a href="'.htmlspecialchars($link->url).'"';
			if ($globals['click_counter'] > 0) {
				echo ' onmousedown="this.href=\'http://'.get_server_name().$globals['base_url'].'go.php?id='.$link->id.'\'; return true"';
			}
			echo " $rel>"._('noticia original')."</a> (".parse_url($link->url, 1).")</p>";

			echo "]]></description>\n";
		}
		if ($thumb) {
			echo '		<media:thumbnail url="'.$thumb."\" width='$link->thumb_x' height='$link->thumb_y' />\n";
		}
		//echo '<wfw:comments>'.$link->comments().'</wfw:comments>';
		// echo "		<trackback:ping>".get_trackback($link->id)."</trackback:ping>\n";  // no standard
		//echo "<content:encoded><![CDATA[ ]]></content:encoded>\n";
		echo '		<wfw:commentRss>http://'.get_server_name().$globals['base_url'].'comments_rss?id='.$link->id.'</wfw:commentRss>';
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
	echo '	<atom:link href="http://'.get_server_name().__(clean_input_url($_SERVER['REQUEST_URI'])).'" rel="self" type="application/rss+xml" />'."\n";
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

	$regex = '/'.$globals['rss_redirect_user_agent'].'|pubsub|meneame|burner/i';

	if (SitesMgr::my_id() > 1 || isset($_REQUEST['local']) || isset($_REQUEST['nohtml']) || $globals['bot'] || !$globals['rss_redirect_user_agent'] || preg_match($regex, htmlspecialchars($_SERVER['PHP_SELF'])) || preg_match($regex, $_SERVER['HTTP_USER_AGENT']) ) return;
	/*|| preg_match('/technoratibot/i', $_SERVER['HTTP_USER_AGENT']) */

	if (!empty($globals['rss_redirect_'.$status])) {
			header('Location: ' . $globals['rss_redirect_'.$status]);
			exit();
	}

}
?>
