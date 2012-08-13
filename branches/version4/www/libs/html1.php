<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


@include_once mnminclude.'ads-credits-functions.php';

// Warning, it redirects to the content of the variable
if (!empty($globals['lounge'])) {
	header('Location: http://'.get_server_name().$globals['base_url'].$globals['lounge']);
	die;
}

$globals['start_time'] = microtime(true);

$globals['extra_js'] = Array();
$globals['extra_css'] = Array();
$globals['post_js'] = Array();


class MenuOption{
	// Small helper class to store links' information
	function __construct($text, $url, $active = false, $title = '') {
		$this->text = $text;
		$this->url = $url;
		$this->title = $title;
		if ($active && $active == $this->text) {
			$this->selected = true;
		} else {
			$this->selected = $false;
		}
	}
}


function do_tabs($tab_name, $tab_selected = false, $extra_tab = false) {
	/* Not used any more */
}


function do_header($title, $id='home', $options = false) {
	global $current_user, $dblang, $globals, $db;

	check_auth_page();
	header('Content-Type: text/html; charset=utf-8');
	header('X-Frame-Options: SAMEORIGIN');
	http_cache();


	if(!empty($globals['link_id'])) {
		// Pingback autodiscovery
		// http://www.hixie.ch/specs/pingback/pingback
		header('X-Pingback: http://' . get_server_name() . $globals['base_url'] . 'xmlrpc.php');
	}

	$globals['security_key'] = get_security_key();
	setcookie('k', $globals['security_key'], 0, $globals['base_url']);

	if (!empty($_REQUEST['q'])) $globals['q'] = $_REQUEST['q'];

/*
	if ($globals['greetings']) $greeting = array_rand($globals['greetings'], 1);
	else $greeting = _('hola');
*/

	if (! is_array($options)) {
		$left_options = array();
		$left_options[] = new MenuOption(_('enviar historia'), $globals['base_url'].'submit.php', $id, _('enviar nueva historia'));
		$left_options[] = new MenuOption(_('portada'), $globals['base_url'], $id, _('página principal'));
		$left_options[] = new MenuOption(_('pendientes'), $globals['base_url'].'shakeit.php', $id, _('menear noticias pendientes'));
		$left_options[] = new MenuOption(_('populares'), $globals['base_url'].'topstories.php', $id, _('historias más votadas'));
		$left_options[] = new MenuOption(_('más visitadas'), $globals['base_url'].'topclicked.php', $id, _('historias más visitadas/leídas'));
		$left_options[] = new MenuOption(_('destacadas'), $globals['base_url'].'topactive.php', $id, _('historias más activas'));

		$right_options = array();
		$right_options[] = new MenuOption(_('fisgona'), $globals['base_url'].'sneak.php', $id, _('visualizador en tiempo real'));
		$right_options[] = new MenuOption(_('nótame'), post_get_base_url(), $id, _('leer o escribir notas y mensajes privados'));
		$right_options[] = new MenuOption(_('galería'), 'javascript:fancybox_gallery(\'all\');', false, _('las imágenes subidas por los usuarios'));
	} else {
		$left_options = $options;
		$right_options = array();
		//$right_options[] = new MenuOption(_('portada'), $globals['base_url'], '', _('página principal'));
		$right_options[] = new MenuOption(_('pendientes'), $globals['base_url'].'shakeit.php', '', _('menear noticias pendientes'));

		$right_options[] = new MenuOption(_('fisgona'), $globals['base_url'].'sneak.php', $id, _('visualizador en tiempo real'));
		$right_options[] = new MenuOption(_('nótame'), post_get_base_url(), $id, _('leer o escribir notas y mensajes privados'));
		$right_options[] = new MenuOption(_('galería'), 'javascript:fancybox_gallery(\'all\');', false, _('las imágenes subidas por los usuarios'));
	}

	$sites = $db->get_results("select * from subs where visible order by id asc");
	$this_site = SitesMgr::get_info();

	$vars = compact('title', 'greeting', 'id', 'left_options', 'right_options', 'sites', 'this_site');
	return Haanga::Load('header.html', $vars);
}


