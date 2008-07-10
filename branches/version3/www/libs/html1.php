<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


@include mnminclude.'ads-credits-functions.php';

include_once(mnminclude.'post.php');

// Warning, it redirects to the content of the variable
if (!empty($globals['lounge'])) {
	header('Location: http://'.get_server_name().$globals['base_url'].$globals['lounge']);
	die;
}

$globals['start_time'] = microtime(true);

header("Content-type: text/html; charset=utf-8");

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
			echo '<li '.$active.'><a href="'.$globals['base_url'].'" title="'.$reload_text.'"><em>'._('portada').'</em></a></li>' . "\n";
		} else {
			echo '<li><a  href="'.$globals['base_url'].'">'._('portada').'</a></li>' . "\n";
		}

		// Google Map
		if ($tab_selected == 'map') {
			echo '<li '.$active.'><a href="'.$globals['base_url'].'map.php" title="'.$reload_text.'"><em>'._('mapa').'</em></a></li>' . "\n";
		} else {
			echo '<li><a href="'.$globals['base_url'].'map.php">'._('mapa').'</a></li>' . "\n";
		}

		// Most voted
		if ($tab_selected == 'popular') {
			echo '<li '.$active.'><a href="'.$globals['base_url'].'topstories.php" title="'.$reload_text.'"><em>'._('popular').'</em></a></li>' . "\n";
		} else {
			echo '<li><a href="'.$globals['base_url'].'topstories.php">'._('popular').'</a></li>' . "\n";
		}

		// shake it
		if ($tab_selected == 'shakeit') {
			echo '<li '.$active.'><a href="'.$globals['base_url'].'shakeit.php" title="'.$reload_text.'"><em>'._('menear pendientes').'</em></a></li>' . "\n";
		} else {
			echo '<li><a href="'.$globals['base_url'].'shakeit.php">'._('menear pendientes').'</a></li>' . "\n";
		}
		// END STANDARD TABS

		//Extra tab
		if ($extra_tab) {
			if ($globals['link_permalink']) $url = $globals['link_permalink'];
			else $url = htmlentities($_SERVER['REQUEST_URI']);
			echo '<li '.$active.'><a href="'.$url.'" title="'.$reload_text.'"><em>'.$tab_selected.'</em></a></li>' . "\n";
		}
		echo '</ul>' . "\n";
	}
}

