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

header('Content-type: text/html; charset=utf-8');
if ($current_user->user_id) {
	header('Cache-Control: private');
}

function do_tabs($tab_name, $tab_selected = false, $extra_tab = false) {
	global $globals;

	$reload_text = _('recargar');
	$active = ' class="tabmain-this"';

	if ($tab_name == "main" ) {
		echo '<ul class="tabmain">';

		// url with parameters?
		if (!empty($_SERVER['QUERY_STRING']))
			$query = "?".htmlentities($_SERVER['QUERY_STRING']);

		// START STANDARD TABS
		// First the standard and always present tabs
		// published tab
		if ($tab_selected == 'published') {
			echo '<li '.$active.'><a href="'.$globals['base_url'].'" title="'.$reload_text.'">'._('portada').'</a></li>';
		} else {
			echo '<li><a  href="'.$globals['base_url'].'">'._('portada').'</a></li>';
		}


		// Most voted
		if ($tab_selected == 'popular') {
			echo '<li '.$active.'><a href="'.$globals['base_url'].'topstories.php" title="'.$reload_text.'">'._('populares').'</a></li>';
		} else {
			echo '<li><a href="'.$globals['base_url'].'topstories.php">'._('populares').'</a></li>';
		}

		// shake it
		if ($tab_selected == 'shakeit') {
			echo '<li '.$active.'><a href="'.$globals['base_url'].'shakeit.php" title="'.$reload_text.'">'._('pendientes').'</a></li>';
		} else {
			echo '<li><a href="'.$globals['base_url'].'shakeit.php">'._('pendientes').'</a></li>';
		}
		// END STANDARD TABS

		//Extra tab
		if ($extra_tab) {
			if ($globals['link_permalink']) $url = $globals['link_permalink'];
			else $url = htmlentities($_SERVER['REQUEST_URI']);
			echo '<li '.$active.'><a href="'.$url.'" title="'.$reload_text.'">'.$tab_selected.'</a></li>';
		}
		echo '</ul>' . "\n";
		echo '<div style="clear:left"></div>'; // Some browsers wrap the tabs
	}
}

function do_header($title, $id='home') {
	global $current_user, $dblang, $globals;

	//echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . "\n";
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">' . "\n";
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'">' . "\n";
	echo '<head>' . "\n";
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
	echo '<meta name="ROBOTS" content="NOARCHIVE" />'."\n";
	echo '<meta name="viewport" content="width=320"/>' . "\n";
	echo "<title>$title</title>\n";

	do_css_includes();

	echo '<meta name="generator" content="meneame mobile" />' . "\n";
	if (!empty($globals['noindex'])) {
		echo '<meta name="robots" content="noindex,follow"/>' . "\n";
	}
	if (!empty($globals['tags'])) {
		echo '<meta name="keywords" content="'.$globals['tags'].'" />' . "\n";
	}
	if (empty($globals['favicon'])) $globals['favicon'] = 'img/favicons/favicon4.ico';
	echo '<link rel="icon" href="'.$globals['base_static'].$globals['favicon'].'" type="image/x-icon"/>' . "\n";
	echo '<link rel="apple-touch-icon" href="'.$globals['base_static'].'img/favicons/apple-touch-icon.png"/>' . "\n";

	do_js_includes();

	if ($globals['extra_head']) echo $globals['extra_head'];

	echo '</head>' . "\n";
	echo "<body id=\"$id\" ". $globals['body_args']. ">\n";

	echo '<div id="header">' . "\n";
	echo '<a href="'.$globals['base_url'].'" title="'._('inicio').'" id="logo">'._("menéame").'</a>'."\n";
	echo '<ul id="headtools">';

 	echo '<li><a href="'.$globals['base_url'].'search.php">'. _('buscar').'</a></li>';
	if($current_user->authenticated) {
  		echo '<li><a href="'.$globals['base_url'].'login.php?op=logout&amp;return='.urlencode($_SERVER['REQUEST_URI']).'">'. _('logout').'</a></li>';
 		echo '<li class="noborder"><a href="'.get_user_uri($current_user->user_login).'" title="'.$current_user->user_login.'"><img src="'.get_avatar_url($current_user->user_id, $current_user->user_avatar, 20).'" width="15" height="15" alt="'.$current_user->user_login.'"/></a></li>';
	} else {
  		echo '<li class="noborder"><a href="'.$globals['base_url'].'login.php?return='.urlencode($_SERVER['REQUEST_URI']).'">'. _('login').'</a></li>';
	}


	echo '</ul>' . "\n";
	echo '</div>' . "\n";
	echo '<div id="container">'."\n";
	do_banner_top_mobile();
}

function do_css_includes() {
	global $globals;

	if ($globals['css_main']) {
		echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$globals['base_static'].$globals['css_main'].'" />' . "\n";
	}
	if ($globals['css_color']) {
		echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$globals['base_static'].$globals['css_color'].'" />' . "\n";
	}
	foreach ($globals['extra_css'] as $css) {
		echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$globals['base_static'].'css/'.$css.'" />' . "\n";
	}
}

function do_js_includes() {
	global $globals;

	echo '<script type="text/javascript">'."\n";
	echo 'if(top.location != self.location)top.location = self.location;'."\n";
	echo 'var base_url="'.$globals['base_url'].'";'."\n";
	echo 'var mobile_version = true;'."\n";
	echo 'var base_static="'.$globals['base_static'].'";'."\n";
	echo 'var base_key="'.get_security_key().'";'."\n";
	echo '</script>'."\n";
	echo '<script src="'.$globals['base_static'].'js/mobile02.js" type="text/javascript"></script>' . "\n";
	do_js_from_array($globals['extra_js']);
	if ($globals['extra_js_text']) {
		echo '<script type="text/javascript">'."\n";
		echo $globals['extra_js_text']."\n";
		echo '</script>'."\n";
	}
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

	echo "</div>\n";
	if($credits) @do_credits_mobile();
	do_js_from_array($globals['post_js']);

	// warn warn warn 
	// dont do stats of password recovering pages
	@include('ads/stats-mobile.inc');
	printf("\n<!--Generated in %4.3f seconds-->\n", microtime(true) - $globals['start_time']);
	echo "</body></html>\n";
}

function do_footer_menu() {
	global $globals, $current_user;

}

function force_authentication() {
	global $current_user;

	if(!$current_user->authenticated) {
		header('Location: '.$globals['base_url'].'login.php?return='.$_SERVER['REQUEST_URI']);
		die;
	}
	return true;
}

function do_pages($total, $page_size=15) {
	global $db;

	if ($total < $page_size) return;

	$query=preg_replace('/page=[0-9]+/', '', $_SERVER['QUERY_STRING']);
	$query=preg_replace('/^&*(.*)&*$/', "$1", $query);
	if(!empty($query)) {
		$query = htmlspecialchars($query);
		$query = "&amp;$query";
	}
	
	$current = get_current_page();
	$total_pages=ceil($total/$page_size);

	echo '<div class="pages">';

	if($current==1) {
		echo '<span class="nextprev">&#171;</span>';
	} else {
		$i = $current-1;
		echo '<a href="?page='.$i.$query.'">&#171;</a>';
	}

	echo '<span class="current">'.$current.'</span>';
	if($current<$total_pages) {
		$i = $current+1;
		echo '<a href="?page='.$i.$query.'">&#187;</a>';
	} else {
		echo '<span class="nextprev">&#187;</span>';
	}
	echo "</div>\n";

}

?>