function do_js_from_array($array) {
	global $globals;

	foreach ($array as $js) {
		if (preg_match('/^http|^\//', $js)) {
			echo '<script src="'.$js.'" type="text/javascript"></script>' . "\n";
		} elseif (preg_match('/\.js$/', $js))  {
			echo '<script src="'.$globals['base_static'].'js/'.$js.'" type="text/javascript"></script>' . "\n";
		} else {
			echo '<script src="'.$globals['base_url'].'js/'.$js.'" type="text/javascript"></script>' . "\n";
		}
	}
}

function do_footer($credits = true) {
	return Haanga::Load('footer.html');
}

function do_footer_menu() {
	return Haanga::Load('footer_menu.html');
}

function do_rss_box($search_rss = 'rss2.php') {
	global $globals, $current_user;

	if ($globals['mobile']) return;

	return Haanga::Load('rss_box.html', compact('search_rss'));
}

function force_authentication() {
	global $current_user, $globals;

	if(!$current_user->authenticated) {
		header('Location: '.$globals['base_url'].'login.php?return='.$globals['uri']);
		die;
	}
	return true;
}

function mobile_redirect() {
	global $globals;

	if ($globals['mobile'] && empty($_COOKIE['nomobile']) && ! preg_match('/(pad|tablet|wii|tv)\W/i', $_SERVER['HTTP_USER_AGENT']) &&
			$globals['url_shortener_mobile_to'] &&
			(! $_SERVER['HTTP_REFERER'] ||
			// Check if the user comes from our own domain
			// If so, don't redirect her
			! preg_match('/^https*:\/\/.*?'.preg_quote(preg_replace('/.+?\.(.+?\..+?)$/', "$1", get_server_name())).'/i', $_SERVER['HTTP_REFERER']))
		) {
		header('Location: http://'.$globals['url_shortener_mobile_to'].$_SERVER['REQUEST_URI']);
		die;
	}
}

function do_pages_reverse($total, $page_size=25, $margin = true) {
	global $db;

	if ($total > 0 && $total < $page_size) return;

	$index_limit = 5;

	$query=preg_replace('/page=[0-9]+/', '', $_SERVER['QUERY_STRING']);
	$query=preg_replace('/^&*(.*)&*$/', "$1", $query);
	if(!empty($query)) {
		$query = htmlspecialchars($query);
		$query = "&amp;$query";
	}

	$total_pages=ceil($total/$page_size);
	$current = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : $total_pages;
	$start=max($current-intval($index_limit/2), 1);
	$end=$start+$index_limit-1;

	if ($margin) {
		echo '<div class="pages margin">';
	} else {
		echo '<div class="pages">';
	}

	if($total < 0 || $current<$total_pages) {
		$i = $current+1;
		if ($i > 10) $nofollow = ' rel="nofollow"'; else $nofollow = '';
		echo '<a href="?page='.$i.$query.'"'.$nofollow.' rel="next">'._('siguiente').' &#171;</a>';
	} else {
		echo '<span class="nextprev">&#171; '._('siguiente'). '</span>';
	}

	if ($total_pages > 0) {

		if($total_pages>$end) {
			$i = $total_pages;
			if ($i > 10) $nofollow = ' rel="nofollow"'; else $nofollow = '';
			echo '<a href="?page='.$i.$query.'" title="'._('ir a página')." $i".'"'.$nofollow.'>'.$i.'</a>';
			echo '<span>...</span>';
		}

		//for ($i=$start;$i<=$end && $i<= $total_pages;$i++) {
		for ($i=min($end, $total_pages) ; $i >= $start;$i--) {
			if($i==$current) {
				echo '<span class="current">'.$i.'</span>';
			} else {
				if ($i > 10) $nofollow = ' rel="nofollow"'; else $nofollow = '';
				echo '<a href="?page='.$i.$query.'" title="'._('ir a página')." $i".'"'.$nofollow.'>'.$i.'</a>';
			}
		}

		if($start>1) {
			$i = 1;
			echo '<span>...</span>';
			echo '<a href="?page='.$i.$query.'" title="'._('ir a página')." $i".'">'.$i.'</a>';
		}

	} else {
		echo '<span class="current">'.$current.'</span>';
		if($current>2) {
			echo '<span>...</span>';
			echo '<a href="?page=1" title="'._('ir a página')." 1".'">1</a>';
		}
	}

	if($current==1) {
		echo '<span class="nextprev">&#187; '._('anterior'). '</span>';
	} else {
		$i = $current-1;
		if ($i > 10) $nofollow = ' rel="nofollow"'; else $nofollow = '';
		echo '<a href="?page='.$i.$query.'"'.$nofollow.' rel="prev">&#187; '._('anterior').'</a>';
	}

	echo '</div>';
}

