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

header("Content-type: text/html; charset=utf-8");
meta_get_current();

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
			echo '<li><a '.$active.' href="'.$globals['base_url'].'" title="'.$reload_text.'">'._('portada').'&nbsp;&nbsp;&nbsp;'.$reload_icon.'</a></li>' . "\n";
		} else {
			echo '<li><a  href="'.$globals['base_url'].'">'._('portada').'</a></li>' . "\n";
		}

		// Google Map
		if ($tab_selected == 'map') {
			echo '<li><a '.$active.' href="'.$globals['base_url'].'map.php" title="'.$reload_text.'">'._('mapa').'&nbsp;&nbsp;&nbsp;'.$reload_icon.'</a></li>' . "\n";
		} else {
			echo '<li><a href="'.$globals['base_url'].'map.php">'._('mapa').'</a></li>' . "\n";
		}

		// Most voted
		if ($tab_selected == 'popular') {
			echo '<li><a '.$active.' href="'.$globals['base_url'].'topstories.php" title="'.$reload_text.'">'._('popular').'&nbsp;&nbsp;&nbsp;'.$reload_icon.'</a></li>' . "\n";
		} else {
			echo '<li><a href="'.$globals['base_url'].'topstories.php">'._('popular').'</a></li>' . "\n";
		}

		// shake it
		if ($tab_selected == 'shakeit') {
			echo '<li><a '.$active.' href="'.$globals['base_url'].'shakeit.php" title="'.$reload_text.'">'._('menear pendientes').'&nbsp;&nbsp;&nbsp;'.$reload_icon.'</a></li>' . "\n";
		} else {
			echo '<li><a href="'.$globals['base_url'].'shakeit.php">'._('menear pendientes').'</a></li>' . "\n";
		}
		// END STANDARD TABS

		//Extra tab
		if ($extra_tab) {
			if ($globals['link_permalink']) $url = $globals['link_permalink'];
			else $url = htmlentities($_SERVER['REQUEST_URI']);
			echo '<li><a '.$active.' href="'.$url.'" title="'.$reload_text.'">'.$tab_selected.'&nbsp;&nbsp;&nbsp;'.$reload_icon.'</a></li>' . "\n";
		}
		echo '</ul>' . "\n";
	}
}

