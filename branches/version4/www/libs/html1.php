<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


@include mnminclude.'ads-credits-functions.php';

// Warning, it redirects to the content of the variable
if (!empty($globals['lounge'])) {
	header('Location: http://'.get_server_name().$globals['base_url'].$globals['lounge']);
	die;
}


$globals['extra_js'] = Array();
$globals['extra_css'] = Array();
$globals['post_js'] = Array();

$globals['start_time'] = microtime(true);

function do_tabs($tab_name, $tab_selected = false, $extra_tab = false) {
	global $globals;

	$reload_text = _('recargar');
	$active = ' class="tabmain-this"';

	if ($tab_name == "main" ) {
		echo '<ul class="tabmain">' . "\n";

		// url with parameters?
		if (!empty($_SERVER['QUERY_STRING']))
			$query = "?".htmlentities($_SERVER['QUERY_STRING']);

		// START STANDARD TABS
		// First the standard and always present tabs
		// published tab
		if ($tab_selected == 'published') {
			echo '<li '.$active.'><a href="'.$globals['base_url'].'" title="'.$reload_text.'">'._('portada').'</a></li>' . "\n";
		} else {
			echo '<li><a  href="'.$globals['base_url'].'">'._('portada').'</a></li>' . "\n";
		}

		/*
		// Google Map
		if ($tab_selected == 'map') {
			echo '<li '.$active.'><a href="'.$globals['base_url'].'map.php" title="'.$reload_text.'">'._('mapa').'</a></li>' . "\n";
		} else {
			echo '<li><a href="'.$globals['base_url'].'map.php">'._('mapa').'</a></li>' . "\n";
		}
		*/

		// Most voted
		if ($tab_selected == 'popular') {
			echo '<li '.$active.'><a href="'.$globals['base_url'].'topstories.php" title="'.$reload_text.'">'._('populares').'</a></li>' . "\n";
		} else {
			echo '<li><a href="'.$globals['base_url'].'topstories.php">'._('populares').'</a></li>' . "\n";
		}

		// shake it
		if ($tab_selected == 'shakeit') {
			echo '<li '.$active.'><a href="'.$globals['base_url'].'shakeit.php" title="'.$reload_text.'">'._('menear pendientes').'</a></li>' . "\n";
		} else {
			echo '<li><a href="'.$globals['base_url'].'shakeit.php">'._('menear pendientes').'</a></li>' . "\n";
		}
		// END STANDARD TABS

		//Extra tab
		if ($extra_tab) {
			if ($globals['link_permalink']) $url = $globals['link_permalink'];
			else $url = htmlentities($_SERVER['REQUEST_URI']);
			echo '<li '.$active.'><a href="'.$url.'" title="'.$reload_text.'">'.$tab_selected.'</a></li>' . "\n";
		}
		echo '</ul>' . "\n";
	}
}

function do_header($title, $id='home') {
	global $current_user, $dblang, $globals;

	check_auth_page();
	header('Content-Type: text/html; charset=utf-8');
	http_cache();


	if(!empty($globals['link_id'])) {
		// Pingback autodiscovery
		// http://www.hixie.ch/specs/pingback/pingback
		header('X-Pingback: http://' . get_server_name() . $globals['base_url'] . 'xmlrpc.php');
	}

    $globals['security_key'] = get_security_key();
    if (!empty($_REQUEST['q'])) $globals['q'] = $_REQUEST['q'];
    $globals['uri'] = $_SERVER['REQUEST_URI'];

	if ($globals['greetings']) $greeting = array_rand($globals['greetings'], 1);
	else $greeting = _('hola');
	
    $vars = compact('title', 'greeting', 'id');
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
	global $globals;

	echo "</div><!--#container closed-->\n";
	if($credits) @do_credits();
	do_js_from_array($globals['post_js']);

	// warn warn warn 
	// dont do stats of password recovering pages
	@include('ads/stats.inc');
	printf("\n<!--Generated in %4.3f seconds-->\n", microtime(true) - $globals['start_time']);
	echo "</div></body></html>\n";
}