function do_pages($total, $page_size=25, $margin = true) {
	global $db;

	if ($total > 0 && $total < $page_size) return;

	$index_limit = 5;

	$query=preg_replace('/page=[0-9]+/', '', $_SERVER['QUERY_STRING']);
	$query=preg_replace('/^&*(.*)&*$/', "$1", $query);
	if(!empty($query)) {
		$query = htmlspecialchars($query);
		$query = "&amp;$query";
	}

	$current = get_current_page();
	$total_pages=ceil($total/$page_size);
	$start=max($current-intval($index_limit/2), 1);
	$end=$start+$index_limit-1;

	if ($margin) {
		echo '<div class="pages margin">';
	} else {
		echo '<div class="pages">';
	}

	if($current==1) {
		echo '<span class="nextprev">&#171; '._('anterior'). '</span>';
	} else {
		$i = $current-1;
		if ($i > 10) $nofollow = ' rel="nofollow"'; else $nofollow = '';
		echo '<a href="?page='.$i.$query.'"'.$nofollow.' rel="prev">&#171; '._('anterior').'</a>';
	}

	if ($total_pages > 0) {

		if($start>1) {
			$i = 1;
			echo '<a href="?page='.$i.$query.'" title="'._('ir a página')." $i".'">'.$i.'</a>';
			echo '<span>...</span>';
		}

		for ($i=$start;$i<=$end && $i<= $total_pages;$i++) {
			if($i==$current) {
				echo '<span class="current">'.$i.'</span>';
			} else {
				if ($i > 10) $nofollow = ' rel="nofollow"'; else $nofollow = '';
				echo '<a href="?page='.$i.$query.'" title="'._('ir a página')." $i".'"'.$nofollow.'>'.$i.'</a>';
			}
		}

		if($total_pages>$end) {
			$i = $total_pages;
			echo '<span>...</span>';
			if ($i > 10) $nofollow = ' rel="nofollow"'; else $nofollow = '';
			echo '<a href="?page='.$i.$query.'" title="'._('ir a página')." $i".'"'.$nofollow.'>'.$i.'</a>';
		}
	} else {
		if($current>2) {
			echo '<a href="?page=1" title="'._('ir a página')." 1".'">1</a>';
			echo '<span>...</span>';
		}
		echo '<span class="current">'.$current.'</span>';
	}

	if($total < 0 || $current<$total_pages) {
		$i = $current+1;
		if ($i > 10) $nofollow = ' rel="nofollow"'; else $nofollow = '';
		echo '<a href="?page='.$i.$query.'"'.$nofollow.' rel="next">'._('siguiente').' &#187;</a>';
	} else {
		echo '<span class="nextprev">&#187; '._('siguiente'). '</span>';
	}
	echo '</div>';

}

//Used in editlink.php and submit.php
function print_categories_form($selected = 0) {
	global $db, $dblang, $globals;

	$metas = SitesMgr::get_metas();

	foreach ($metas as &$meta) {
		$meta->categories = SitesMgr::get_categories($meta->id);
	}
	unset($meta);

	$vars = compact('selected', 'metas');
	return Haanga::Load('form_categories.html', $vars);
}

