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

$globals['ads'] = true;


$sql = "SELECT link_id  FROM links WHERE  link_date > date_sub(now(), interval 24 hour) and link_negatives > 0  and link_karma < 0 ORDER BY link_negatives DESC LIMIT 25 ";

do_header(_('las peores :-)'));
do_navbar(_('noticias') . ' &#187; ' . _('estadísticas'));
do_sidebar_top();
echo '<div id="contents">';
echo '<div class="air-with-footer">'."\n";
echo '<h2>'._('¿noticias?').' :-) </h2>';

$link = new Link;

$links = $db->get_results($sql);
if ($links) {
	foreach($links as $dblink) {
		$link->id=$dblink->link_id;
		$link->read();
		$link->print_summary('short');
	}
}
echo '</div>';
echo '</div>';
echo '<br clear="all">';
do_footer();


function do_sidebar_top() {
	global $db, $dblang, $range_values, $range_names;

	echo '<div id="sidebar">'."\n";
	echo '<ul class="main-menu">'."\n";
// 	do_standard_links();
	echo '</ul>';
	echo '</div>';
}
?>