function do_footer_menu() {
	global $globals, $current_user;

	echo '<div id="footwrap">'."\n";

	echo '<div id="footcol1">'."\n";
	do_rss();
	echo '</div>'."\n";

	echo '<div id="footcol2">'."\n";
	do_footer_help();
	echo '</div>'."\n";

	echo '<div id="footcol3">'."\n";
	do_footer_plus_meneame();
	echo '</div>'."\n";

	echo '<div id="footcol4">'."\n";
	echo '<h5>estadísticas</h5>'."\n";
	echo '<ul id="statisticslist">'."\n";
	echo '<li><a href="'.$globals['base_url'].'topusers.php">'._('usuarios').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'topstories.php">'._('populares').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'topcommented.php">'._('más comentadas').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'topcomments.php">'._('mejores comentarios').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'cloud.php">'._('nube de etiquetas').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'sitescloud.php">'._('nube de webs').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'promote.php">'._('candidatas').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'values.php">'._('parámetros básicos').'</a></li>'."\n";
	echo '</ul>'."\n";
	echo '</div>'."\n";

	echo '<div id="footcol5">'."\n";
	echo '<h5>mapas</h5>'."\n";
	echo '<ul id="mapslist">'."\n";
	echo '<li><a href="'.$globals['base_url'].'geovision.php">'._('geovisión').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'map.php">'._('noticias').'</a></li>'."\n";
	echo '</ul>'."\n";

	do_footer_shop();

	echo '</div>'."\n";

	echo '</div>'."\n";
}

function do_rss() {
	global $globals, $current_user;

	echo '<h5>'._('suscripciones por RSS').'</h5>'."\n";
	echo '<ul>'."\n";

	echo '<li>';
	echo '<a href="'.$globals['base_url'].'rss2.php" rel="rss">'._('publicadas').'</a>';
	echo '</li>' . "\n";
	
	echo '<li>';
	echo '<a href="'.$globals['base_url'].'rss2.php?status=queued" rel="rss">'._('en cola').'</a>';
	echo '</li>' . "\n";

	if($current_user->user_id > 0) {
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'comments_rss2.php?conversation_id='.$current_user->user_id.'" rel="rss" title="'._('comentarios de las noticias donde has comentado').'">'._('mis conversaciones').'</a>';
		echo '</li>' . "\n";
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'comments_rss2.php?author_id='.$current_user->user_id.'" rel="rss">'._('comentarios a mis noticias').'</a>';
		echo '</li>' . "\n";
	}

	echo '<li>';
	echo '<a href="'.$globals['base_url'].'comments_rss2.php" rel="rss">'._('todos los comentarios').'</a>';
	echo '</li>' . "\n";
	echo '</ul>' . "\n";
}

