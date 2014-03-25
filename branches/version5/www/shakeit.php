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
//	  http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include_once('config.php');
include(mnminclude.'html1.php');

$page_size = $globals['page_size'] * 2;

meta_get_current();

$page = get_current_page();
$offset = ($page-1)*$page_size;
$rows = -1; // Don't show page numbers by default
$cat = $_REQUEST['category'];

$from = '';
switch ($globals['meta']) {
	case '_personal':
		$globals['tag_status'] = 'queued';
		$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - $globals['time_enabled_votes']).'"';
		$where = "date > $from_time and status='queued' and category in (".$globals['meta_categories'].") ";
		$order_by = "ORDER BY date DESC";
		$tab = 7;
		break;
	case '_friends':
		$globals['noindex'] = true;
		$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - $globals['time_enabled_votes']).'"';
		$from = ", friends, links";
		$where = "date > $from_time and status='queued' and friend_type='manual' and friend_from = $current_user->user_id and friend_to=link_author and friend_value > 0 and link_id = link";
		$rows = -1;
		$order_by = "ORDER BY date DESC";
		$tab = 2;
		$globals['tag_status'] = 'queued';
		break;
	case '_popular':
		// Show  the hihgher karma first
		$globals['noindex'] = true;
		$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 86400*4).'"';
		$from = ", links, link_clicks";
		$where = "date > $from_time and status='queued' and link = link_id and link_id = link_clicks.id and link_clicks.counter/(link_votes+link_negatives) > 1.3 and link_karma > 20 ";
		$order_by = "ORDER BY link_karma DESC";
		$rows = -1;
		$tab = 3;
		$globals['tag_status'] = 'queued';
		break;
	case '_discarded':
		// Show only discarded in four days
		$globals['noindex'] = true;
		$globals['ads'] = false;
		$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 86400*4).'"';
		$where = "status in ('discard', 'abuse', 'autodiscard') " ;
		$order_by = "ORDER BY date DESC ";
		$tab = 5;
		$globals['tag_status'] = 'discard';
		$rows = Link::count('discard') + Link::count('autodiscard') + Link::count('abuse');
		break;
	case '_all':
	default:
		$globals['tag_status'] = 'queued';
		$order_by = "ORDER BY date DESC";
		if ($globals['meta_current'] > 0) {
			$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - $globals['time_enabled_comments']).'"';
			$rows = -1;
			$where = "status='queued' and date > $from_time and category in (".$globals['meta_categories'].") ";
			$tab = false;
		} else {
			$rows = Link::count('queued');
			$where = "status='queued' ";
			$tab = 1;
		}
		break;
}

if($cat) {
	$where .= " AND category=$cat ";
}

$pagetitle = _('noticias pendientes');
if ($page > 1) {
    $pagetitle .= " ($page)";
}
do_header($pagetitle, _('nuevas'));
do_tabs("main","shakeit");
print_shakeit_tabs($tab);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
if ($globals['show_popular_queued']) do_best_queued();
do_last_blogs();
//do_best_comments();
//do_categories_cloud('queued', 24);
do_vertical_tags('queued');
echo '</div>' . "\n";
/*** END SIDEBAR ***/


echo '<div id="newswrap">'."\n";

$sql = "SELECT".Link::SQL."INNER JOIN (SELECT link FROM sub_statuses $from WHERE sub_statuses.id = ". SitesMgr::my_id() ." AND $where $order_by LIMIT $offset,$page_size) as ids on (ids.link = link_id)";

$links = $db->object_iterator($sql, "Link");
if ($links) {
	foreach($links as $link) {
		if ($link->votes == 0 && $link->author != $current_user->user_id) continue;
		if ($offset < 1000) {
			$link->print_summary('full', 16);
		} else {
			$link->print_summary('full');
		}
	}
}


do_pages($rows, $page_size);
echo '</div>'."\n";

do_footer_menu();
do_footer();

function print_shakeit_tabs($option=-1) {
	global $globals, $current_user, $db;

	$items = array();
	if ($current_user->has_personal) {
		$items[] = array('id' => 7, 'url' => 'queue', 'title' => _('personal'));
	}
	$items[] = array('id' => 1, 'url' => 'queue'.$globals['meta_skip'], 'title' => _('todas'));

	if (empty($globals['submnm']) && ! $globals['mobile']) {
		$metas = SitesMgr::get_metas();
		if ($metas) {
			foreach ($metas as $meta) {
				$items[] = array(
					'id'  => 9999, /* fake number */
					'url' =>'queue?meta='.$meta->uri,
					'selected' => $meta->id == $globals['meta_current'],
					'title' => $meta->name
				);
			}
		}
	}

	$items[] = array('id' => 3, 'url' => 'queue?meta=_popular', 'title' => _('candidatas'));

	if ($current_user->user_id > 0) {
		$items[] = array('id' => 2, 'url' => 'queue?meta=_friends', 'title' => _('amigos'));
	}

	if (!$globals['bot']) {
		$items [] = array('id' => 5, 'url' => 'queue?meta=_discarded', 'title' => _('descartadas'));
	}

	// Print RSS teasers
	if (! $globals['mobile']) {
		switch ($option) {
			case 7: // Personalised, queued
				$feed = array("url" => "?status=queued&amp;personal=".$current_user->user_id, "title" => "");
				break;
			default:
				$feed = array("url" => "?status=queued&amp;meta=".$globals['meta_current'], "title" => "");
				break;
		}
	}
	$vars = compact('items', 'option', 'feed');
	return Haanga::Load('print_tabs.html', $vars);
}

?>
