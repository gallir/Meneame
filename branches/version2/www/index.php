<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');

// Manage "search" url and redirections accordingly
if (!empty($globals['base_search_url'])) {
	if (!empty($_SERVER['PATH_INFO']) ) {
		$q = preg_quote($globals['base_url'].$globals['base_search_url']);
		if(preg_match("{^$q}", $_SERVER['SCRIPT_URL'])) {
			$_REQUEST['search'] = urldecode(substr($_SERVER['PATH_INFO'], 1));
		}
	} elseif (!empty($_REQUEST['search'])) {
		$_REQUEST['search'] = substr(trim(strip_tags($_REQUEST['search'])), 0, 300);
		if (!preg_match('/\//', $_REQUEST['search']) ) {  // Freaking Apache rewrite that translate //+ to just one /
														// for example "http://" is converted to http:/
														// also it cheats the paht_info and redirections, so don't redirect
			header('Location: http://'. get_server_name().$globals['base_url'].$globals['base_search_url'].urlencode($_REQUEST['search']));
			die;
		}
	} elseif (isset($_REQUEST['search'])) {
		header('Location: http://'. get_server_name().$globals['base_url']);
		die;
	}
}



$page_size = 20;
$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;

$search = get_search_clause();
// Search all if it's a search
$cat=check_integer('category');

if($search) {
	$search_txt = htmlspecialchars($_REQUEST['search']);
	$from_where = "FROM links WHERE ";
	if($cat) {
		$from_where .= " link_category=$cat AND ";
	}
	do_header(_('búsqueda de'). '"'.$search_txt.'"');
	do_banner_top();
	echo '<div id="'.$globals['css_container'].'">'."\n";
	echo '<div id="contents">';
	do_tabs('main',_('búsqueda'), htmlentities($_SERVER['REQUEST_URI']));
	echo '<div class="topheading"><h2>'._('resultados de buscar'). ' "'.$search_txt.'" </h2></div>';
	$from_where .= $search;
	if ($_REQUEST['tag'] == 'true' || $_REQUEST['date']  == 'true' ) {
		$order_by = ' ORDER BY link_date DESC ';
	} else {
		$order_by = '';
	}
} else {
	do_header(_('últimas publicadas'));
	do_banner_top();
	echo '<div id="'.$globals['css_container'].'">'."\n";
	echo '<div id="contents">'."\n";
	do_tabs('main','published');

	if ($globals['meta_current'] > 0) {
		$from_where = "FROM links WHERE link_status='published' and link_category in (".$globals['meta_categories'].") ";
		print_index_tabs(); // No other view
	} elseif ($current_user->user_id > 0) { // Check authenticated users
		// Check the personalized views
		switch ($globals['meta']) {
			case '_friends':
				$from_time = '"'.date("Y-m-d H:00:00", time() - 86400*4).'"';
				$from_where = "FROM links, friends WHERE link_date >  $from_time and link_status='published' and friend_type='manual' and friend_from = $current_user->user_id and friend_to=link_author";
				print_index_tabs(1); // Friends
			break;
			default:
				print_index_tabs(0); // All
				$from_where = "FROM links WHERE link_status='published' ";
		}
	} else {
		print_index_tabs(0); // No other view
		$from_where = "FROM links WHERE link_status='published' ";
	}

	if($cat) {
		$from_where .= " AND link_category=$cat ";
	}
	$order_by = " ORDER BY link_published_date DESC ";
}

$link = new Link;
$rows = $db->get_var("SELECT count(*) $from_where");
$links = $db->get_col("SELECT link_id $from_where $order_by LIMIT $offset,$page_size");
if ($links) {
	foreach($links as $link_id) {
		$link->id=$link_id;
		$link->read();
		$link->print_summary();
	}
}

do_pages($rows, $page_size);
echo '</div> <!--index.php-->';
do_sidebar();
$globals['tag_status'] = 'published';
do_rightbar();
do_footer();

function print_index_tabs($option=-1) {
	global $globals, $db, $current_user;

	$active = array();
	if ($option >= 0)
		$active[$option] = 'class="tabsub-this"';

	echo '<ul class="tabsub-shakeit">'."\n";
	echo '<li><a '.$active[0].' href="'.$globals['base_url'].$globals['meta_skip'].'">'._('todas'). '</a></li>'."\n";
	// Do metacategories list
	$metas = $db->get_results("SELECT category_id, category_name, category_uri FROM categories WHERE category_parent = 0 ORDER BY category_id ASC");
	if ($metas) {
		foreach ($metas as $meta) {
			if ($meta->category_id == $globals['meta_current']) $active_meta = 'class="tabsub-this"';
			else $active_meta = '';
			echo '<li><a '.$active_meta.' href="'.$globals['base_url'].'?meta='.$meta->category_uri.'">'.$meta->category_name. '</a></li>'."\n";
		}
	}
	if ($current_user->user_id > 0) {
		echo '<li><a '.$active[1].' href="'.$globals['base_url'].'?meta=_friends">'._('amigos'). '</a></li>'."\n";
	}
	meta_teaser_item();
	echo '</ul>'."\n";
}

?>
