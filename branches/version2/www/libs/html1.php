<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


@include('ads-credits-functions.php');

// Warning, it redirects to the content of the variable
if (!empty($globals['lounge'])) {
	header('Location: http://'.get_server_name().$globals['base_url'].$globals['lounge']);
	die;
}

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
			echo '<li><a '.$active.' href="'.$globals['base_url'].'" title="'.$reload_text.'">'._('portada').'&nbsp;&nbsp;&nbsp;'.$reload_icon.'</a></li>' . "\n";
		} else {
			echo '<li><a  href="'.$globals['base_url'].'">'._('portada').'</a></li>' . "\n";
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
			echo '<li><a '.$active.' href="'.htmlentities($_SERVER['REQUEST_URI']).'" title="'.$reload_text.'">'.$tab_selected.'&nbsp;&nbsp;&nbsp;'.$reload_icon.'</a></li>' . "\n";
		}
		echo '</ul>' . "\n";
	}
}

function do_header($title, $id='home') {
	global $current_user, $dblang, $globals;

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
	//echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'" lang="'.$dblang.'">' . "\n";
	echo '<head>' . "\n";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
	echo "<title>"._($title)." // men&eacute;ame</title>\n";
	echo '<meta name="generator" content="meneame" />' . "\n";
	echo '<meta name="keywords" content="'.$globals['tags'].'" />' . "\n";
	echo '<link rel="microsummary" type="application/x.microsummary+xml" href="'.$globals['base_url'].'microsummary.xml" />' . "\n";
	echo '<style type="text/css" media="screen">@import "'.$globals['base_url'].'css/es/mnm21.css";</style>' . "\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('publicadas').'" href="http://'.get_server_name().$globals['base_url'].'rss2.php" />'."\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('pendientes').'" href="http://'.get_server_name().$globals['base_url'].'rss2.php?status=queued" />'."\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('todas').'" href="http://'.get_server_name().$globals['base_url'].'rss2.php?status=all" />'."\n";
	echo '<link rel="alternate" type="application/rss+xml" title="'._('comentarios').'" href="http://'.get_server_name().$globals['base_url'].'comments_rss2.php" />'."\n";

	if (empty($globals['favicon'])) $globals['favicon'] = 'img/favicons/favicon4.ico';
	echo '<link rel="icon" href="'.$globals['base_url'].$globals['favicon'].'" type="image/x-icon" />' . "\n";

	do_js_includes();

	echo '</head>' . "\n";
	echo "<body id=\"$id\" ". $globals['body-args']. ">\n";
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
	echo '</form>' . "\n";
	echo '</li>' . "\n";
	// form
	echo '</ul>' . "\n";
	echo '<span class="header-left">&nbsp;</span>' . "\n";
	echo '</div>' . "\n";
}

function do_js_includes() {
	global $globals;

	echo '<script type="text/javascript">var base_url="'.$globals['base_url'].'";</script>';
	// Cache for Ajax
	echo '<script src="'.$globals['base_url'].'js/jsoc-0.11.0.js" type="text/javascript"></script>' . "\n";
	echo '<script src="'.$globals['base_url'].'js/general04.js" type="text/javascript"></script>' . "\n";
}

function do_footer($credits = true) {
	if($credits) @do_credits();

	// warn warn warn 
	// dont do stats of password recovering pages
	@include('ads/statcounter.inc');
	@include('ads/analytics-01.inc');

	echo "</body></html><!--html1:do_footer-->\n";
}

function do_sidebar() {
	global $db, $dblang, $globals;
	echo '<div id="sidebar">';

	if(!empty($globals['link_id'])) {
		$doing_story=true;
		do_mnu_faq('story');
		do_mnu_trackbacks();
	} else {
		$doing_story=false;
		do_mnu_faq('home');
	}

	do_mnu_submit();
	do_mnu_sneak();

	if(empty($globals['link_id'])) {
		do_mnu_categories('index', $_REQUEST['category']);
	}
	do_mnu_meneria();
	do_mnu_menedising();
	do_mnu_tools();
	do_mnu_bugs();
	do_mnu_rss();
	echo '</div><!--html1:do_sidebar-->' . "\n";
}

function do_rightbar() {
	require_once(mnminclude.'html-utils.php');
	echo "<div id='rightbar'>\n";
	do_banner_right();
	do_vertical_tags();
	do_best_comments();
	//do_last_comments();
	echo "</div>";
}

// menu items

