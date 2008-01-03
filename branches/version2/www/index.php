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


$page_size = 20;
$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;

$cat=check_integer('category');

do_header(_('últimas publicadas') . ' // men&eacute;me');
do_banner_top();
echo '<div id="container">'."\n";
do_sidebar();
echo '<div id="contents">'."\n";
do_tabs('main','published');
if ($globals['meta_current'] > 0) {
	$from_where = "FROM links WHERE link_status='published' and link_category in (".$globals['meta_categories'].") ";
	print_index_tabs(); // No other view
} elseif ($current_user->user_id > 0) { // Check authenticated users
	// Check the personalized views
	switch ($globals['meta']) {
		case '_friends':
			$from_time = '"'.date("Y-m-d H:00:00", $globals['now'] - 86400*4).'"';
			$from_where = "FROM links, friends WHERE link_date >  $from_time and link_status='published' and friend_type='manual' and friend_from = $current_user->user_id and friend_to=link_author and friend_value > 0";
			print_index_tabs(1); // Friends
		break;
		default:
			print_index_tabs(0); // All
			$from_where = "FROM links WHERE link_status='published' ";
	}
} else {
	print_index_tabs(0); // No other view
	$from_where = "FROM links WHERE link_status='published' ";
}

do_mnu_categories_horizontal($_REQUEST['category']);

if($cat) {
	$from_where .= " AND link_category=$cat ";
}
$order_by = " ORDER BY link_published_date DESC ";

$link = new Link;
$rows = $db->get_var("SELECT count(*) $from_where");

$links = $db->get_col("SELECT link_id $from_where $order_by LIMIT $offset,$page_size");
if ($links) {
	foreach($links as $link_id) {
		$link->id=$link_id;
		$link->read();
		$link->print_summary();
	}
}

do_pages($rows, $page_size);
echo '</div> <!--index.php-->';
$globals['tag_status'] = 'published';
do_footer();

function print_index_tabs($option=-1) {
	global $globals, $db, $current_user;

	$active = array();
	if ($option >= 0)
		$active[$option] = 'class="tabsub-this"';

	echo '<ul class="tabsub-shakeit">'."\n";
	echo '<li><a '.$active[0].' href="'.$globals['base_url'].$globals['meta_skip'].'">'._('todas'). '</a></li>'."\n";
	// Do metacategories list
	$metas = $db->get_results("SELECT category_id, category_name, category_uri FROM categories WHERE category_parent = 0 ORDER BY category_id ASC");
	if ($metas) {
		foreach ($metas as $meta) {
			if ($meta->category_id == $globals['meta_current']) $active_meta = 'class="tabsub-this"';
			else $active_meta = '';
			echo '<li><a '.$active_meta.' href="'.$globals['base_url'].'?meta='.$meta->category_uri.'">'.$meta->category_name. '</a></li>'."\n";
		}
	}
	if ($current_user->user_id > 0) {
		echo '<li><a '.$active[1].' href="'.$globals['base_url'].'?meta=_friends">'._('amigos'). '</a></li>'."\n";
	}
	meta_teaser_item();

	// Print RSS teasers
	if ($option==0) { // All published
		echo '<li><a class="teaser" href="'.$globals['base_url'].'rss2.php" rel="rss"><img src="'.$globals['base_url'].'img/common/feed-icon-12x12.png" width="12" height="12" alt="rss2"/></a></li>';
	} elseif ($globals['meta_current'] > 0) { // A meta rss
		echo '<li><a class="teaser" href="'.$globals['base_url'].'rss2.php?meta='.$globals['meta_current'].'" rel="rss"><img src="'.$globals['base_url'].'img/common/feed-icon-12x12.png" width="12" height="12" alt="rss2"/></a></li>';
	}

	echo '</ul>'."\n";
}

?>
