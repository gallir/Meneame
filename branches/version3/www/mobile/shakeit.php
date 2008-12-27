<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1-mobile.php');
include(mnminclude.'linkmobile.php');

header('Cache-Control: no-cache');

$page_size = 20;
$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;


$order_by = " ORDER BY link_date DESC ";
$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 864000).'"'; // Ten days
$from_where = "FROM links WHERE link_date > $from_time and link_status='queued'";

do_header(_('noticias pendientes') . ' | men&eacute;ame mobile');
do_tabs("main","shakeit");

echo '<div id="newswrap">'."\n";

$link = new LinkMobile;
$rows = $db->get_var("SELECT SQL_CACHE count(*) $from_where");
$links = $db->get_col("SELECT SQL_CACHE link_id $from_where $order_by LIMIT $offset,$page_size");
if ($links) {
	foreach($links as $link_id) {
		$link->id=$link_id;
		$link->read();
		$link->print_summary();
	}
}
do_pages($rows, $page_size);
echo '</div>'."\n";

do_footer();

?>
