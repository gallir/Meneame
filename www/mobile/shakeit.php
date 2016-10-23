<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include(__DIR__.'/config.php');
include(mnminclude.'html1-mobile.php');
include(mnminclude.'linkmobile.php');

$page_size = 20;
$offset=(get_current_page()-1)*$page_size;

do_header(_('pendientes') . ' | ' . $globals['site_name']);
do_tabs("main","shakeit");

echo '<div id="newswrap">'."\n";

$rows = Link::count('queued');
$links = $db->object_iterator("SELECT".Link::SQL."INNER JOIN (SELECT link FROM sub_statuses $from WHERE sub_statuses.id = ". SitesMgr::my_id() ." AND status = 'queued' ORDER by date desc LIMIT $offset,$page_size) as ids ON (ids.link = link_id)", "LinkMobile");
if ($links) {
	foreach($links as $link) {
		$link->print_summary();
	}
}
do_pages($rows, $page_size);
echo '</div>'."\n";

do_footer();

?>