function do_vertical_tags($what=false) {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	if (!empty($what)) {
		$status = '= "'.$what. '"';
	} else {
		$status = "!= 'discarded'";
	}
	if(!empty($globals['meta_categories'])) {
		$meta_cond = 'and link in ('.$globals['meta_categories'].')';
	}

	$cache_key = 'tags_'.$globals['site_shortname'].$globals['v'].$status.$meta_cond;
	if(memcache_mprint($cache_key)) return;

	echo '<!-- Calculating '.__FUNCTION__.' -->';

	$min_pts = 8;
	$max_pts = 22;

	// Delete old tags, they are not used anywhere else
	$db->query("delete from tags where tag_lang = '$dblang' and tag_date < date_sub(now(), interval 8 day)");

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 172800); // 48 hours (edit! 2zero)
	$from_where = "FROM tags, links, sub_statuses WHERE id = ".SitesMgr::my_id()." AND link_id = link AND tag_lang='$dblang' and tag_date > '$min_date' and link_id = tag_link_id and status $status $meta_cond GROUP BY tag_words";
	$max = 3;

	$res = $db->get_results("select lower(tag_words) as word, count(*) as count $from_where order by count desc limit 20");
	if ($res) {
		$url = $globals['base_url'].'cloud.php';
		$title = _('etiquetas');
		$content = '';
		foreach ($res as $item) {
			$words[$item->word] = $item->count;
			if ($item->count > $max) $max = $item->count;
		}
		$coef = ($max_pts - $min_pts)/($max-1);
		ksort($words);
		$max_count = 0;
		foreach ($words as $word => $count) {
			if ($count > $max_count) $max_count = $count;
			$size = round($min_pts + ($count-1)*$coef, 1);
			$op = round(0.4 + 0.6*$count/$max, 2);
			$content .= '<a style="font-size: '.$size.'pt;opacity:'.$op.'" href="';
			if ($globals['base_search_url']) {
				$content .= $globals['base_url'].$globals['base_search_url'].'tag:';
			} else {
				$content .= $globals['base_url'].'search.php?p=tags&amp;q=';
			}
			$content .= urlencode($word).'">'.$word.'</a>  ';
		}
		if ($max_count > 2) {
			$vars = compact('content', 'title', 'url');
			$output = Haanga::Load('tags_sidebox.html', $vars, true);
			echo $output;
		} else {
			$output = ' ';
		}
		memcache_madd($cache_key, $output, 900);
	}
}

function do_categories_cloud($what=false, $hours = 48) {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$cache_key = 'categories_cloud_'.$globals['site_shortname'].$globals['v'].$what;
	if(memcache_mprint($cache_key)) return;
	echo '<!-- Calculating '.__FUNCTION__.' -->';

	if (!empty($what)) {
		$status = '= "'.$what. '"';
	} else {
		$status = "!= 'discarded'";
	}


	$min_pts = 8;
	$max_pts = 22;

	$min_date = date("Y-m-d H:i:00", $globals['now'] - $hours*3600);
	$from_where = "from categories, links, sub_statuses where id = ".SitesMgr::my_id()." AND link_id = link AND link_status $status and date > '$min_date' and category = category_id group by category_name";
	$max = 0;


	$res = $db->get_results("select count(*) as count, lower(category_name) as category_name, category_id $from_where order by count desc limit 10");

	if ($res) {
		if ($what == 'queued') $page = $globals['base_url'].'shakeit.php?category=';
		else  $page = $globals['base_url'].'?category=';

		$title = _('categorías populares');

		$counts = array();
		$names = array();

		foreach ($res as $item) {
			if ($item->count > 1) {
				if ($item->count > $max) $max = $item->count;
				$counts[$item->category_id] = $item->count;
				$names[$item->category_name] = $item->category_id;
			}
		}
		ksort($names);
		$coef = (($max - 1) > 0)?(($max_pts - $min_pts)/($max-1)):0;

		foreach ($names as $name => $id) {
			$count = $counts[$id];
			$size = round($min_pts + ($count-1)*$coef, 1);
			$op = round(0.3 + 0.7*$count/$max, 2);
			$content .= '<a style="font-size: '.$size.'pt;opacity:'.$op.'" href="'.$page.$id.'">'.$name.'</a> ';
		}
		$vars = compact('content', 'title', 'url');
		$output = Haanga::Load('tags_sidebox.html', $vars, true);
		echo $output;
		memcache_madd($cache_key, $output, 600);
	}
}

