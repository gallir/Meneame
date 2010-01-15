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
include(mnminclude.'link.php');

meta_get_current();


$page_size = 15;
$page = get_current_page();
$offset=($page-1)*$page_size;
$globals['ads'] = true;

$cat=$_REQUEST['category'];

do_header(_('últimas publicadas') . ' | men&eacute;ame');
do_tabs('main','published');
if ($globals['meta_current'] > 0) {
	$from_where = "FROM links WHERE link_status='published' and link_category in (".$globals['meta_categories'].") ";
	print_index_tabs(); // No other view
} elseif ($current_user->user_id > 0) { // Check authenticated users
	switch ($globals['meta']) {
		case '_personal':
			$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - $globals['time_enabled_comments']).'"';
			$from_where = "FROM links WHERE link_date > $from_time and link_status='published' and link_category in (".$globals['meta_categories'].") ";
			//$from_where = "FROM links WHERE link_status='published' and link_category in (".$globals['meta_categories'].") ";
			print_index_tabs(7); // Show "personal" as default
			break;
		case '_friends':
			$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 86400*4).'"';
			$from_where = "FROM links, friends WHERE link_date >  $from_time and link_status='published' and friend_type='manual' and friend_from = $current_user->user_id and friend_to=link_author and friend_value > 0";
			print_index_tabs(1); // Friends
		break;
		default:
			print_index_tabs(0); // All
			$rows = Link::count('published');
			$from_where = "FROM links WHERE link_status='published' ";
	}
} else {
	print_index_tabs(0); // No other view
	$from_where = "FROM links WHERE link_status='published' ";
}

do_mnu_categories_horizontal($_REQUEST['category']);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
if ($globals['show_popular_published']) do_best_stories();
do_best_sites();
do_categories_cloud('published');
if ($page < 2) {
	do_best_comments();
}
do_vertical_tags('published');
do_banner_promotions();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

if($cat) {
	$from_where .= " AND link_category=$cat ";
}
$order_by = " ORDER BY link_date DESC ";

if (!$rows) $rows = $db->get_var("SELECT SQL_CACHE count(*) $from_where");

$links = $db->get_col("SELECT SQL_CACHE link_id $from_where $order_by LIMIT $offset,$page_size");
if ($links) {
	foreach($links as $link_id) {
		$link = Link::from_db($link_id);
		$link->print_summary();
	}
}

do_pages($rows, $page_size);
echo '</div>'."\n";

do_footer_menu();
do_footer();

function print_index_tabs($option=-1) {
	global $globals, $db, $current_user;

	$toggler = get_toggler_plusminus('topcatlist', $_REQUEST['category']);
	$active = array();
	$toggle_active = array();
	if ($option >= 0) {
		$active[$option] = 'class="tabsub-this"';
		$toggle_active[$option] = &$toggler;
	}

	echo '<ul class="tabsub-shakeit">'."\n";
	if ($current_user->has_personal) {
		echo '<li '.$active[7].'><a href="'.$globals['base_url'].'">'._('personal'). '</a>'.$toggle_active[7].'</li>'."\n";
	}
	echo '<li '.$active[0].'><a href="'.$globals['base_url'].$globals['meta_skip'].'">'._('todas'). '</a>'.$toggle_active[0].'</li>'."\n";
	// Do metacategories list
	$metas = $db->get_results("SELECT SQL_CACHE category_id, category_name, category_uri FROM categories WHERE category_parent = 0 ORDER BY category_id ASC");
	if ($metas) {
		foreach ($metas as $meta) {
			if ($meta->category_id == $globals['meta_current']) {
				$active_meta = 'class="tabsub-this"';
				$globals['meta_current_name'] = $meta->category_name;
				$toggle = &$toggler;
			} else {
				$active_meta = '';
				$toggle = '';
			}
			echo '<li '.$active_meta.'><a href="'.$globals['base_url'].'?meta='.$meta->category_uri.'">'.$meta->category_name. '</a>'.$toggle.'</li>'."\n";
		}
	}
	if ($current_user->user_id > 0) {
		echo '<li '.$active[1].'><a href="'.$globals['base_url'].'?meta=_friends">'._('amigos'). '</a>'.$toggle_active[1].'</li>'."\n";
	} else {
		meta_teaser_item();
	}

	// Print RSS teasers
	switch ($option) {
		case 0: // All, published
			echo '<li><a class="teaser" href="'.$globals['base_url'].'rss2.php" rel="rss"><img class="tabsub-shakeit-icon" src="'.$globals['base_static'].'img/common/feed-icon-12x12.png" width="12" height="12" alt="rss2"/></a></li>';
			break;
		case 7: // Personalised, published
			echo '<li><a class="teaser" href="'.$globals['base_url'].'rss2.php?personal='.$current_user->user_id.'" rel="rss"><img class="tabsub-shakeit-icon" src="'.$globals['base_static'].'img/common/feed-icon-12x12.png" width="12" height="12" alt="rss2"/></a></li>';
			break;
		default:
			echo '<li><a class="teaser" href="'.$globals['base_url'].'rss2.php?meta='.$globals['meta_current'].'" rel="rss"><img class="tabsub-shakeit-icon" src="'.$globals['base_static'].'img/common/feed-icon-12x12.png" width="12" height="12" alt="rss2"/></a></li>';
	}

	echo '</ul>'."\n";
}

?>
