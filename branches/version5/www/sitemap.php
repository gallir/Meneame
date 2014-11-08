<?php
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');

$index_size = 1000;

header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

if (empty($_SERVER['QUERY_STRING'])) {
	do_master($index_size);
} else {
	if (isset($_REQUEST['statics'])) {
		do_statics();
	} elseif (isset($_REQUEST['last'])) {
		do_last_published();
	} else {
		$page = (int) $_REQUEST['page'];
		do_published($page);
	}
}

function do_master($size) {
	global $globals, $db;

	echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

	echo '<sitemap>'."\n";
	echo '<loc>'.$globals['scheme'].'//'.get_server_name().$globals['base_url'].'sitemap?statics</loc>'."\n";
	echo '</sitemap>'."\n";

	echo '<sitemap>'."\n";
	echo '<loc>'.$globals['scheme'].'//'.get_server_name().$globals['base_url'].'sitemap?last</loc>'."\n";
	echo '</sitemap>'."\n";

	echo '</sitemapindex>'."\n";
}

function do_statics() {
	global $globals;

	$urls = Array('queue', 'sneak', 'notame/', 
			'cloud', 'popular', 'top_commented', 
			'top_comments', 'top_users', 'legal', 'faq-es.php');

	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
	// The index
	echo '<url>'."\n";
	echo '<loc>'.$globals['scheme'].'//'.get_server_name().$globals['base_url'].'</loc>'."\n";
	echo '<priority>1.0</priority>'."\n";
	echo '</url>'."\n";
	// Secondary pages
	foreach ($urls as $url) {
		echo '<url>'."\n";
		echo '<loc>'.$globals['scheme'].'//'.get_server_name().$globals['base_url'].$url.'</loc>'."\n";
		echo '<priority>0.8</priority>'."\n";
		echo '</url>'."\n";
	}
	echo '</urlset>'."\n";
}

function do_published($page) {
	global $globals, $index_size, $db;
	$start = $page * $index_size;

	$sql = "SELECT SQL_NO_CACHE link_uri from links, sub_statuses where id = ".SitesMgr::my_id()." and link_id = link and status='published' order by date asc limit $start, $index_size";
	$result = $db->get_col($sql);
	if (!$result) return;
	if (isset($globals['canonical_server_name']) && ! empty($globals['canonical_server_name'])) {
		$server = $globals['canonical_server_name'];
	} else {
		$server = get_server_name();
	}
	echo '<urlset xmlns="'.$globals['scheme'].'//www.sitemaps.org/schemas/sitemap/0.9">'."\n";
	foreach ($result as $uri) {
		echo '<url>'."\n";
		echo '<loc>'.$globals['scheme'].'//'.$server.$globals['base_url'].'story/'.$uri.'</loc>'."\n";
		echo '</url>'."\n";
	}
	echo '</urlset>'."\n";
}

function do_last_published() {
	global $globals, $db;

	$sql = "SELECT SQL_NO_CACHE link_uri from links, sub_statuses where id = ".SitesMgr::my_id()." and link_id = link and status='published' and date > date_sub(now(), interval 60 day) order by date desc";
	$result = $db->get_col($sql);
	if (!$result) return;
	if (isset($globals['canonical_server_name']) && ! empty($globals['canonical_server_name'])) {
		$server = $globals['canonical_server_name'];
	} else {
		$server = get_server_name();
	}
	echo '<urlset xmlns="'.$globals['scheme'].'//www.sitemaps.org/schemas/sitemap/0.9">'."\n";
	foreach ($result as $uri) {
		echo '<url>'."\n";
		echo '<loc>'.$globals['scheme'].'//'.$server.$globals['base_url'].'story/'.$uri.'</loc>'."\n";
		echo '</url>'."\n";
	}
	echo '</urlset>'."\n";
}
?>