function do_header($title, $id='home') {
	global $current_user, $dblang, $globals;

	if(!empty($globals['link_id'])) {
		// Pingback autodiscovery
		// http://www.hixie.ch/specs/pingback/pingback
		header('X-Pingback: http://' . get_server_name() . $globals['base_url'] . 'xmlrpc.php');
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
	//echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'" lang="'.$dblang.'">' . "\n";
	echo '<head>' . "\n";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
	echo "<title>$title</title>\n";

	do_css_includes();

	echo '<meta name="generator" content="meneame" />' . "\n";
	if (!empty($globals['noindex'])) {
		echo '<meta name="robots" content="noindex,follow"/>' . "\n";
	}
	if (!empty($globals['tags'])) {
		echo '<meta name="keywords" content="'.$globals['tags'].'" />' . "\n";
	}
	echo '<link rel="microsummary" type="application/x.microsummary+xml" href="'.$globals['base_url'].'microsummary.xml" />' . "\n";
	echo '<link rel="search" type="application/opensearchdescription+xml" title="Menéame Search" href="http://'.get_server_name().$globals['base_url'].'opensearch_plugin.php"/>'."\n";

	echo '<link rel="alternate" type="application/rss+xml" title="'._('publicadas').'" href="http://'.get_server_name().$globals['base_url'].'rss2.php" />'."\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('pendientes').'" href="http://'.get_server_name().$globals['base_url'].'rss2.php?status=queued" />'."\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('comentarios').'" href="http://'.get_server_name().$globals['base_url'].'comments_rss2.php" />'."\n";

	if (empty($globals['favicon'])) $globals['favicon'] = 'img/favicons/favicon4.ico';
	echo '<link rel="icon" href="'.$globals['base_url'].$globals['favicon'].'" type="image/x-icon"/>' . "\n";

	if(!empty($globals['link_id'])) {
		// Pingback autodiscovery
		// http://www.hixie.ch/specs/pingback/pingback
		echo '<link rel="pingback" href="http://' . get_server_name() . $globals['base_url'] . 'xmlrpc.php"/>'."\n";
	}

	do_js_includes();

	if ($globals['extra_head']) echo $globals['extra_head'];

	echo '</head>' . "\n";
	echo "<body id=\"$id\" ". $globals['body_args']. ">\n";
	echo '<div id="wrap">' . "\n";

	echo '<div id="header">' . "\n";
	echo '<a href="'.$globals['base_url'].'" title="'._('inicio').'" id="logo">menéame</a>'."\n";
	echo '<ul id="headtools">' . "\n";

	// Main search form
	echo '<li class="noborder">' . "\n";
	echo '<form action="'.$globals['base_url'].'search.php" method="get" name="top_search">' . "\n";
	if (!empty($_REQUEST['q'])) {
		echo '<input type="text" name="q" value="'.htmlspecialchars(strip_tags($_REQUEST['q'])).'" />';
	} else {
		echo '<input name="q" value="'._('buscar...').'" type="text" onblur="if(this.value==\'\') this.value=\''._('buscar...').'\';" onfocus="if(this.value==\''._('buscar...').'\') this.value=\'\';"/>';
	}
	echo '<a href="javascript:document.top_search.submit()"><img class="searchIcon" align="top" alt="buscar" src="'.$globals['base_url'].'img/common/search-02.gif" id="submit_image"/></a>';
	echo '</form>';
	echo '</li>' . "\n";
	// form

	echo '<li><a href="http://meneame.wikispaces.com/Comenzando"><img src="'.$globals['base_url'].'img/common/help-bt.gif" alt="help button" title="'._('ayuda').'" width="12" height="12" /></a></li>';
	if ($current_user->user_level=='god' || $current_user->user_level=='admin') {
		echo '<li><a href="'.$globals['base_url'].'admin/bans.php"><img src="'.$globals['base_url'].'img/common/tools-bt.gif" alt="tools button" title="herramientas" width="14" height="12" /> </a></li>' . "\n";
	}

	if($current_user->authenticated) {
 		echo '<li><a href="'.get_user_uri($current_user->user_login).'">'._('bienvenid@').'&nbsp;'.$current_user->user_login.'&nbsp;<img src="'.get_avatar_url($current_user->user_id, $current_user->user_avatar, 20).'" width="15" height="15" alt="'.$current_user->user_login.'" title="'._('perfil').'"/></a></li>' . "\n";
  		echo '<li class="noborder"><a href="'.$globals['base_url'].'login.php?op=logout&amp;return='.urlencode($_SERVER['REQUEST_URI']).'"><img src="'.$globals['base_url'].'img/common/login-bt.gif" alt="login button" title="login" width="12" height="12" /> '. _('cerrar sesión').'</a></li>' . "\n";
	} else {
  		echo '<li><a href="'.$globals['base_url'].'register.php">' . _('registrarse') . '</a></li>' . "\n";
  		echo '<li class="noborder"><a href="'.$globals['base_url'].'login.php?return='.urlencode($_SERVER['REQUEST_URI']).'"><img src="'.$globals['base_url'].'img/common/login-bt.gif" alt="login button" title="login" width="12" height="12" /> '. _('login').'</a></li>' . "\n";
	}

	//echo '<li><a href="'.$globals['base_url'].'faq-'.$dblang.'.php">' . _('acerca de menéame').'</a></li>' . "\n";
	echo '</ul>' . "\n";
	echo '<span class="header-left">&nbsp;</span>' . "\n";
	echo '</div>' . "\n";
	echo '<div id="naviwrap"><ul id="navigation">'."\n";
	echo '<li class="first"><a href="'.$globals['base_url'].'submit.php">enviar noticia</a></li>'."\n";
	echo '<li class="second"><a href="'.$globals['base_url'].'shakeit.php">pendientes</a></li>'."\n";
	echo '<li class="third"><a href="'.$globals['base_url'].'sneak.php">fisgona</a></li>'."\n";
	echo '<li class="fourth"><a href="'.$globals['base_url'].'notame/">nótame</a></li>'."\n";
	echo '</ul></div>'."\n";
	do_banner_top();
	echo '<div id="container">'."\n";
}

function do_css_includes() {
	global $globals;

	if ($globals['css_main']) {
		echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$globals['base_url'].$globals['css_main'].'" />' . "\n";
	}
	if ($globals['css_color']) {
		echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$globals['base_url'].$globals['css_color'].'" />' . "\n";
	}
	foreach ($globals['extra_css'] as $css) {
		echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$globals['base_url'].'css/'.$css.'" />' . "\n";
	}

}

function do_js_includes() {
	global $globals;

	echo '<script type="text/javascript">var base_url="'.$globals['base_url'].'";</script>'."\n";
	// Cache for Ajax
	echo '<script src="'.$globals['base_url'].'js/jquery.pack.js" type="text/javascript"></script>' . "\n";
	echo '<script src="'.$globals['base_url'].'js/jsoc-0.12.0.js" type="text/javascript"></script>' . "\n";
	echo '<script src="'.$globals['base_url'].'js/jquery.simplemodal.pack.js" type="text/javascript"></script>' . "\n";
	echo '<script src="'.$globals['base_url'].'js/general07.js" type="text/javascript"></script>' . "\n";
	do_js_from_array($globals['extra_js']);
	echo '<script type="text/javascript">if(top.location != self.location)top.location = self.location;'."\n";
	if ($globals['extra_js_text']) {
		 echo $globals['extra_js_text']."\n";
	}
	echo '</script>'."\n";
}

function do_js_from_array($array) {
	global $globals;

	foreach ($array as $js) {
		if (preg_match('/^http|^\//', $js)) {
			echo '<script src="'.$js.'" type="text/javascript"></script>' . "\n";
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
	// Store as html page load
	stats_increment('html');
	printf("\n<!--Generated in %4.3f seconds-->\n", microtime(true) - $globals['start_time']);
	echo "</div></body></html>\n";
}

function do_footer_menu() {
	global $globals, $current_user;

	echo '<div id="footwrap">'."\n";

	echo '<div id="footcol1">'."\n";
	do_footer_rss();
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
	echo '<li><a href="'.$globals['base_url'].'promote.php">'._('candidatas').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'topcommented.php">'._('más comentadas').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'topcomments.php">'._('mejores comentarios').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'cloud.php">'._('nube de etiquetas').'</a></li>'."\n";
	echo '<li><a href="'.$globals['base_url'].'sitescloud.php">'._('nube de webs').'</a></li>'."\n";
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

function do_footer_rss() {
	global $globals, $current_user;

	echo '<h5>'._('suscripciones por RSS').'</h5>'."\n";
	echo '<ul id="rsslist">'."\n";

	if(!empty($_REQUEST['q'])) {
		$search =  htmlspecialchars($_REQUEST['q']);
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'rss2.php?q='.urlencode($search).'" rel="rss">'._("búsqueda").': '. htmlspecialchars($_REQUEST['q'])."</a>\n";
		echo '</li>';
	}

	if(!empty($globals['category_name'])) {
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'rss2.php?status=all&amp;category='.$globals['category_id'].'" rel="rss">'._("categoría").': '.$globals['category_name']."</a>\n";
		echo '</li>';
	}
	echo '<li>';
	echo '<a href="'.$globals['base_url'].'rss2.php" rel="rss">'._('publicadas').'</a>';
	echo '</li>' . "\n";
	
	echo '<li>';
	echo '<a href="'.$globals['base_url'].'rss2.php?status=queued" rel="rss">'._('en cola').'</a>';
	echo '</li>' . "\n";


	if(!empty($globals['link_id'])) {
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'comments_rss2.php?id='.$globals['link_id'].'" rel="rss">'._('comentarios esta noticia').'</a>';
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

	/*
	if(empty($globals['link_id'])) { // Netvibes. In homepage only.
		echo '<li class="mnu-rss-external">';
		echo '<a href="http://www.netvibes.com/subscribe.php?preconfig=7cec38e5bac4adc3608f68e8603bb3c3" title="Añadir a Netvibes"><img src="http://www.netvibes.com/img/add2netvibes.gif" width="91" height="17" border="0" alt="Añadir a Netvibes"/></a>';
		echo '</li>';
	}
	*/

	echo '</ul>' . "\n";
}

function do_mnu_categories_horizontal($what_cat_id) {
	global $db, $dblang, $globals;

	echo '<div class="catsub-block">' . "\n";
	echo '<ul>' . "\n";

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
	$categories = $db->get_results("SELECT category_id, category_name FROM categories WHERE $category_condition ORDER BY category_name ASC");
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
				echo '&bull; '; 
			}
			$i++;
			echo '<a href="'.$base_url.'?category='.$category->category_id.$query.'">';
			echo _($category->category_name);
			echo "</a></li>\n";
		}
	}

	echo '</ul>';
	echo '</div><!--html1:do_mnu_categories_horizontal-->' . "\n";

}

function force_authentication() {
	global $current_user;

	if(!$current_user->authenticated) {
		header('Location: '.$globals['base_url'].'login.php?return='.$_SERVER['REQUEST_URI']);
		die;
	}
	return true;
}

function do_pages($total, $page_size=25, $margin = true) {
	global $db;

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
	if($current<$total_pages) {
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
			echo 'value="'.$category->category_id.'"/>'._($category->category_name).'</dd>'."\n";
		}
		echo '</dl>'."\n";
	}
	echo '<br style="clear: both;"/>' . "\n";
	echo '</fieldset>';
}

function do_vertical_tags($what=false) {
	global $db, $globals, $dblang;

	if (!empty($what)) {
		$status = '= "'.$what. '"';
	} else {
		$status = "!= 'discarded'";
	}
	if(!empty($globals['meta_categories'])) {
		$meta_cond = 'and link_category in ('.$globals['meta_categories'].')';
	}

	if(memcache_mprint('tags'.$status.$meta_cond)) return;

	$min_pts = 8;
	$max_pts = 20;
	$line_height = $max_pts * 0.70;

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 172800); // 48 hours
	$from_where = "FROM tags, links WHERE tag_lang='$dblang' and tag_date > '$min_date' and link_id = tag_link_id and link_status $status $meta_cond GROUP BY tag_words";
	$max = max($db->get_var("select count(*) as words $from_where order by words desc limit 1"), 3);
	$coef = ($max_pts - $min_pts)/($max-1);

	$res = $db->get_results("select tag_words, count(*) as count $from_where order by count desc limit 30");
	if ($res) {
		$output = '<div class="vertical-box" style="text-align: center">';
		$output .= '<h4><a href="'.$globals['base_url'].'cloud.php">'._('etiquetas').'</a></h4><p 
		class="nube">'."\n";
		foreach ($res as $item) {
			$words[$item->tag_words] = $item->count;
		}
		ksort($words);
		foreach ($words as $word => $count) {
			$size = round($min_pts + ($count-1)*$coef, 1);
			$output .= '<a style="font-size: '.$size.'pt" href="';
			if ($globals['base_search_url']) {
				$output .= $globals['base_url'].$globals['base_search_url'].'tag:';
			} else {
				$output .= $globals['base_url'].'search.php?p=tag&amp;q=';
			}
			$output .= urlencode($word).'">'.$word.'</a>  ';
		}
		$output .= '</p></div>';
		echo $output;
		memcache_madd('tags'.$status.$meta_cond, $output, 600);
	}
}

