<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'lucene.php');

// Manage "search" url and redirections accordingly
if (!empty($globals['base_search_url'])) {
	if (!empty($_SERVER['PATH_INFO']) ) {
		$q = preg_quote($globals['base_url'].$globals['base_search_url']);
		if(preg_match("{^$q}", $_SERVER['SCRIPT_URL'])) {
			$_REQUEST['q'] = urldecode(substr($_SERVER['PATH_INFO'], 1));
		}
	} elseif (!empty($_REQUEST['q'])) {
		$_REQUEST['q'] = substr(trim(strip_tags($_REQUEST['q'])), 0, 300);
		if (!preg_match('/\//', $_REQUEST['q']) ) {  // Freaking Apache rewrite that translate //+ to just one /
														// for example "http://" is converted to http:/
														// also it cheats the paht_info and redirections, so don't redirect
			header('Location: http://'. get_server_name().$globals['base_url'].$globals['base_search_url'].urlencode($_REQUEST['q']));
			die;
		}
	} elseif (isset($_REQUEST['q'])) {
		header('Location: http://'. get_server_name().$globals['base_url']);
		die;
	}
}


$page_size = 20;
$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;

$globals['noindex'] = true;

if ($_REQUEST['q']) {
	// Catch url searchs and search directly into the mysql db (it is indexed)
	if (preg_match('/^ *http[s]*:\/\/|^www\./', $_REQUEST['q'])) {
		$url = trim(strip_tags( $_REQUEST['q']));
		$url = $db->escape($url);
		$globals['rows'] = $db->get_var("select count(*) from links where link_url like '$url%'");
		$ids = $db->get_col("select link_id from links where link_url like '$url%' order by link_date desc limit $offset,$page_size");
	} else {
		$ids = lucene_get_search_link_ids(false, $offset, $page_size);
	}
}

$search_txt = htmlspecialchars($_REQUEST['q']);
do_header(_('búsqueda de'). ' "'.$search_txt.'"');
do_banner_top();

echo '<div id="container">'."\n";
do_sidebar();
echo '<div id="contents">';
do_tabs('main',_('búsqueda'), htmlentities($_SERVER['REQUEST_URI']));
echo '<div class="topheading"><h2>'._('resultados de buscar'). ' "'.$search_txt.'" </h2></div>';

$link = new Link;
if ($ids) {
	$rows = $globals['rows'];
	foreach($ids as $link_id) {
		$link->id=$link_id;
		$link->read();
		$link->print_summary();
	}
}

do_pages($rows, $page_size);
echo '</div>';
do_footer();

?>