function do_best_sites() {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$output = '';

	$key = 'best_sites_'.$globals['site_shortname'].$globals['v'].'_'.$globals['meta_current'];
	if(memcache_mprint($key)) return;
	echo '<!-- Calculating '.__FUNCTION__.' -->';

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 172800); // about  48 hours
	// The order is not exactly the votes counts
	// but a time-decreasing function applied to the number of votes
	$res = $db->get_results("select sum(link_votes-link_negatives*2)*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.8/172800) as coef, sum(link_votes-link_negatives*2) as total, blog_url from links, blogs, sub_statuses where id = ".SitesMgr::my_id()." AND link_id = link AND date > '$min_date' and status='published' and link_blog = blog_id group by link_blog order by coef desc limit 10;
");
	if ($res) {
		$output = Haanga::Load("best_sites_posts.html", compact('res'), TRUE);
		echo $output;
		memcache_madd($key, $output, 300);
	}
}

function do_best_comments() {
	global $db, $globals, $dblang;

	if ($globals['mobile'] || $globals['bot']) return;

	$foo = new Comment();
	$output = '';

	$key = 'best_comments_'.$globals['site_shortname'].$globals['v'];
	if(memcache_mprint($key)) return;
	echo '<!-- Calculating '.__FUNCTION__.' -->';

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 50000); // about 12 hours
	$link_min_date = date("Y-m-d H:i:00", $globals['now'] - 86400*2); // 48 hours
	$now = intval($globals['now']/60) * 60;
	// The order is not exactly the comment_karma
	// but a time-decreasing function applied to the number of votes
	$res = $db->get_results("select comment_id, comment_order, user_id, user_login, user_avatar, link_id, link_uri, link_title, link_comments, comment_karma*(1-($now-unix_timestamp(comment_date))*0.7/43000) as value, link_negatives/link_votes as rel from comments, links, users, sub_statuses where id = ".SitesMgr::my_id()." AND status in ('published', 'queued') AND link_id = link AND date > '$link_min_date' and comment_date > '$min_date' and LENGTH(comment_content) > 32 and link_negatives/link_votes < 0.5  and comment_karma > 50 and comment_link_id = link and comment_user_id = user_id order by value desc limit 10");
	if ($res) {
		$objects = array();
		$title = _('mejores comentarios');
		$url = $globals['base_url'].'topcomments.php';
		foreach ($res as $comment) {
			$obj = new stdClass();
			$obj->id = $foo->id = $comment->comment_id;
			$obj->link = $foo->get_relative_individual_permalink();
			$obj->user_id = $comment->user_id;
			$obj->avatar = $comment->user_avatar;
			$obj->title = $comment->link_title;
			$obj->username = $comment->user_login;
			$obj->tooltip = 'c';
			$objects[] = $obj;
		}
		$vars = compact('objects', 'title', 'url');
		$output = Haanga::Load('best_comments_posts.html', $vars, true);
		echo $output;
		memcache_madd($key, $output, 300);
	}
}