function do_rss_box($search_rss = 'rss2.php') {
	global $globals, $current_user;

	if ($globals['mobile']) return;

	echo '<div class="sidebox"><div class="header"><h4>'._('suscripciones por RSS').'</h4></div>'."\n";
	echo '<div class="rss"><ul>'."\n";

	if(!empty($_REQUEST['q'])) {
		$search =  htmlspecialchars($_REQUEST['q']);
		echo '<li>';
		echo '<a href="'.$globals['base_url'].$search_rss.'?'.htmlspecialchars($_SERVER['QUERY_STRING']).'" rel="rss">'._("búsqueda").': '. htmlspecialchars($_REQUEST['q'])."</a>\n";
		echo '</li>';
	}

	// RSS related to a single link
	if ($globals['link']) {
		if(!empty($globals['link']->meta_name)) {
			echo '<li>';
			echo '<a href="'.$globals['base_url'].'rss2.php?meta='.$globals['link']->meta_id.'&amp;status=all" rel="rss">'._('temática').': <em>'.$globals['link']->meta_name."</em></a>\n";
			echo '</li>';
		}
		if(!empty($globals['link']->category_name)) {
			echo '<li>';
			echo '<a href="'.$globals['base_url'].'rss2.php?category='.$globals['link']->category.'&amp;status=all" rel="rss">'._('categoría').': <em>'.$globals['link']->category_name."</em></a>\n";
			echo '</li>';
		}
	}
	echo '<li>';
	echo '<a href="'.$globals['base_url'].'rss2.php" rel="rss">'._('publicadas').'</a>';
	echo '</li>' . "\n";
	
	echo '<li>';
	echo '<a href="'.$globals['base_url'].'rss2.php?status=queued" rel="rss">'._('en cola').'</a>';
	echo '</li>' . "\n";

	if($globals['link_id']) {
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'comments_rss2.php?id='.$globals['link_id'].'" rel="rss">'._('comentarios de esta noticia').'</a>';
		echo '</li>' . "\n";
	}

	if($current_user->user_id > 0) {
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'comments_rss2.php?conversation_id='.$current_user->user_id.'" rel="rss" title="'._('comentarios de las noticias donde has comentado').'">'._('mis conversaciones').'</a>';
		echo '</li>' . "\n";
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'comments_rss2.php?author_id='.$current_user->user_id.'" rel="rss">'._('comentarios a mis noticias').'</a>';
		echo '</li>' . "\n";
	}

	echo '<li>';
	echo '<a href="'.$globals['base_url'].'comments_rss2.php" rel="rss">'._('todos los comentarios').'</a>';
	echo '</li>' . "\n";
	echo '</ul></div></div>' . "\n";
}

function get_toggler_plusminus($container_id, $enabled = false) {
	global $globals;

	static $n = 0;

	if ($enabled) {
		$image = $globals['base_static'].'img/common/minus-001.png';
	} else {
		$image = $globals['base_static'].'img/common/plus-001.png';
	}
	echo "<script type=\"text/javascript\">";
	if ($n == 0) {
		echo "var plus = '".$globals['static_server']."' + base_url + 'img/common/plus-001.png';\n";
		echo "var minus = '".$globals['static_server']."' + base_url + 'img/common/minus-001.png';\n";
	}
	echo "bindTogglePlusMinus('toggle_i_$n', 'toggle_l_$n', '$container_id')";
	echo "</script>\n";
	return "<a class='toggler' id='toggle_l_$n' href=''><img src='$image' id='toggle_i_$n' alt='' width='18' height='18'/></a>";
	$n++;
}

function do_mnu_categories_horizontal($what_cat_id) {
	global $db, $dblang, $globals;

	echo '<div id="topcatlist" class="catsub-block"';
	if (! $what_cat_id) echo ' style="display:none;"';
	echo "><ul>\n";

	$query=preg_replace('/category=[0-9]*/', '', $_SERVER['QUERY_STRING']);
	// If a meta is not a "virtual" one, delete it.
	$query=preg_replace('/meta=[a-z]+/i', '', $query);
	// Always return to page 1
	$query=preg_replace('/page=[0-9]*/', '', $query);
	$query=preg_replace('/^&*(.*)&*$/', "$1", $query);
	if(!empty($query)) {
		$query = htmlspecialchars($query);
		$query = "&amp;$query";
	}

	// draw categories
	if (!empty($globals['meta_categories'])) {
		$category_condition = "category_id in (".$globals['meta_categories'].")";
	} else {
		$category_condition = "category_parent > 0";
	}
	$categories = $db->get_results("SELECT SQL_CACHE category_id, category_name FROM categories WHERE $category_condition ORDER BY category_name ASC");
	if ($categories) {
		$i = 0;
		foreach ($categories as $category) {
			if($category->category_id == $what_cat_id) {
				$globals['category_id'] = $category->category_id;
				$globals['category_name'] = $category->category_name;
				$thiscat = ' class="thiscat"';
			} else {
				$thiscat = '';
			}

			echo '<li'.$thiscat.'>';
			if ($i > 0) {
				echo '&bull; &nbsp;'; 
			}
			$i++;
			echo '<a href="'.$base_url.'?category='.$category->category_id.$query.'">';
			echo _($category->category_name);
			echo "</a></li>\n";
		}
	}

	echo '</ul>';
	echo '</div>' . "\n";

}

