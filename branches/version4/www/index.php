<?
// The Meneame source code is Free Software, Copyright (C) 2005-2009 by
// Ricardo Galli <gallir at gmail dot com> and Menéame Comunicacions S.L.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.

// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

meta_get_current();



$page_size = 20;
$page = get_current_page();
$offset=($page-1)*$page_size;
$globals['ads_section'] = 'portada';

$cat=$_REQUEST['category'];

do_header($globals['site_name'], _('portada'));
do_tabs('main','published');

$from = '';

if ($globals['meta_current'] > 0) {
	$where = "status='published' and category in (".$globals['meta_categories'].") ";
	print_index_tabs(); // No other view
} else {
	switch ($globals['meta']) {
		case '_personal':
			if (! $current_user->user_id > 0) do_error(_('debe autentificarse'), 401); // Check authenticated users
			$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - $globals['time_enabled_comments']).'"';
			$where = "status='published' and date > $from_time and category in (".$globals['meta_categories'].") ";
			$rows = -1;
			//$from_where = "FROM links WHERE link_status='published' and link_category in (".$globals['meta_categories'].") ";
			print_index_tabs(7); // Show "personal" as default
			break;
		case '_friends':
			if (! $current_user->user_id > 0) do_error(_('debe autentificarse'), 401); // Check authenticated users
			$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 86400*4).'"';
			$from = ", friends, links";
			$where = "date > $from_time and status='published' and friend_type='manual' and friend_from = $current_user->user_id and friend_to=link_author and friend_value > 0 and link_id = link";
			$rows = -1;
			print_index_tabs(1); // Friends
			break;
		default:
			print_index_tabs(0); // All
			$rows = Link::count('published');
			$where = "status='published' ";
	}
}


do_mnu_categories_horizontal($_REQUEST['category']);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
if ($globals['show_popular_published']) {
	do_active_stories();
}
do_banner_promotions();
if ($globals['show_popular_published']) {
	do_best_stories();
	do_most_clicked_stories();
}
do_best_sites();
if ($page < 2) {
	do_best_comments();
}
// do_categories_cloud('published');
do_vertical_tags('published');
do_last_blogs();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

do_banner_top_news();

if ($page == 1 && ($top = Link::top())) {
	$vars = array('self' => $top);
	Haanga::Load("link_top.html", $vars);
	$counter = 1;
}




if($cat) {
	$where .= " AND category=$cat ";
}
$order_by = "ORDER BY date DESC ";

if (!$rows) $rows = $db->get_var("SELECT SQL_CACHE count(*) FROM sub_statuses $from WHERE sub_statuses.id = ". SitesMgr::my_id() ." AND $where");

// We use a "INNER JOIN" in order to avoid "order by" whith filesorting. It was very bad for high pages
$sql = "SELECT".Link::SQL."INNER JOIN (SELECT link FROM sub_statuses $from WHERE sub_statuses.id = ". SitesMgr::my_id() ." AND $where $order_by LIMIT $offset,$page_size) as ids ON (ids.link = link_id)";

$links = $db->object_iterator($sql, "Link");
if ($links) {
	foreach($links as $link) {
		$link->print_summary();
		$counter++; Haanga::Safe_Load('private/ad-interlinks.html', compact('counter'));
	}
}


do_pages($rows, $page_size);
echo '</div>'."\n";

do_footer_menu();
do_footer();
exit(0);

function print_index_tabs($option=-1) {
	global $globals, $db, $current_user;

	$items = array();
	if (isset($current_user->has_personal)) {
		$items[] = array('id' => 7, 'url' => '', 'title' => _('personal'));
	}
	$items[] = array('id' => 0, 'url' => $globals['meta_skip'], 'title' => _('todas'));

	$metas = SitesMgr::get_metas();
	if ($metas) {
		foreach ($metas as $meta) {
			$items[] = array(
				'id'  => 9999, /* fake number */
				'url' =>'?meta='.$meta->uri,
				'selected' => $meta->id == $globals['meta_current'],
				'title' => $meta->name
			);
		}
	}
	// RSS teasers
	switch ($option) {
		case 7: // Personalised, published
			$feed = array("url" => "?personal=".$current_user->user_id, "title" => _('categoría personalizadas'));
			break;
		default:
			$feed = array("url" => "?meta=".$globals['meta_current'], "title" => "");
			break;
	}

	if ($current_user->user_id > 0) {
		$items[] = array('id' => 1, 'url' => '?meta=_friends', 'title' => _('amigos'));
	}

	$vars = compact('items', 'option', 'feed');
	$vars['container_id']   = 'topcatlist';
	$vars['toggle_enabled'] = isset($_REQUEST['category']);
	return Haanga::Load('print_tabs.html', $vars);
}

?>