function do_header($title, $id='home') {
	global $current_user, $dblang, $globals, $css_main_file, $css_color_file;

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
	echo "<title>$title // men&eacute;ame</title>\n";
	echo '<meta name="generator" content="meneame" />' . "\n";
	if (!empty($globals['noindex'])) {
		echo '<meta name="robots" content="noindex,follow"/>' . "\n";
	}
	if (!empty($globals['tags'])) {
		echo '<meta name="keywords" content="'.$globals['tags'].'" />' . "\n";
	}
	echo '<link rel="microsummary" type="application/x.microsummary+xml" href="'.$globals['base_url'].'microsummary.xml" />' . "\n";

	do_css_includes();

	echo '<link rel="alternate" type="application/rss+xml" title="'._('publicadas').'" href="http://'.get_server_name().$globals['base_url'].'rss2.php" />'."\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('pendientes').'" href="http://'.get_server_name().$globals['base_url'].'rss2.php?status=queued" />'."\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('todas').'" href="http://'.get_server_name().$globals['base_url'].'rss2.php?status=all" />'."\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('comentarios').'" href="http://'.get_server_name().$globals['base_url'].'comments_rss2.php" />'."\n";

	if (empty($globals['favicon'])) $globals['favicon'] = 'img/favicons/favicon4.ico';
	echo '<link rel="icon" href="'.$globals['base_url'].$globals['favicon'].'" type="image/x-icon"/>' . "\n";

	if(!empty($globals['link_id'])) {
		// Pingback autodiscovery
		// http://www.hixie.ch/specs/pingback/pingback
		echo '<link rel="pingback" href="http://' . get_server_name() . $globals['base_url'] . 'xmlrpc.php"/>'."\n";
	}

	do_js_includes();

	echo '</head>' . "\n";
	echo "<body id=\"$id\" ". $globals['body_args']. ">\n";
	echo '<div id="logo">'  . "\n";
	echo '<a href="'.$globals['base_url'].'" title="la elefanta Eli"><img src="'.$globals['base_url'].'img/es/logo02.png" alt="logo menéame" /></a>';
	echo '</div>'  . "\n";

	echo '<div id="header">' . "\n";
	echo '<ul>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'telnet.php"  title="'._('es la fisgona, pero más segura para el trabajo').'">'. _('¡la jefa!') . '</a></li>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'faq-'.$dblang.'.php">' . _('acerca de menéame').'</a></li>' . "\n";
	if ($title != "login") {
		if($current_user->authenticated) {
	  		echo '<li><a href="'.$globals['base_url'].'login.php?op=logout&amp;return='.urlencode($_SERVER['REQUEST_URI']).'">' . _('cerrar sesión') . '</a></li>' . "\n";
  			echo '<li><a href="'.get_user_uri($current_user->user_login).'">' . _('perfil de') . ' ' . $current_user->user_login . '</a></li>' . "\n";
		} else {
  			echo '<li><a href="'.$globals['base_url'].'register.php">' . _('registrarse') . '</a></li>' . "\n";
  			echo '<li><a href="'.$globals['base_url'].'login.php?return='.urlencode($_SERVER['REQUEST_URI']).'">' . _('login') . '</a></li>' . "\n";
		}
	}

	// Main search form
	echo '<li>' . "\n";
	echo '<form action="'.$globals['base_url'].'" method="get">' . "\n";
	if (!empty($_REQUEST['search'])) {
		echo '<input type="text" name="search" value="'.htmlspecialchars(strip_tags($_REQUEST['search'])).'" />' . "\n";
	} else {
		echo '<input name="search" value="'._('buscar...').'" type="text" onblur="if(this.value==\'\') this.value=\''._('buscar...').'\';" onfocus="if(this.value==\''._('buscar...').'\') this.value=\'\';"/>' . "\n";
	}
	echo '<input class="searchIcon" type="image" align="top" value="buscar" alt="buscar" src="'.$globals['base_url'].'img/common/search-01.gif" />' . "\n";
	echo '</form>' . "\n";
	echo '</li>' . "\n";
	// form
	echo '</ul>' . "\n";
	echo '<span class="header-left">&nbsp;</span>' . "\n";
	echo '</div>' . "\n";
}

function do_css_includes() {
	global $globals;

	if ($globals['css_main']) {
		echo '<style type="text/css" media="screen">@import "'.$globals['base_url'].$globals['css_main'].'";</style>' . "\n";
	}
	if ($globals['css_color']) {
		echo '<style type="text/css" media="screen">@import "'.$globals['base_url'].$globals['css_color'].'";</style>' . "\n";
	}
	foreach ($globals['extra_css'] as $css) {
		echo '<style type="text/css" media="screen">@import "'.$globals['base_url'].'css/'.$css.'";</style>' . "\n";
	}

}

function do_js_includes() {
	global $globals;

	echo '<script type="text/javascript">var base_url="'.$globals['base_url'].'";</script>'."\n";
	// Cache for Ajax
	echo '<script src="'.$globals['base_url'].'js/jquery.pack.js" type="text/javascript"></script>' . "\n";
	echo '<script src="'.$globals['base_url'].'js/jsoc-0.12.0.js" type="text/javascript"></script>' . "\n";
	echo '<script src="'.$globals['base_url'].'js/general06.js" type="text/javascript"></script>' . "\n";
	do_js_from_array($globals['extra_js']);
}

function do_js_from_array($array) {
	global $globals;

	foreach ($array as $js) {
		if (preg_match('/^http/', $js)) {
			echo '<script src="'.$js.'" type="text/javascript"></script>' . "\n";
		} else {
			echo '<script src="'.$globals['base_url'].'js/'.$js.'" type="text/javascript"></script>' . "\n";
		}
	}
}

function do_footer($credits = true) {
	global $globals;

	if($credits) @do_credits();
	do_js_from_array($globals['post_js']);

	// warn warn warn 
	// dont do stats of password recovering pages
	@include('ads/statcounter.inc');
	@include('ads/analytics-01.inc');

	echo "</body></html><!--html1:do_footer-->\n";
}

