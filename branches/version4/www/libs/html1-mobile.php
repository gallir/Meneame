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

	check_auth_page();
	header('Content-type: text/html; charset=utf-8');
	http_cache();

    $vars = compact('title', 'id');

    return Haanga::Load("mobile/header.html", $vars);
}

function do_footer($credits = true) {
	global $globals;

    $globals['security_key'] = get_security_key();
    $vars = compact('credits');
    return Haanga::Load('mobile/footer.html', $vars);
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
	echo '.errt { text-align:center; padding-top:20px; font-size:150%; color:#FF6400;}'."\n";
	echo '.errl { text-align:center; margin-top:20px; margin-bottom:20px; }'."\n";
	echo '-->'."\n";
	echo '</STYLE>'."\n";

	echo '<div class="errt">'.$mess.'<br />'."\n";
	if ($error) echo '('._('error').' '.$error.')</div>'."\n";

	do_footer_menu();
	do_footer();
	die;
}


?>
