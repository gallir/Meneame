<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

do_header(_('destacadas') . ' | ' . $globals['site_name'], _('destacadas'));
$globals['tag_status'] = 'published';
do_tabs('main', 'active');

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_best_stories();
do_banner_promotions();
do_best_comments();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

$top = new Annotation('top-actives-'.$globals['site_shortname']);
if ($top->read() && ($links = explode(',',$top->text))) {
	$counter = 0;
	foreach($links as $id) {
		$link = Link::from_db($id);
		$link->show_clicks = true;
		$link->print_summary();
		$counter++;
		Haanga::Safe_Load('private/ad-interlinks.html', compact('counter'));
	}
}
echo '</div>'."\n";

do_footer_menu();
do_footer();

?>