function force_authentication() {
	global $current_user, $globals;

	if(!$current_user->authenticated) {
		header('Location: '.$globals['base_url'].'login.php?return='.$_SERVER['REQUEST_URI']);
		die;
	}
	return true;
}

function mobile_redirect() {
	global $globals;

	if ($globals['mobile'] && ! preg_match('/(pad|tablet|wii|tv)\W/i', $_SERVER['HTTP_USER_AGENT']) &&
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
		echo '<div class="pages-margin">';
	} else {
		echo '<div class="pages">';
	}

	if($current==1) {
		echo '<span class="nextprev">&#171; '._('anterior'). '</span>';
	} else {
		$i = $current-1;
		echo '<a href="?page='.$i.$query.'">&#171; '._('anterior').'</a>';
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
				echo '<a href="?page='.$i.$query.'" title="'._('ir a página')." $i".'">'.$i.'</a>';
			}
		}

		if($total_pages>$end) {
			$i = $total_pages;
			echo '<span>...</span>';
			echo '<a href="?page='.$i.$query.'" title="'._('ir a página')." $i".'">'.$i.'</a>';
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
		echo '<a href="?page='.$i.$query.'">&#187; '._('siguiente').'</a>';
	} else {
		echo '<span class="nextprev">&#187; '._('siguiente'). '</span>';
	}
	echo "</div><!--html1:do_pages-->\n";

}

//Used in editlink.php and submit.php
function print_categories_form($selected = 0) {
	global $db, $dblang;
	echo '<fieldset style="clear: both;">';
	echo '<legend>'._('selecciona la categoría más apropiada').'</legend>'."\n";
	$metas = $db->get_results("SELECT category_id, category_name FROM categories WHERE category_parent = 0 ORDER BY category_name ASC");
	foreach ($metas as $meta) {
		echo '<dl class="categorylist"><dt>'.$meta->category_name.'</dt>'."\n";
		$categories = $db->get_results("SELECT category_id, category_name FROM categories WHERE category_parent = $meta->category_id ORDER BY category_name ASC");
		foreach ($categories as $category) {
			echo '<dd><input name="category" type="radio" ';
			if ($selected == $category->category_id) echo '  checked="true" ';
			echo 'value="'.$category->category_id.'"/> '._($category->category_name).'</dd>'."\n";
		}
		echo '</dl>'."\n";
	}
	echo '<br style="clear: both;"/>' . "\n";
	echo '</fieldset>';
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
		$meta_cond = 'and link_category in ('.$globals['meta_categories'].')';
	}

	$cache_key = 'tags_'.$globals['css_main'].$status.$meta_cond;
	if(memcache_mprint($cache_key)) return;

	$min_pts = 8;
	$max_pts = 22;

	// Delete old tags, they are not used anywhere else
	$db->query("delete from tags where tag_lang = '$dblang' and tag_date < date_sub(now(), interval 8 day)");

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 172800); // 48 hours (edit! 2zero)
	$from_where = "FROM tags, links WHERE tag_lang='$dblang' and tag_date > '$min_date' and link_id = tag_link_id and link_status $status $meta_cond GROUP BY tag_words";
	$max = 3;
	//$max = max($db->get_var("select count(*) as words $from_where order by words desc limit 1"), 3);

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
		foreach ($words as $word => $count) {
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
		$vars = compact('content', 'title', 'url');
		$output = Haanga::Load('tags_sidebox.html', $vars, true);
		echo $output;
		memcache_madd($cache_key, $output, 600);
	}
}

