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


print_tabs();

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


if (isset($_GET['all'])) {
	$all = true;
	$page_size = 50;
	$page = get_current_page();
	$offset=($page-1)*$page_size;

	$sql = "select subs.*, user_id, user_login, user_avatar from subs, users where subs.sub = 1 and created_from = ".SitesMgr::my_id()." and user_id = owner order by name asc limit $offset, $page_size";
	$rows = -1;
} else {
	$all = false;
	$sql = "select subs.*, user_id, user_login, user_avatar, count(*) as c from subs, sub_statuses, users where date > date_sub(now(), interval 5 day) and subs.id = sub_statuses.id and sub_statuses.id = sub_statuses.origen and sub_statuses.status = 'published' and subs.sub = 1 and user_id = owner group by subs.id order by c desc limit 50";
}

$subs = $db->get_results($sql);

Haanga::Load('subs.html', compact('title', 'subs'));
echo '</div>';

if ($all) {
	do_pages($rows, $page_size, false);
}

do_footer();

function print_tabs() {
	if (SitesMgr::my_id() == 1 && SitesMgr::can_edit(0)) $can_edit = true;
	else $can_edit = false;

	$items = array();
    $items[] = array('id' => 0, 'url' => 'subs', 'title' => _('más activos'));
	$items[] = array('id' => 1, 'url' => 'subs?all', 'title' => _('todos'));
	if ($can_edit) {
		$items[] = array('id' => 2, 'url' => 'subedit', 'title' => _('crear sub'));
	}


	if (isset($_GET['all'])) $option = 1;
	else $option = 0;

	$vars = compact('items', 'option');
	return Haanga::Load('print_tabs.html', $vars);
}