function do_best_comments() {
	global $db, $globals, $dblang;
	require_once(mnminclude.'link.php');
	$foo_link = new Link();
	$output = '';

	if(memcache_mprint('best_comments_3')) return;

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 22000); // about 6 hours
	$res = $db->get_results("select comment_id, comment_order, user_login, link_id, link_uri, link_title, link_comments from comments, links, users  where comment_date > '$min_date' and comment_karma > 10 and comment_link_id = link_id and comment_user_id = user_id order by comment_karma desc limit 12");
	if ($res) {
		$output .= '<h4><a href="'.$globals['base_url'].'topcomments.php">'._('mejores comentarios').'</a></h4><ul class="topcommentsli">'."\n";
		foreach ($res as $comment) {
			$foo_link->uri = $comment->link_uri;
			$link = $foo_link->get_relative_permalink().get_comment_page_suffix($globals['comments_page_size'], $comment->comment_order, $comment->link_comments).'#comment-'.$comment->comment_order;
			$output .= '<li><strong>'.$comment->user_login.'</strong> '._('en').' <a  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_comment_tooltip.php\', \''.$comment->comment_id.'\', 10000);" href="'.$link.'">'.$comment->link_title.'</a></li>'."\n";
		}
		$output .= '</ul>';
		echo $output;
		memcache_madd('best_comments_3', $output, 300);
	}
}