function do_categories_cloud($what=false, $hours = 48) {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$cache_key = 'categories_cloud_'.$globals['css_main'].$what;
	if(memcache_mprint($cache_key)) return;


	if (!empty($what)) {
		$status = '= "'.$what. '"';
	} else {
		$status = "!= 'discarded'";
	}


	$min_pts = 8;
	$max_pts = 22;

	$min_date = date("Y-m-d H:i:00", $globals['now'] - $hours*3600); 
	$from_where = "from categories, links where link_status $status and link_date > '$min_date' and link_category = category_id group by category_name";
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

	$key = 'best_sites_'.$globals['css_main'].'_'.$globals['meta_current'];
	if(memcache_mprint($key)) return;

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 172800); // about  48 hours
	// The order is not exactly the votes counts
	// but a time-decreasing function applied to the number of votes
	$res = $db->get_results("select sum(link_votes-link_negatives*2)*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.8/172800) as coef, sum(link_votes-link_negatives*2) as total, blog_url from links, blogs where link_date > '$min_date' and link_status='published' and link_blog = blog_id group by link_blog order by coef desc limit 10;
");
	if ($res) {
		$i = 0;
		$output .= '<div class="sidebox"><div class="header"><h4>'._('sitios más votados').'</h4></div><div class="mainsites"><ul>'."\n";
		foreach ($res as $site) {
			$i++;
			$parsed_url = parse_url($site->blog_url);
			$output .= '<li>'.$i.'. <a href="'.$globals['base_url'].'search.php?q='.rawurlencode($site->blog_url).'&amp;p=site&amp;h=48&amp;s=published" title="'._('votos 48 horas').': '.$site->total.' (coef: '.intval($site->coef).')">'.$parsed_url['host'].'</a></li>'."\n";
		}
		$output .= '</ul></div></div>';
		echo $output;
		memcache_madd($key, $output, 300);
	}
}

function do_best_comments() {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$foo = new Comment();
	$output = '';

	$key = 'best_comments_'.$globals['css_main'];
	if(memcache_mprint($key)) return;

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 43000); // about 12 hours 
	$link_min_date = date("Y-m-d H:i:00", $globals['now'] - 86400); // 24 hours
	$now = intval($globals['now']/60) * 60;
	// The order is not exactly the comment_karma
	// but a time-decreasing function applied to the number of votes
	$res = $db->get_results("select comment_id, comment_order, user_id, user_login, user_avatar, link_id, link_uri, link_title, link_comments, comment_karma*(1-($now-unix_timestamp(comment_date))*0.7/43000) as value, link_negatives/link_votes as rel from comments, links, users  where link_date > '$link_min_date' and comment_date > '$min_date' and link_negatives/link_votes < 0.5  and comment_karma > 50 and comment_link_id = link_id and comment_user_id = user_id order by value desc limit 12");
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
			$obj->tooltip = 'get_comment_tooltip.php';
			array_push($objects, $obj);
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
		$key = 'best_story_comments_'.$globals['css_main'].$link->id;
		if(memcache_mprint($key)) return;
	}

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
			$obj->tooltip = 'get_comment_tooltip.php';
			array_push($objects, $obj);
		}
		$vars = compact('objects', 'title', 'url');
		$output = Haanga::Load('best_comments_posts.html', $vars, true);
		echo $output;
		if($do_cache) {
			memcache_madd($key, $output, 300);
		}
	}
}

