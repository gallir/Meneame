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

$globals['ads'] = true;

$page_size = 10;
$page = get_current_page();
$offset=($page-1)*$page_size;

$cat=$_REQUEST['category'];

do_header('menéame mobile');
do_tabs('main','published');

echo '<div id="newswrap">'."\n";

if ($page == 1 && ($top = Link::top())) {
	$vars = array('self' => $top);
	Haanga::Load("mobile/link_top.html", $vars);
}

$rows = Link::count('published');
$links = $db->object_iterator("SELECT".Link::SQL."INNER JOIN (SELECT link_id FROM links WHERE link_status='published' ORDER BY link_date DESC LIMIT $offset,$page_size) as id USING (link_id)", "LinkMobile");
if ($links) {
	foreach($links as $link) {
		$link->print_summary();
	}
}

do_pages($rows, $page_size);
echo '</div>'."\n";

do_footer();


?>