function do_mnu_faq($whichpage) {
	global $dblang, $globals;
	echo '<div class="mnu-faq">' . "\n";
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

function do_mnu_bugs() {
	echo '<div class="mnu-bugs"><a href="http://meneame.wikispaces.com/Bugs">'._("reportar errores").'</a></div>' . "\n";
}

function do_mnu_menedising() {
	echo '<div class="mnu-menedising"><a href="http://meneame.wikispaces.com/menechandising">'._("menechandising").'</a></div>' . "\n";
}

function do_mnu_meneria () {
	global $globals;
	echo '<ul class="mnu-meneria">' . "\n";
	echo '<li><a href="'.$globals['base_url'].'cloud.php">'._("nube de etiquetas").'</a></li>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'topstories.php">'._("más meneadas").'</a></li>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'topcommented.php">'._("más comentadas").'</a></li>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'topcomments.php">'._("mejores comentarios").'</a></li>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'sitescloud.php">'._("webs").'</a></li>' . "\n";
	echo '<li><a href="'.$globals['base_url'].'topusers.php">'._("usuarios").'</a></li>' . "\n";
	echo '</ul>' . "\n";
}

function do_mnu_tools () {
	global $dblang, $globals;
	echo '<ul class="mnu-tools">' . "\n";
	echo '<li><a href="'.$globals['base_url'].'faq-'.$dblang.'.php">' . _('faq').'</a></li>' . "\n";
	echo '<li><a href="http://meneame.wikispaces.com/Ayuda">'._("ayuda").'</a></li>' . "\n";
	echo '<li><a href="http://meneame.wikispaces.com">'._("wiki").'</a></li>' . "\n";
	echo '<li><a href="http://blog.meneame.net">'._("blog").'</a></li>' . "\n";
	echo '<li><a href="http://mueveme.net">'._("para móviles").'</a></li>' . "\n";
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

	echo '</ul>' . "\n";
}

function do_mnu_trackbacks() {
	global $db, $globals;

	echo '<ul class="mnu-trackback">' . "\n";

	echo '<li><a href="'.$globals['link']->get_trackback().'" title="'._('URI para trackbacks').'">trackback <img src="'.$globals['base_url'].'img/common/permalink.gif" alt="'._('enlace trackback').'" width="16" height="9"/></a></li>' . "\n";

	echo '<li><ul class="mnu-trackback-list">' . "\n";
	$id=$globals['link_id'];
	$trackbacks = $db->get_col("SELECT trackback_id FROM trackbacks WHERE trackback_link_id=$id AND trackback_type='in' ORDER BY trackback_date DESC");	
	if ($trackbacks) {
		require_once(mnminclude.'trackback.php');
		$trackback = new Trackback;
		foreach($trackbacks as $trackback_id) {
			$trackback->id=$trackback_id;
			$trackback->read();
			echo '<li class="mnu-trackback-entry"><a href="'.$trackback->url.'" title="'.$trackback->content.'">'.$trackback->title.'</a></li>' . "\n";
		}
	}
// 	echo '<li class="mnu-trackback-entry"><a href="#">prova</a></li>';

	echo '<li class="mnu-trackback-technorati"><a href="http://technorati.com/search/'.urlencode($globals['link']->get_permalink()).'">'._('según Technorati').'</a></li>' . "\n";
	echo '<li class="mnu-trackback-google"><a href="http://blogsearch.google.com/blogsearch?hl=es&amp;q=link%3A'.urlencode($globals['link']->get_permalink()).'">'._('según Google').'</a></li>' . "\n";

	echo '<li class="mnu-trackback-askcom"><a href="http://es.ask.com/blogsearch?q='.urlencode($globals['link']->get_permalink()).'&amp;t=a&amp;search=Buscar&amp;qsrc=2101&amp;bql=any">'._('según Ask.com').'</a></li>' . "\n";


	echo "</ul></li>\n";

	echo "</ul>\n";
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

	$categories = $db->get_results("SELECT category_id, category_name FROM categories WHERE category_lang='$dblang' ORDER BY category_name ASC");

	$query=preg_replace('/category=[0-9]*/', '', $_SERVER['QUERY_STRING']);
	// Always return to page 1
	$query=preg_replace('/page=[0-9]*/', '', $query);
	$query=preg_replace('/^&*(.*)&*$/', "$1", $query);
	if(!empty($query)) {
		$query = htmlspecialchars($query);
		$query = "&amp;$query";
	}

	// draw first category: all categories
	if (empty($what_cat_id)) 
		$thiscat = ' class="thiscat"';
	else 
		$thiscat = '';
	if (preg_match('/index\.php/', $_SERVER['PHP_SELF'])) $base_url = $globals['base_url'];
	else $base_url = htmlspecialchars($_SERVER['PHP_SELF']);
	echo '<li'.$thiscat.'><a href="'.$base_url.'?'.$query.'">'._('_todas');
	//if ($what_cat_type == 'shakeit') echo '&nbsp;('.$queued_count.')';
	echo '</a></li>' . "\n";

	// draw categories
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
		//if ($what_cat_type == 'shakeit') echo '&nbsp;('.$category->count.')';
		echo "</a></li>\n";

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
?>