function do_best_story_comments($link) {
	global $db, $globals, $dblang;

	$do_cache = false;
	$output = '';

	if ($link->comments > 30 && $globals['now'] - $link->date < 86400*2) $do_cache = true;

	if($do_cache && memcache_mprint('best_story_comments_'.$link->id)) return;

	$limit = min(25, intval($link->comments/4));
	$res = $db->get_results("select comment_id, comment_order, user_login, substring(comment_content, 1, 60) as content from comments, users  where comment_link_id = $link->id and comment_karma > 20 and comment_user_id = user_id order by comment_karma desc limit $limit");
	if ($res) {
		$output .= '<h4><a href="'.$link->get_relative_permalink().'/best-comments">'._('comentarios + valorados').'</a></h4><ul class="topcommentsli">'."\n";
		foreach ($res as $comment) {
			$url = $link->get_relative_permalink().get_comment_page_suffix($globals['comments_page_size'], $comment->comment_order, $link->comments).'#comment-'.$comment->comment_order;
			$output .= '<li><strong>'.$comment->user_login.'</strong> '._('en').' <a  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_comment_tooltip.php\', \''.$comment->comment_id.'\', 10000);" href="'.$url.'">'.text_to_summary($comment->content, 50).'</a></li>'."\n";
		}
		$output .= '</ul>';
		echo $output;
		memcache_madd('best_story_comments_'.$link->id, $output, 300);
	}
}