function do_sidebar($do_vert_bars = true) {
	global $db, $dblang, $globals;
	echo '<div id="sidebar">';

	if(!empty($globals['link_id'])) {
		do_mnu_faq('story');
	} else {
		do_mnu_faq('home');
	}

	do_mnu_submit();
	do_mnu_sneak();
	do_mnu_geovision();
	do_mnu_notame();

	// don't show every box if it's a search
	if (!isset($_REQUEST['search'])) {
		do_mnu_meneria();
		if($do_vert_bars) {
			do_vertical_tags();
			do_best_comments();
			//do_best_posts();
		}
	}
	do_mnu_rss();
	do_mnu_tools();
	do_mnu_menedising();
	do_mnu_seguiment_ext();
	do_mnu_bugs();
	do_banner_left_down();
	echo '</div><!--html1:do_sidebar-->' . "\n";
}

function do_tags_comments() {
	global $globals;
	do_vertical_tags();
	do_best_comments();
}

// menu items

function do_mnu_faq($whichpage) {
	global $dblang, $globals, $current_user;

	if ($current_user->user_id > 0) return; // Don't shpw FAQ if it's a registered user

	echo '<div class="mnu-faq">' . "\n";

	@include mnminclude.'ads/adhere.inc';

	switch ($whichpage) {
		case 'home':
			echo '<strong>' . _("menéame"). '</strong>' . "\n";
			echo _("es un sistema de promoción de noticias...").' <a href="'.$globals['base_url'].'faq-'.$dblang.'.php">'. _("leer más") . '</a>.' . "\n";
			break;
		case 'story':
			echo '<strong>' . _("menéame"). '</strong>' . "\n";
			echo _("te ofrece esta noticia...").' <a href="'.$globals['base_url'].'faq-'.$dblang.'.php">'. _("¿qué es menéame?") . '</a>' . "\n";
			break;
		case 'shakeit':
			echo 'las noticias de esta p&aacute;gina con suficientes votos pasar&aacute;n a portada. para votar pulsa en <strong>men&eacute;alo</strong>.';
			break;
		case 'cloud':
			echo 'las etiquetas <strong>más populares</strong> aparecen a la derecha <strong>con mayor tamaño</strong>';
			break;
		case 'sitescloud':
			echo 'los webs <strong>más populares</strong> aparecen a la derecha <strong>con mayor tamaño</strong>';
			break;
		case 'topstories':
			echo 'selecciona un período y aparecerán las noticias <strong>más populares</strong> del momento';
			break;
		case 'topcomments':
			echo 'estos son los comentarios más valorados durante las ultimas 24 horas';
			break;
		case 'topcommented':
			echo 'estos son los artículos con más comentarios. en el menú, debajo esta nota, puedes seleccionar el período que quieres ver.';
			break;

	}
	echo '</div>' . "\n";
	
}

function do_mnu_submit() {
	global $globals;
	echo '<div class="mnu-submit"><a href="'.$globals['base_url'].'submit.php">'._("enviar noticia").'</a></div>' . "\n";
}

function do_mnu_sneak() {
	global $globals;
	echo '<div class="mnu-sneak"><a href="'.$globals['base_url'].'sneak.php">'._("fisgona").'</a></div>' . "\n";
}

function do_mnu_notame() {
	global $globals;
	echo '<div class="mnu-notame"><a href="'.post_get_base_url().'">'._("nótame").'</a></div>' . "\n";
}

function do_mnu_geovision() {
	global $globals;
	echo '<div class="mnu-geovision"><a href="'.$globals['base_url'].'geovision.php">'._("geovisión").'</a></div>' . "\n";
}