function do_best_story_comments($link) {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$do_cache = false;
	$output = '';

	if ($link->comments > 30 && $globals['now'] - $link->date < 86400*4) {
		$do_cache = true;
		$sql_cache = 'SQL_NO_CACHE';
	} else {
		$sql_cache = 'SQL_CACHE';
	}

	if($do_cache) {
		$key = 'best_story_comments_'.$globals['v'].$link->id;
		if(memcache_mprint($key)) return;
	}
	echo '<!-- Calculating '.__FUNCTION__.' -->';

	$limit = min(15, intval($link->comments/5));
	$res = $db->get_results("select $sql_cache comment_id, comment_order, user_id, user_login, user_avatar, comment_content as content from comments, users  where comment_link_id = $link->id and comment_karma > 30 and comment_user_id = user_id order by comment_karma desc limit $limit");
	if ($res) {
		$objects = array();
		$title = _('mejores comentarios');
		$url = $link->get_relative_permalink().'/best-comments';
		foreach ($res as $comment) {
			$obj = new stdClass();
			$obj->id = $comment->comment_id;
			$obj->link = $link->get_relative_permalink().'/000'.$comment->comment_order;
			$obj->user_id = $comment->user_id;
			$obj->avatar = $comment->user_avatar;
			$obj->title = text_to_summary($comment->content, 75);
			$obj->username = $comment->user_login;
			$obj->tooltip = 'c';
			$objects[] = $obj;
		}
		$vars = compact('objects', 'title', 'url');
		$output = Haanga::Load('best_comments_posts.html', $vars, true);
		echo $output;
		if($do_cache) {
			memcache_madd($key, $output, 300);
		}
	}
}

function do_active_stories() {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$key = 'active_stories_'.$globals['site_shortname'].$globals['v'].'_'.$globals['meta_current'];
	if(memcache_mprint($key)) return;
	echo '<!-- Calculating '.__FUNCTION__.' -->';

	$category_list	= '';
	$title = _('destacadas');
	$url = $globals['base_url'].'topactive.php';

	$top = new Annotation('top-actives-'.$globals['site_shortname']);
	if ($top->read() && ($ids = explode(',',$top->text))) {
		$links = array();
		$ids = array_slice($ids, 0, 5);
		foreach($ids as $id) {
			$link = Link::from_db($id);
			$link->url = $link->get_relative_permalink();
			$link->thumb = $link->has_thumb();
			$link->total_votes = $link->votes+$link->anonymous;
			if ($link->thumb) {
				$link->thumb_x = round($link->thumb_x / 2);
				$link->thumb_y = round($link->thumb_y / 2);
			}
			if ($link->negatives >= $link->votes/10) $link->warn = true;
			$links[] = $link;
		}
		$subclass = 'red';
		$vars = compact('links', 'title', 'url', 'subclass');
		$output = Haanga::Load('best_stories.html', $vars, true);
		echo $output;
		memcache_madd($key, $output, 60);
	}
}

function do_best_stories() {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$key = 'best_stories_'.$globals['site_shortname'].$globals['v'].'_'.$globals['meta_current'];
	if(memcache_mprint($key)) return;
	echo '<!-- Calculating '.__FUNCTION__.' -->';

	if ($globals['meta_current'] && $globals['meta_categories']) {
			$category_list = 'and link_category in ('.$globals['meta_categories'].')';
			$title = sprintf(_('más votadas «%s»'), $globals['meta_current_name']);
	} else {
		$category_list	= '';
		$title = _('más votadas');
	}

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 129600); // 36 hours
	// The order is not exactly the votes
	// but a time-decreasing function applied to the number of votes
	$res = $db->get_results("select link_id, (link_votes-link_negatives*2)*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.8/129600) as value from links, sub_statuses where id = ".SitesMgr::my_id()." AND link_id = link AND status='published' $category_list and date > '$min_date' order by value desc limit 5");
	if ($res) {
		$links = array();
		$url = $globals['base_url'].'topstories.php';
		$link = new Link();
		foreach ($res as $l) {
			$link = Link::from_db($l->link_id);
			$link->url = $link->get_relative_permalink();
			$link->thumb = $link->has_thumb();
			$link->total_votes = $link->votes+$link->anonymous;
			if ($link->thumb) {
				$link->thumb_x = round($link->thumb_x / 2);
				$link->thumb_y = round($link->thumb_y / 2);
			}
			if ($link->negatives >= $link->votes/10) $link->warn = true;
			$links[] = $link;
		}
		$subclass = '';
		$vars = compact('links', 'title', 'url', 'subclass');
		$output = Haanga::Load('best_stories.html', $vars, true);
		echo $output;
		memcache_madd($key, $output, 180);
	}
}

