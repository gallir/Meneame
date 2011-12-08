<?
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
	echo '<loc>http://'.get_server_name().$globals['base_url'].'sitemap.php?statics</loc>'."\n";
	echo '</sitemap>'."\n";

	echo '<sitemap>'."\n";
	echo '<loc>http://'.get_server_name().$globals['base_url'].'sitemap.php?last</loc>'."\n";
	echo '</sitemap>'."\n";

	/*
	* Simplified with last published in the previos lines
	$count = (int) Link::count('published');
	$indexes = ceil($count/$size);
	for ($i = 0; $i < $indexes; $i++) {
		echo '<sitemap>'."\n";
		echo '<loc>http://'.get_server_name().$globals['base_url'].'sitemap.php?page='.$i.'</loc>'."\n";
		echo '</sitemap>'."\n";
	}
	*/
	echo '</sitemapindex>'."\n";
}

function do_statics() {
	global $globals;

	$urls = Array('shakeit.php', 'sneak.php', 'geovision.php', 'notame/', 
			'cloud.php', 'topstories.php', 'topcommented.php', 
			'topcomments.php', 'sitescloud.php', 'topusers.php', 'faq-es.php');

	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
	// The index
	echo '<url>'."\n";
	echo '<loc>http://'.get_server_name().$globals['base_url'].'</loc>'."\n";
	echo '<priority>1.0</priority>'."\n";
	echo '</url>'."\n";
	// Secondary pages
	foreach ($urls as $url) {
		echo '<url>'."\n";
		echo '<loc>http://'.get_server_name().$globals['base_url'].$url.'</loc>'."\n";
		echo '<priority>0.8</priority>'."\n";
		echo '</url>'."\n";
	}
	echo '</urlset>'."\n";
}

function do_published($page) {
	global $globals, $index_size, $db;
	$start = $page * $index_size;

	$sql = "SELECT SQL_NO_CACHE link_uri from links where link_status='published' order by link_date asc limit $start, $index_size";
	$result = $db->get_col($sql);
	if (!$result) return;
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
	foreach ($result as $uri) {
		echo '<url>'."\n";
		echo '<loc>http://'.get_server_name().$globals['base_url'].$globals['base_story_url'].$uri.'</loc>'."\n";
		echo '</url>'."\n";
	}
	echo '</urlset>'."\n";
}

function do_last_published() {
	global $globals, $db;

	$sql = "SELECT SQL_NO_CACHE link_uri from links where link_status='published' and link_date > date_sub(now(), interval 60 day) order by link_date desc";
	$result = $db->get_col($sql);
	if (!$result) return;
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
	foreach ($result as $uri) {
		echo '<url>'."\n";
		echo '<loc>http://'.get_server_name().$globals['base_url'].$globals['base_story_url'].$uri.'</loc>'."\n";
		echo '</url>'."\n";
	}
	echo '</urlset>'."\n";
}
?>
