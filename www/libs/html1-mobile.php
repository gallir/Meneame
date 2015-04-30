<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


@include mnminclude.'ads-credits-functions.php';

// Warning, it redirects to the content of the variable
if (!empty($globals['lounge'])) {
	header('Location: http://'.get_server_name().$globals['base_url'].$globals['lounge']);
	die;
}

$globals['extra_js'] = Array();
$globals['extra_css'] = Array();

function do_tabs($tab_name, $tab_selected = false, $extra_tab = false) {
	global $globals;

	$reload_text = _('recargar');
	$active = ' class="tabmain-this"';

	if ($tab_name == "main" ) {
		$items = array(
			array('url' => '', 'name' => 'published', 'title' => _('portada'), 'rel'=>true),
			array('url' => 'popular', 'name' => 'popular', 'title' => _('populares'), 'rel'=>true),
			array('url' => 'queue', 'name' => 'shakeit', 'title' => _('pendientes'), 'rel'=>true),
		);
		if ($extra_tab) {
			if ($globals['link_permalink']) $url = $globals['link_permalink'];
			else $url = __($globals['uri']);
			$items[] = array('url' => $url, 'name' => $tab_selected, 'title' => $tab_selected);
		}
		$tabname = 'tabmain';
	}

	$vars = compact('items', 'reload_text', 'tab_selected', 'tabname', 'active');
	return Haanga::Load('mobile/do_tabs.html', $vars);
}

function do_header($title, $id='home') {
	global $current_user, $dblang, $globals;

	check_auth_page();
	header('Content-type: text/html; charset=utf-8');
	header('X-Frame-Options: SAMEORIGIN');
	http_cache();
	$globals['security_key'] = get_security_key();
	setcookie('k', $globals['security_key'], 0, $globals['base_url']);

	$vars = compact('title', 'id');

	return Haanga::Load("mobile/header.html", $vars);
}

function do_footer($credits = true) {
	global $globals;

	$vars = compact('credits');
	return Haanga::Load('mobile/footer.html', $vars);
}

function do_footer_menu() {
	global $globals, $current_user;

}

function force_authentication() {
	global $current_user, $globals;

	if(!$current_user->authenticated) {
		header('Location: '.$globals['base_url'].'login?return='.$globals['uri']);
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

