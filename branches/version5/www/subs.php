<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//				http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

if (empty($routes)) die; // Don't allow to be called bypassing dispatcher

do_header(_("subs menéame"), 'm/');

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
if ($globals['show_popular_published']) {
	do_active_stories();
	do_best_stories();
	do_most_clicked_stories();
}
do_banner_promotions();
do_most_clicked_sites();
echo '</div>';
/*** END SIDEBAR ***/
echo '<div id="newswrap">';

$my_id =  SitesMgr::my_id();
if ($current_user->admin) {
	$where = '';
} else {
	$where = "where (enabled = 1 or owner = $current_user->user_id) and sub = $my_id ";
}
$subs = $db->get_results("select * from subs $where order by id asc");
if ($my_id == 1 && SitesMgr::can_edit(0)) $can_edit = true;
else $can_edit = false;

Haanga::Load('subs.html', compact('subs', 'can_edit'));
echo '</div>';

do_footer();