function do_best_queued() {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$key = 'best_queued_'.$globals['site_shortname'].$globals['v'].'_'.$globals['meta_current'];
	if(memcache_mprint($key)) return;
	echo '<!-- Calculating '.__FUNCTION__.' -->';

	$avg_karma = intval($db->get_var("SELECT avg(karma) from sub_statuses WHERE id = ".SitesMgr::my_id()." AND date >= date_sub(now(), interval 1 day) and status='published'"));
	if ($globals['meta_current'] && $globals['meta_categories']) {
			$category_list = 'and link_category in ('.$globals['meta_categories'].')';
			$title =sprintf( _('candidatas en «%s»'), $globals['meta_current_name']);
			$min_karma = intval($avg_karma/4);
	} else {
		$min_karma = intval($avg_karma/3);
		$category_list	= '';
		$title = _('candidatas');
	}
	$warned_threshold = intval($min_karma * 1.5);


	$min_date = date("Y-m-d H:i:00", $globals['now'] - 86400*3); // 3 days
	// The order is not exactly the votes
	// but a time-decreasing function applied to the number of votes
	$res = $db->get_results("select link_id from links, sub_statuses where id = ".SitesMgr::my_id()." AND status='queued' and link_id = link AND link_karma > $min_karma AND date > '$min_date' $category_list order by link_karma desc limit 20");
	if ($res) {
		$url = $globals['base_url'].'promote.php';
		$links = array();
		$link = new Link();
		foreach ($res as $l) {
			$link = Link::from_db($l->link_id);
			if ($link->negatives > $link->votes/10 && $link->karma < $warned_threshold) continue;
			$link->url = $link->get_relative_permalink();
			$link->thumb = $link->has_thumb();
			$link->total_votes = $link->votes+$link->anonymous;
			if ($link->thumb) {
				$link->thumb_x = round($link->thumb_x / 2);
				$link->thumb_y = round($link->thumb_y / 2);
			}
			if ($link->negatives >= $link->votes/10) $link->warn = true;
			$links[] = $link;
		}
		$subclass = '';
		$vars = compact('links', 'title', 'url', 'subclass');
		$output = Haanga::Load('best_stories.html', $vars, true);
		echo $output;
		memcache_madd($key, $output, 120);
	}
}

function do_most_clicked_stories() {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$key = 'most_clicked_'.$globals['site_shortname'].$globals['v'].'_'.$globals['meta_current'];
	if(memcache_mprint($key)) return;
	echo '<!-- Calculating '.__FUNCTION__.' -->';

	if ($globals['meta_current'] && $globals['meta_categories']) {
			$category_list = 'and link_category in ('.$globals['meta_categories'].')';
			$title = sprintf(_('más visitadas «%s»'), $globals['meta_current_name']);
	} else {
		$category_list	= '';
		$title = _('más visitadas');
	}

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 172800); // 48 hours
	// The order is not exactly the votes
	// but a time-decreasing function applied to the number of votes
	$res = $db->get_results("select link_id, counter*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.5/172800) as value from links, link_clicks, sub_statuses where sub_statuses.id = ".SitesMgr::my_id()." AND link_id = link AND status='published' $category_list and date > '$min_date' and link_clicks.id = link order by value desc limit 5");
	if ($res) {
		$links = array();
		$url = $globals['base_url'].'topclicked.php';
		$link = new Link();
		foreach ($res as $l) {
			$link = Link::from_db($l->link_id);
			$link->url = $link->get_relative_permalink();
			$link->thumb = $link->has_thumb();
			$link->total_votes = $link->votes+$link->anonymous;
			if ($link->thumb) {
				$link->thumb_x = round($link->thumb_x / 2);
				$link->thumb_y = round($link->thumb_y / 2);
			}
			if ($link->negatives >= $link->votes/10) $link->warn = true;
			$links[] = $link;
		}
		$vars = compact('links', 'title', 'url');
		$output = Haanga::Load('most_clicked_stories.html', $vars, true);
		echo $output;
		memcache_madd($key, $output, 180);
	}
}

