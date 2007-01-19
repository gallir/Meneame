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
do_banner_top();
echo '<div id="'.$globals['css_container'].'">'."\n";
echo '<div id="contents">';
echo '<div class="topheading"><h2>'._('¿noticias?').' :-) </h2></div>';

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
do_sidebar();
do_rightbar();
do_footer();
?>