function do_mnu_bugs() {
	echo '<ul class="mnu-bugs">' . "\n";
	if (preg_match('/meneame.net$/', get_server_name())) {
		echo '<li><a href="http://meneame.wikispaces.com/Bugs">'._("reportar errores").'</a></li>' . "\n";
		echo '<li><a href="http://meneame.net/libs/ads/legal-meneame.php#contact" title="'._("encontrarás la dirección en la página de información legal").'">'._("reportar abusos").'</a></li>' . "\n";

	} else {
		echo '<li><a href="#">'._("your bugs link here").'</a></li>' . "\n";
		echo '<li><a href="#">'._("your abuse link here").'</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
}

function do_mnu_menedising() {
	if (preg_match('/meneame.net$/', get_server_name())) {
		echo '<div class="mnu-menedising"><a href="http://meneame.wikispaces.com/menechandising">'._("menechandising").'</a></div>' . "\n";
	} else {
		echo '<div class="mnu-menedising"><a href="#">'._("merchandasing here").'</a></div>' . "\n";
	}
}

function do_mnu_seguiment_ext() {
	echo '<ul class="mnu-seguiment-ext">' . "\n";
	if (preg_match('/meneame.net$/', get_server_name())) {
		echo '<li><a href="http://meneame.jaiku.com">'._("seguimiento en Jaiku").'</a></li>' . "\n";
		echo '<li><a href="http://twitter.com/meneame_net">'._("seguimiento en Twitter").'</a></li>' . "\n";
	} else {
		echo '<li><a href="#">'._("here jaiku, twitter, etc").'</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
}

function do_mnu_meneria () {
	global $globals;
	echo '<ul class="mnu-meneria">' . "\n";
	echo '<li><a href="'.$globals['base_url'].'cloud.php">'._("nube de etiquetas").'</a></li>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'topstories.php">'._("más meneadas").'</a></li>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'topcommented.php">'._("más comentadas").'</a></li>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'topcomments.php">'._("mejores comentarios").'</a></li>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'sitescloud.php">'._("nube de webs").'</a></li>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'topusers.php">'._("usuarios").'</a></li>' . "\n";
	echo '<li><a href="http://mueveme.net" title="'._('para móviles').'">'._('muéveme').'</a></li>' . "\n";
	echo '</ul>' . "\n";
}

function do_mnu_tools () {
	global $dblang, $globals;
	echo '<ul class="mnu-tools">' . "\n";
	if (preg_match('/meneame.net$/', get_server_name())) {
		echo '<li><a href="'.$globals['base_url'].'faq-'.$dblang.'.php">' . _('faq').'</a></li>' . "\n";
		echo '<li><a href="http://meneame.wikispaces.com/Ayuda">'._("ayuda").'</a></li>' . "\n";
		echo '<li><a href="http://meneame.wikispaces.com">'._("wiki").'</a></li>' . "\n";
		echo '<li><a href="http://blog.meneame.net">'._("blog").'</a></li>' . "\n";
		echo '<li><a href="http://mueveme.net">'._("para móviles").'</a></li>' . "\n";
	} else {
		echo '<li><a href="#">'._("your tools here").'</a></li>' . "\n";
	}
	echo '</ul>' . "\n";
}

function do_mnu_rss() {
	global $globals, $current_user;

	echo '<ul class="mnu-rss">' . "\n";

	if(!empty($_REQUEST['search'])) {
		$search =  htmlspecialchars($_REQUEST['search']);
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'rss2.php?search='.urlencode($search).'" rel="rss">'._("búsqueda").': <strong>'. htmlspecialchars($_REQUEST['search'])."</strong></a>\n";
		echo '</li>';
	}

	if(!empty($globals['category_name'])) {
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'rss2.php?status=all&amp;category='.$globals['category_id'].'" rel="rss">'._("rss categoría").': <strong>'.$globals['category_name']."</strong></a>\n";
		echo '</li>';
	}

	echo '<li>';
	echo '<a href="'.$globals['base_url'].'rss2.php" rel="rss">'._('rss publicadas').'</a>';
	echo '</li>' . "\n";
	
	echo '<li>';
	echo '<a href="'.$globals['base_url'].'rss2.php?status=queued" rel="rss">'._('rss en cola').'</a>';
	echo '</li>' . "\n";

	echo '<li>';
	echo '<a href="'.$globals['base_url'].'rss2.php?status=all" rel="rss">'._('rss todas').'</a>';
	echo '</li>' . "\n";

	if(!empty($globals['link_id'])) {
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'comments_rss2.php?id='.$globals['link_id'].'" rel="rss">'._('rss comentarios <strong>de esta noticia</strong>').'</a>';
		echo '</li>' . "\n";
	}

	if($current_user->user_id > 0) {
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'comments_rss2.php?conversation_id='.$current_user->user_id.'" rel="rss" title="'._('comentarios de las noticias donde has comentado').'">'._('rss mis conversaciones').'</a>';
		echo '</li>' . "\n";
		echo '<li>';
		echo '<a href="'.$globals['base_url'].'comments_rss2.php?author_id='.$current_user->user_id.'" rel="rss">'._('rss comentarios (mis noticias)').'</a>';
		echo '</li>' . "\n";
	}

	echo '<li>';
	echo '<a href="'.$globals['base_url'].'comments_rss2.php" rel="rss">'._('rss comentarios (todos)').'</a>';
	echo '</li>' . "\n";

	if(empty($globals['link_id'])) { // Netvibes. In homepage only.
		echo '<li class="mnu-rss-external">';
		echo '<a href="http://www.netvibes.com/subscribe.php?preconfig=7cec38e5bac4adc3608f68e8603bb3c3" title="Añadir a Netvibes"><img src="http://www.netvibes.com/img/add2netvibes.gif" width="91" height="17" border="0" alt="Añadir a Netvibes"/></a>';
		echo '</li>';
	}

	echo '</ul>' . "\n";
}

function do_mnu_categories_horizontal($what_cat_id) {
	
	// $what_cat_type:
	//	index: from index.php
	// 	shakeit: from shakeit.php

	global $db, $dblang, $globals;

	/*
	if (!$globals['meta_current'] > 0) 
		return;
	*/

	echo '<div class="catsub-block">' . "\n";
	echo '<ul>' . "\n";

	$query=preg_replace('/category=[0-9]*/', '', $_SERVER['QUERY_STRING']);
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
// 	echo '<br style="clear: both;" />' . "\n";
	echo '</div><!--html1:do_mnu_categories_horizontal-->' . "\n";

}

function do_mnu_categories($what_cat_type, $what_cat_id) {
	
	// $what_cat_type:
	//	index: from index.php
	// 	shakeit: from shakeit.php

	global $db, $dblang, $globals;

	// Categories Box

	// change class id for shakeit page
	if ($what_cat_type == 'shakeit') 
		$categorylist_class = 'column-one-list';
	else 
		$categorylist_class = 'column-list';
	echo '<div class="'.$categorylist_class.'">' . "\n";
	
	echo '<ul>' . "\n";


	$query=preg_replace('/category=[0-9]*/', '', $_SERVER['QUERY_STRING']);
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
		foreach ($categories as $category) {
			if($category->category_id == $what_cat_id) {
				$globals['category_id'] = $category->category_id;
				$globals['category_name'] = $category->category_name;
				$thiscat = ' class="thiscat"';
			} else {
				$thiscat = '';
			}

			echo '<li'.$thiscat.'><a href="'.$base_url.'?category='.$category->category_id.$query.'">';
			echo _($category->category_name);
			echo "</a></li>\n";
		}
	}

	echo '</ul>';
	echo '<br style="clear: both;" />' . "\n";
	echo '</div><!--html1:do_mnu_categories-->' . "\n";

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

	// pager style == "margin": notices. with margin for meneos box.
	//             == ''      : rest of pages. no margin.

	global $db;

	$index_limit = 10;

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
	// Adsense
	do_pager_ads();

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
	$metas = $db->get_results("SELECT category_id, category_name FROM categories WHERE category_parent = 0 ORDER BY category_id ASC");
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

function do_vertical_tags() {
	global $db, $globals, $dblang;

	if (!empty($globals['tag_status'])) {
		$status = '= "'. $globals['tag_status']. '"';
	} else {
		$status = "!= 'discarded'";
	}
	if(!empty($globals['meta_categories'])) {
		$meta_cond = 'and link_category in ('.$globals['meta_categories'].')';
	}

	if(memcache_mprint('tags'.$status.$meta_cond)) return;

	$min_pts = 8;
	$max_pts = 18;
	$line_height = $max_pts * 0.75;

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 172800); // 48 hours
	$from_where = "FROM tags, links WHERE tag_lang='$dblang' and tag_date > '$min_date' and link_id = tag_link_id and link_status $status $meta_cond GROUP BY tag_words";
	$max = max($db->get_var("select count(*) as words $from_where order by words desc limit 1"), 3);
	$coef = ($max_pts - $min_pts)/($max-1);

	$res = $db->get_results("select tag_words, count(*) as count $from_where order by count desc limit 30");
	if ($res) {
		$output = '<div class="vertical-box center">';
		$output .= '<h4><a href="'.$globals['base_url'].'cloud.php">'._('etiquetas').'</a></h4>'."\n";
		foreach ($res as $item) {
			$words[$item->tag_words] = $item->count;
		}
		ksort($words);
		foreach ($words as $word => $count) {
			$size = round($min_pts + ($count-1)*$coef, 1);
			$output .= '<a style="font-size: '.$size.'pt" href="'.$globals['base_url'].'?search=tag:'.urlencode($word).'">'.$word.'</a>  ';
		}
		$output .= '</div>';
		echo $output;
		memcache_madd('tags'.$status.$meta_cond, $output, 600);
	}
}

function do_last_comments() {
	global $db, $globals, $dblang;
	$foo_link = new Link();

	$res = $db->get_results("select comment_id, comment_order, user_login, link_id, link_uri, link_title, link_comments from comments, links, users where comment_link_id = link_id and comment_user_id = user_id order by comment_date desc limit 10");
	if ($res) {
		echo '<div class="vertical-box">';
		echo '<h4>' . _('últimos comentarios'). '</h4><ul>';
		foreach ($res as $comment) {
			$foo_link->uri = $comment->link_uri;
			$link = $foo_link->get_permalink().get_comment_page_suffix($globals['comments_page_size'], $comment->comment_order, $comment->link_comments).'#comment-'.$comment->comment_order;
			echo '<li>'.$comment->user_login.' '._('en').' <a  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_comment_tooltip.php\', \''.$comment->comment_id.'\', 10000);" href="'.$link.'">'.$comment->link_title.'</a></li>'."\n";
		}
		echo '</ul></div>';
	}
}

function do_last_posts() {
	global $db, $globals, $dblang;

	$res = $db->get_results("select post_id, post_content, user_login from posts, users where post_user_id = user_id order by post_date desc limit 10");
	if ($res) {
		echo '<div class="vertical-box">';
		echo '<h4><a href="'.post_get_base_url().'">' . _('últimos nótame'). '</a></h4><ul>';
		foreach ($res as $post) {
			echo '<li>'.$post->user_login.': <a  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_post_tooltip.php\', \''.$post->post_id.'\', 10000);" href="'.post_get_base_url($post->user_login).'/'.$post->post_id.'">'.text_to_summary($post->post_content, 50).'</a></li>'."\n";
		}
		echo '</ul></div>';
	}
}

function do_best_comments() {
	global $db, $globals, $dblang;
	$foo_link = new Link();

	if(memcache_mprint('best_comments')) return;

	$min_date = date("Y-m-d H:i:00", $globals['now'] - 22000); // about 6 hours
	$res = $db->get_results("select comment_id, comment_order, user_login, link_id, link_uri, link_title, link_comments from comments, links, users  where comment_date > '$min_date' and comment_karma > 10 and comment_link_id = link_id and comment_user_id = user_id order by comment_karma desc limit 12");
	if ($res) {
		$output = '<div class="vertical-box">';
		$output .= '<h4><a href="'.$globals['base_url'].'topcomments.php">'._('¿mejores? comentarios').'</a></h4><ul>'."\n";
		foreach ($res as $comment) {
			$foo_link->uri = $comment->link_uri;
			$link = $foo_link->get_permalink().get_comment_page_suffix($globals['comments_page_size'], $comment->comment_order, $comment->link_comments).'#comment-'.$comment->comment_order;
			$output .= '<li>'.$comment->user_login.' '._('en').' <a  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_comment_tooltip.php\', \''.$comment->comment_id.'\', 10000);" href="'.$link.'">'.$comment->link_title.'</a></li>'."\n";
		}
		$output .= '</ul></div>';
		echo $output;
		memcache_madd('best_comments', $output, 600);
	}
}

function do_best_posts() {
	global $db, $globals, $dblang;

	$min_date = date("Y-m-d H:00:00", $globals['now'] - 22000); // about 6 hours
	$res = $db->get_results("select post_id, post_content, user_login from posts, users where post_date > '$min_date' and  post_user_id = user_id order by post_karma desc limit 10");
	if ($res) {
		echo '<div class="vertical-box">';
		echo '<h4><a href="'.post_get_base_url('_best').'">' . _('¿mejores? notas'). '</a></h4><ul>';
		foreach ($res as $post) {
			echo '<li>'.$post->user_login.': <a  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_post_tooltip.php\', \''.$post->post_id.'\', 10000);" href="'.post_get_base_url($post->user_login).'/'.$post->post_id.'">'.text_to_summary($post->post_content, 50).'</a></li>'."\n";
		}
		echo '</ul></div>';
	}
}

?>
