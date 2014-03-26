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
/*
if ($current_user->admin) {
	$where = '';
} else {
	$where = "where (enabled = 1 or owner = $current_user->user_id) and sub = $my_id ";
}
$subs = $db->get_results("select * from subs $where order by id asc");
*/

$sql = "select subs.*, user_id, user_login, user_avatar, count(*) as c from links, subs, sub_statuses, users where link_date > date_sub(now(), interval 3 day) and link = link_id and subs.id = sub_statuses.id and sub_statuses.id = sub_statuses.origen and subs.sub = 1 and user_id = owner group by subs.id order by c desc limit 50";
$subs = $db->get_results($sql);
if ($my_id == 1 && SitesMgr::can_edit(0)) $can_edit = true;
else $can_edit = false;

Haanga::Load('subs.html', compact('subs', 'can_edit'));
echo '</div>';

do_footer();

