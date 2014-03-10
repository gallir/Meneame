<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1-mobile.php');
include(mnminclude.'linkmobile.php');
include(mnminclude.'search.php');


$page_size = 10;
$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = false;

$globals['noindex'] = true;

$_REQUEST['q'] = trim(stripslashes($_REQUEST['q']));
$response = do_search(false, $offset, $page_size);
$search_txt = htmlspecialchars($_REQUEST['q']);
do_header(sprintf(_('búsqueda de «%s»'), $search_txt));
do_tabs('main',_('búsqueda'), htmlentities($_SERVER['REQUEST_URI']));

echo '<div id="newswrap">'."\n";

if(!empty($_REQUEST['q'])) {
	echo '<div style="background:#FFE2C5;margin: 4px; padding:4px;">'._('búsqueda'). ': <em>'.$search_txt.'</em>';
	echo '&nbsp;&nbsp;'._('encontrados').': '.$response['rows'].', '._('tiempo total').': '.sprintf("%1.3f",$response['time']).' '._('segundos').'</div>';
}

echo '<form action="'.$globals['base_url'].'search.php" method="get">' . "\n";
echo '<fieldset>';
echo '<label for="search">'. _('búsqueda').'</label>'."\n";
echo '<div><input type="text" name="q" id="search" value="'.htmlspecialchars(strip_tags($_REQUEST['q'])).'" /></div>';
echo '<input type="submit" value="'._('buscar').'" />'."\n";
echo '</fieldset>';
echo '</form>';

if ($response['ids']) {
	$rows = min($response['rows'], 1000);
	foreach($response['ids'] as $link_id) {
		$link = new LinkMobile;
		$link->id=$link_id;
		$link->read();
		$link->print_summary('full', $link->status == 'published' ? 100 : 20);
	}
}

do_pages($rows, $page_size);
echo '</div>';
do_footer();
?>
