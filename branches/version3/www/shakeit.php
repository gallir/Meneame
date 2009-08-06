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

//header('Cache-Control: max-age=0, must-revalidate');
header('Cache-Control: no-cache');

meta_get_current();

$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;


$cat = $_REQUEST['category'];

switch ($globals['meta']) {
	case '_personal':
		$globals['tag_status'] = 'queued';
		$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - $globals['time_enabled_votes']).'"';
		$from_where = "FROM links WHERE link_date > $from_time and link_status='queued' and link_category in (".$globals['meta_categories'].") ";
		//$from_where = "FROM links WHERE link_status='queued' and link_category in (".$globals['meta_categories'].") ";
		$order_by = " ORDER BY link_date DESC ";
		$tab = 7;
		break;
	case '_friends':
		$globals['noindex'] = true;
		$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - $globals['time_enabled_votes']).'"';
		$from_where = "FROM links, friends WHERE link_date >  $from_time and link_status='queued' and friend_type='manual' and friend_from = $current_user->user_id and friend_to=link_author and friend_value > 0";
		$order_by = " ORDER BY link_date DESC ";
		$tab = 2;
		$globals['tag_status'] = 'queued';
		break;
	case '_popular':
		// Show  the hihgher karma first
		$globals['noindex'] = true;
		$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 86400*2).'"';
		$from_where = "FROM links WHERE link_date > $from_time and link_status='queued' and link_karma > 10";
		$order_by = " ORDER BY link_karma DESC ";	
		$tab = 3;
		$globals['tag_status'] = 'queued';
		break;
	case '_discarded':
		// Show only discarded in four days
		$globals['noindex'] = true;
		$globals['ads'] = false;
		$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 86400*4).'"';
		$from_where = "FROM links WHERE link_date > $from_time and link_status in ('discard', 'abuse', 'autodiscard') and (link_votes >0 || link_author = $current_user->user_id)";
		$order_by = " ORDER BY link_date DESC ";
		$tab = 5;
		$globals['tag_status'] = 'discard';
		break;
	case '_all':
	default:
		$globals['tag_status'] = 'queued';
		$order_by = " ORDER BY link_date DESC ";
		//$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - $globals['time_enabled_votes']).'"';
		$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 864000).'"'; // Ten days
		//$from_time = 'date_sub(now(), interval 10 day)';
		if ($globals['meta_current'] > 0) {
			$from_where = "FROM links WHERE link_status='queued' and link_date > $from_time and link_category in (".$globals['meta_categories'].") ";
			$tab = false;
		} else {
			$from_where = "FROM links WHERE link_date > $from_time and link_status='queued'";
			//$from_where = "FROM links WHERE link_status='queued'";
			$tab = 1;
		}
		break;
}

do_header(_('noticias pendientes') . ' | men&eacute;ame');
do_tabs("main","shakeit");
print_shakeit_tabs($tab);

do_mnu_categories_horizontal($_REQUEST['category']);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
if ($globals['show_popular_queued']) do_best_queued();
do_categories_cloud('queued', 24);
do_best_comments();
do_vertical_tags('queued');
echo '</div>' . "\n";
/*** END SIDEBAR ***/


echo '<div id="newswrap">'."\n";


if($cat) {
	$from_where .= " AND link_category=$cat ";
}

$link = new Link;
$rows = $db->get_var("SELECT SQL_CACHE count(*) $from_where");
$links = $db->get_col("SELECT SQL_CACHE link_id $from_where $order_by LIMIT $offset,$page_size");
if ($links) {
	foreach($links as $link_id) {
		$link->id=$link_id;
		$link->read();
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

	$toggler = get_toggler_plusminus('topcatlist', $_REQUEST['category']);
	$active = array();
	$toggle_active = array();
	if ($option > 0) {
		$active[$option] = 'class="tabsub-this"';
		$toggle_active[$option] = &$toggler;
	}

	echo '<ul class="tabsub-shakeit">'."\n";
	if ($current_user->has_personal) {
		echo '<li '.$active[7].'><a href="'.$globals['base_url'].'shakeit.php">'._('personal'). '</a>'.$toggle_active[7].'</li>'."\n";
	}
	echo '<li '.$active[1].'><a href="'.$globals['base_url'].'shakeit.php'.$globals['meta_skip'].'">'._('todas'). '</a>'.$toggle_active[1].'</li>'."\n";
	// Do metas' list
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
			echo '<li '.$active_meta.'><a href="'.$globals['base_url'].'shakeit.php?meta='.$meta->category_uri.'">'.$meta->category_name. '</a>'.$toggle.'</li>'."\n";
		}
	}

	echo '<li '.$active[3].'><a href="'.$globals['base_url'].'shakeit.php?meta=_popular">'._('candidatas'). '</a>'.$toggle_active[3].'</li>'."\n";
	if ($current_user->user_id > 0) {
		echo '<li '.$active[2].'><a href="'.$globals['base_url'].'shakeit.php?meta=_friends">'._('amigos'). '</a>'.$toggle_active[2].'</li>'."\n";
	}
	if (!$globals['bot']) {
		echo '<li '.$active[5].'><a href="'.$globals['base_url'].'shakeit.php?meta=_discarded">'._('descartadas'). '</a>'.$toggle_active[5].'</li>'."\n";
	}
	if ($current_user->user_id == 0) {
		meta_teaser_item();
	}

	// Print RSS teasers
	switch ($option) {
		case 1: // All, queued
			echo '<li><a class="teaser" href="'.$globals['base_url'].'rss2.php?status=queued" rel="rss"><img class="tabsub-shakeit-icon" src="'.$globals['base_static'].'img/common/feed-icon-12x12.png" width="12" height="12" alt="rss2"/></a></li>';
			break;
		case 7: // Personalised, queued
			echo '<li><a class="teaser" href="'.$globals['base_url'].'rss2.php?status=queued&amp;personal='.$current_user->user_id.'" rel="rss"><img class="tabsub-shakeit-icon" src="'.$globals['base_static'].'img/common/feed-icon-12x12.png" width="12" height="12" alt="rss2"/></a></li>';
			break;
		default:
			echo '<li><a class="teaser" href="'.$globals['base_url'].'rss2.php?status=queued&amp;meta='.$globals['meta_current'].'" rel="rss"><img class="tabsub-shakeit-icon" src="'.$globals['base_static'].'img/common/feed-icon-12x12.png" width="12" height="12" alt="rss2"/></a></li>';
	}

	echo '</ul>'."\n";
}

?>
