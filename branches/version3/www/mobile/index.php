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

$globals['ads'] = false;

$page_size = 10;
$page = get_current_page();
$offset=($page-1)*$page_size;

$cat=$_REQUEST['category'];

do_header('men&eacute;ame mobile');
do_tabs('main','published');

$from_where = "FROM links WHERE link_status='published' ";
$order_by = " ORDER BY link_date DESC ";

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
