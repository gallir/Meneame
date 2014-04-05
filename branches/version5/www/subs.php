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

if (isset($_GET['all'])) {
	$option = 2; // Show all subs
} elseif (! $current_user->user_id || isset($_GET['active']))  {
	$option = 1; // Show active
} else {
	$option = 0; // Show suscribed
}


do_header(_("subs menéame"), 'm/');


print_tabs($option);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_banner_promotions();
echo '</div>';
/*** END SIDEBAR ***/
echo '<div id="newswrap">';

switch ($option) {
	case 0:
		$sql = "select subs.* from subs, prefs where pref_user_id = $current_user->user_id and pref_key = 'sub_follow' and subs.id = pref_value order by name asc";
		$template = 'subs_simple.html';
		$all = false;
		break;
	case 1:
		$all = false;
		$template = 'subs.html';
		$sql = "select subs.*, user_id, user_login, user_avatar, count(*) as c from subs LEFT JOIN users ON (user_id = owner), sub_statuses where date > date_sub(now(), interval 5 day) and subs.id = sub_statuses.id and sub_statuses.id = sub_statuses.origen and sub_statuses.status = 'published' and subs.sub = 1 group by subs.id order by c desc limit 50";
		break;
	default:
		$all = true;
		$template = 'subs.html';
		$page_size = 50;
		$page = get_current_page();
		$offset=($page-1)*$page_size;

		$sql = "select subs.*, user_id, user_login, user_avatar from subs, users where subs.sub = 1 and created_from = ".SitesMgr::my_id()." and user_id = owner order by name asc limit $offset, $page_size";
		$rows = -1;
}

$subs = $db->get_results($sql);

Haanga::Load($template, compact('title', 'subs'));
echo '</div>';

if ($all) {
	do_pages($rows, $page_size, false);
}

do_footer();

function print_tabs($option) {
	global $current_user;

	if (SitesMgr::my_id() == 1 && SitesMgr::can_edit(0)) $can_edit = true;
	else $can_edit = false;

	$items = array();
	
	if ($current_user->user_id) {
    	$items[] = array('id' => 0, 'url' => 'subs', 'title' => _('suscriptos'));
	}
    $items[] = array('id' => 1, 'url' => 'subs?active', 'title' => _('más activos'));
	$items[] = array('id' => 2, 'url' => 'subs?all', 'title' => _('todos'));
	if ($can_edit) {
		$items[] = array('id' => 3, 'url' => 'subedit', 'title' => _('crear sub'));
	}


	$vars = compact('items', 'option');
	return Haanga::Load('print_tabs.html', $vars);
}


