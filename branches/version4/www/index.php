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


if (! $current_user->user_id) {
	$globals['fancybox_enabled'] = false; // Don't load fancybox CSS and Javascript
}

meta_get_current();



$page_size = 20;
$page = get_current_page();
$offset=($page-1)*$page_size;
$globals['ads'] = true;
$globals['ads_section'] = 'portada';

$cat=$_REQUEST['category'];

do_header(_('Menéame'));
do_tabs('main','published');

$from = '';

if ($globals['meta_current'] > 0) {
	$where = "link_status='published' and link_category in (".$globals['meta_categories'].") ";
	print_index_tabs(); // No other view
} elseif ($current_user->user_id > 0) { // Check authenticated users
	switch ($globals['meta']) {
		case '_personal':
			$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - $globals['time_enabled_comments']).'"';
			$where = "link_date > $from_time and link_status='published' and link_category in (".$globals['meta_categories'].") ";
			//$from_where = "FROM links WHERE link_status='published' and link_category in (".$globals['meta_categories'].") ";
			print_index_tabs(7); // Show "personal" as default
			break;
		case '_friends':
			$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 86400*4).'"';
			$from = ", friends";
			$where = "link_date >  $from_time and link_status='published' and friend_type='manual' and friend_from = $current_user->user_id and friend_to=link_author and friend_value > 0";
			print_index_tabs(1); // Friends
		break;
		default:
			print_index_tabs(0); // All
			$rows = Link::count('published');
			$where = "link_status='published' ";
	}
} else {
	print_index_tabs(0); // No other view
	$rows = Link::count('published');
	$where = "link_status='published' ";
}

do_mnu_categories_horizontal($_REQUEST['category']);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_banner_promotions();
if ($globals['show_popular_published']) {
	do_active_stories();
	do_best_stories();
	do_most_clicked_stories();
}
do_best_sites();
if ($page < 2) {
	do_best_comments();
}
do_categories_cloud('published');
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
	$where .= " AND link_category=$cat ";
}
$order_by = "ORDER BY link_date DESC ";

if (!$rows) $rows = $db->get_var("SELECT SQL_CACHE count(*) FROM links $from WHERE $where");

// We use a "INNER JOIN" in order to avoid "order by" whith filesorting. It was very bad for high pages
$links = $db->object_iterator("SELECT".Link::SQL."INNER JOIN (SELECT link_id FROM links $from WHERE $where $order_by LIMIT $offset,$page_size) as id USING (link_id)", "Link");
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
	if ($current_user->has_personal) {
		$items[] = array('id' => 7, 'url' => '', 'title' => _('personal'));
	}
	$items[] = array('id' => 0, 'url' => $globals['meta_skip'], 'title' => _('todas'));
	$metas = $db->get_results("SELECT SQL_CACHE category_id, category_name, category_uri FROM categories WHERE category_parent = 0 ORDER BY category_id ASC");
	if ($metas) {
		foreach ($metas as $meta) {
			$items[] = array(
				'id'  => 9999, /* fake number */
				'url' =>'?meta='.$meta->category_uri,
				'selected' => $meta->category_id == $globals['meta_current'],
				'title' => $meta->category_name
			);
		}
	}
	// RSS teasers
	switch ($option) {
	case 0: // All, published
		$feed = array("url" => "", "title" => "");
		break;
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