function do_best_stories() {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$key = 'best_stories_'.$globals['css_main'].'_'.$globals['meta_current'];
	if(memcache_mprint($key)) return;

	if ($globals['meta_current'] && $globals['meta_categories']) {
			$category_list = 'and link_category in ('.$globals['meta_categories'].')';
			$title = sprintf(_('populares de «%s»'), $globals['meta_current_name']);
	} else {
		$category_list  = '';
		$title = _('populares');
	}

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 129600); // 36 hours 
	// The order is not exactly the votes
	// but a time-decreasing function applied to the number of votes
	$res = $db->get_results("select link_id, (link_votes-link_negatives*2)*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.8/129600) as value from links where link_status='published' $category_list and link_date > '$min_date' order by value desc limit 10");
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
			array_push($links, $link);
		}
		$vars = compact('links', 'title', 'url');
		$output = Haanga::Load('best_stories.html', $vars, true);
		echo $output;
		memcache_madd($key, $output, 180);
	}
}

function do_best_queued() {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$key = 'best_queued_'.$globals['css_main'].'_'.$globals['meta_current'];
	if(memcache_mprint($key)) return;

	if ($globals['meta_current'] && $globals['meta_categories']) {
			$category_list = 'and link_category in ('.$globals['meta_categories'].')';
			$title =sprintf( _('candidatas en «%s»'), $globals['meta_current_name']);
	} else {
		$category_list  = '';
		$title = _('candidatas');
	}

	
	$min_date = date("Y-m-d H:i:00", $globals['now'] - 86400*4); // 4 days
	// The order is not exactly the votes
	// but a time-decreasing function applied to the number of votes
	$res = $db->get_results("select link_id from links where link_status='queued' and link_date > '$min_date' $category_list order by link_karma desc limit 15");
	if ($res) {
		$url = $globals['base_url'].'promote.php';
		$links = array();
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
			array_push($links, $link);
		}
		$vars = compact('links', 'title', 'url');
		$output = Haanga::Load('best_stories.html', $vars, true);
		echo $output;
		memcache_madd($key, $output, 180);
	}
}

function do_best_posts() {
	global $db, $globals, $dblang;

	if ($globals['mobile']) return;

	$output = '';

	$key = 'best_posts_'.$globals['css_main'];
	if(memcache_mprint($key)) return;

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
			$obj->link = post_get_base_url($post->username).'/'.$post->id;
			$obj->user_id = $post->author;
			$obj->avatar = $post->avatar;
			$obj->title = text_to_summary($post->clean_content(), 80);
			$obj->username = $post->username;
			$obj->tooltip = 'get_post_tooltip.php';
			array_push($objects, $obj);
		}
		$vars = compact('objects', 'title', 'url');
		$output = Haanga::Load('best_comments_posts.html', $vars, true);
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
	echo '<STYLE TYPE="text/css" MEDIA=screen>'."\n";
	echo '<!--'."\n";
	echo '.errt { text-align:center; padding-top:50px; font-size:300%; color:#FF6400;}'."\n";
	echo '.errl { text-align:center; margin-top:50px; margin-bottom:100px; }'."\n";
	echo '-->'."\n";
	echo '</STYLE>'."\n";

	echo '<p class="errt">'.$mess.'<br />'."\n";
	if ($error) echo '('._('error').' '.$error.')</p>'."\n";
	echo '<div class="errl"><img src="'.$globals['base_url'].'img/mnm/lame_excuse_01.png" width="362" height="100" alt="ooops logo" /></div>'."\n";

	do_footer_menu();
	do_footer();
	die;
}

function do_subheader($content, $selected = false) { 
// arguments: hash array with "button text" => "button URI"; Nº of the selected button 
	echo '<ul class="subheader" style="margin-bottom: 20px">'."\n";
	if (is_array($content)) {
		$n = 0;
		foreach ($content as $text => $url) {
	   		if ($selected == $n) $class_b = ' class = "selected"'; 
			else $class_b='';
	   		echo '<li'.$class_b.'>'."\n";
	   		echo '<a href="'.$url.'">'.$text."</a>\n";
	   		echo '</li>'."\n";
	   		$n++;
		}
	} else {
	    echo '<h1>'.$content.'</h1>'; 
	}
	echo '</ul>'."\n";
}

?>