function do_best_posts() {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$output = '';

	$key = 'best_posts_'.$globals['site_shortname'].$globals['v'];
	if(memcache_mprint($key)) return;
	echo '<!-- Calculating '.__FUNCTION__.' -->';

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 86400); // about 24 hours
	$res = $db->get_results("select post_id from posts, users where post_date > '$min_date' and  post_user_id = user_id and post_karma > 0 order by post_karma desc limit 10");
	if ($res) {
		$objects = array();
		$title = _('mejores notas');
		$url = post_get_base_url('_best');
		foreach ($res as $p) {
			$obj = new stdClass();
			$post = new Post;
			$post->id = $p->post_id;
			$post->read();
			$obj->id = $post->id;
			$obj->link = post_get_base_url().$post->id;
			$obj->user_id = $post->author;
			$obj->avatar = $post->avatar;
			$obj->title = text_to_summary($post->clean_content(), 80);
			$obj->username = $post->username;
			$obj->tooltip = 'p';
			$objects[] = $obj;
		}
		$vars = compact('objects', 'title', 'url');
		$output = Haanga::Load('best_comments_posts.html', $vars, true);
		echo $output;
		memcache_madd($key, $output, 300);
	}
}

function do_last_blogs() {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$foo = new Comment();
	$output = '';

	$key = 'last_blogs_'.$globals['v'];
	if(memcache_mprint($key)) return;
	echo '<!-- Calculating '.__FUNCTION__.' -->';


	$entries = $db->get_results("select rss.blog_id, rss.user_id, title, url, user_login, user_avatar from rss, users where rss.user_id = users.user_id order by rss.date desc limit 10");
	if ($entries) {
		$objects = array();
		$title = _('apuntes de blogs');
		$url = $globals['base_url'].'rsss.php';
		foreach ($entries as $entry) {
			$obj = new stdClass();
			$obj->user_id = $entry->user_id;
			$obj->avatar = $entry->user_avatar;
			$obj->title = text_to_summary($entry->title, 75);
			$obj->link = $entry->url;
			$obj->username = $entry->user_login;
			$objects[] = $obj;
		}
		$vars = compact('objects', 'title', 'url');
		$output = Haanga::Load('last_blogs.html', $vars, true);
		echo $output;
		memcache_madd($key, $output, 300);
	}
}

function do_error($mess = false, $error = false, $send_status = true) {
	global $globals;
	$globals['ads'] = false;

	if (! $mess ) $mess = _('algún error nos ha petado');

	if ($error && $send_status) {
		header("HTTP/1.0 $error $mess");
		header("Status: $error $mess");
	}

	do_header(_('error'));
	Haanga::Load('error.html', compact('mess', 'error'));
	do_footer_menu();
	do_footer();
	die;
}

function do_subheader($content, $selected = false) {
// arguments: hash array with "button text" => "button URI"; Nº of the selected button
	echo '<ul class="subheader">'."\n";
	if (is_array($content)) {
		$n = 0;
		foreach ($content as $text => $url) {
			if ($selected == $n) $class_b = ' class = "selected"';
			else $class_b='';
			echo '<li'.$class_b.'>';
			echo '<a href="'.$url.'">'.$text.'</a>';
			echo '</li>';
			$n++;
		}
	} else {
		echo '<h1>'.$content.'</h1>';
	}
	echo '</ul>'."\n";
}

?>