function do_best_stories() {
	global $db, $globals, $dblang;
	require_once(mnminclude.'link.php');
	$foo_link = new Link();
	$output = '<div id="sidepop"><h4><a href="'.$globals['base_url'].'topstories.php">'._('populares').'</a></h4>';

	if(memcache_mprint('best_stories_3')) return;

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 86400); // 24 hours
	// The order is not exactly the votes
	// but a time-decreasing function applied to the number of votes
	$res = $db->get_results("select link_id, link_uri, link_title, link_votes+link_anonymous as votes, (link_votes+link_anonymous)*(1-(unix_timestamp(now())-unix_timestamp(link_date))*0.5/86400) as value from links where link_status='published' and link_date > '$min_date' order by value desc limit 10");
	if ($res) {
		foreach ($res as $link) {
			$foo_link->uri = $link->link_uri;
			$url = $foo_link->get_relative_permalink();
			$output .= '<div class="mnm-pop">'.$link->votes.'</div>';
			$output .= '<h5><a href="'.$url.'">'.$link->link_title.'</a></h5>';
			$output .= '<div class="mini-pop"></div>'."\n";
		}
		$output .= '</div>'."\n";
		echo $output;
		memcache_madd('best_stories_3', $output, 300);
	}
}

function do_best_queued() {
	global $db, $globals, $dblang;
	require_once(mnminclude.'link.php');
	$foo_link = new Link();
	$output = '<div id="sidepop"><h4><a href="'.$globals['base_url'].'promote.php">'._('candidatas').'</a></h4>';

	if(memcache_mprint('best_queued_3')) return;

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 86400*2); // 48 hours
	// The order is not exactly the votes
	// but a time-decreasing function applied to the number of votes
	$res = $db->get_results("select link_id, link_uri, link_title, link_votes+link_anonymous as votes, link_karma from links where link_status='queued' and link_date > '$min_date' order by link_karma desc limit 15");
	if ($res) {
		foreach ($res as $link) {
			$foo_link->uri = $link->link_uri;
			$url = $foo_link->get_relative_permalink();
			$output .= '<div class="mnm-pop queued">'.$link->votes.'</div>';
			$output .= '<h5><a href="'.$url.'">'.$link->link_title.'</a></h5>';
			$output .= '<div class="mini-pop"></div>'."\n";
		}
		$output .= '</div>'."\n";
		echo $output;
		memcache_madd('best_queued_3', $output, 300);
	}
}

function do_best_posts() {
	global $db, $globals, $dblang;
	$output = '';

	if(memcache_mprint('best_posts_3')) return;

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 86400); // about 24 hours
	$res = $db->get_results("select post_id, post_content, user_login from posts, users where post_date > '$min_date' and  post_user_id = user_id and post_karma > 0 order by post_karma desc limit 10");
	if ($res) {
		$output .= '<h4><a href="'.post_get_base_url('_best').'">'._('mejores notas').'</a></h4><ul class="topcommentsli">'."\n";
		foreach ($res as $post) {
			$output .= '<li><strong>'.$post->user_login.'</strong>: <a  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_post_tooltip.php\', \''.$post->post_id.'\', 10000);" href="'.post_get_base_url($post->user_login).'/'.$post->post_id.'">'.text_to_summary($post->post_content, 50).'</a></li>'."\n";
		}
		$output .= '</ul>';
		echo $output;
		memcache_madd('best_posts_3', $output, 300);
	}
}

?>
