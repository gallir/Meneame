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
include(mnminclude.'sphinx.php');

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

$_REQUEST['q'] = stripslashes($_REQUEST['q']);
$response = sphinx_get_search_link(false, $offset, $page_size);
$search_txt = htmlspecialchars($_REQUEST['q']);
do_header(_('búsqueda de'). ' "'.$search_txt.'"');
do_tabs('main',_('búsqueda'), htmlentities($_SERVER['REQUEST_URI']));

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_best_stories();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

echo '<div style="background:#FFE2C5;margin:10px 0 5px 86px;font-size:100%;text-align:right;padding:5px;">'._('búsqueda'). ': <strong>'.$search_txt.'</strong>';
if(!empty($_REQUEST['q'])) {
	echo '&nbsp;<a href="'.$globals['base_url'].'rss2.php?q='.urlencode($_REQUEST['q']).'" rel="rss"><img src="'.$globals['base_url'].'img/common/feed-icon-12x12.png" alt="rss2" height="12" width="12"  style="vertical-align:top"/></a>';
}
echo '&nbsp;&nbsp;&nbsp;'._('encontrados').': '.$response['rows'].', '._('tiempo total').': '.sprintf("%1.3f",$response['time']).' '._('segundos').'</div>';
$link = new Link;
if ($response['ids']) {
	$rows = min($response['rows'], 1000);
	foreach($response['ids'] as $link_id) {
		$link->id=$link_id;
		$link->read();
		$link->print_summary('full', $link->status == 'published' ? 100 : 20);
	}
}

do_pages($rows, $page_size);
echo '</div>';
do_footer();

?>
