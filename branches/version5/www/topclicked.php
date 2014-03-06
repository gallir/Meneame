<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

$page_size = $globals['page_size'];

$range_names  = array(_('48 horas'), _('una semana'), _('un mes'), _('un año'));
$range_values = array(2, 7, 30, 365);

$current_page = min(4, get_current_page());
$offset=($current_page-1)*$page_size;

// Select a month and year
if (!empty($_GET['month']) && !empty($_GET['year']) && ($month = (int) $_GET['month']) > 0 && ($year = (int) $_GET['year'])) {
	$sql = "SELECT SQL_CACHE link_id, link_votes as votes FROM sub_statuses, links WHERE id = ".SitesMgr::my_id()." AND YEAR(date) = $year AND MONTH(date) = $month AND status = 'published' AND link = link_id ORDER BY link_votes DESC ";
	$time_link = "YEAR(date) = $year AND MONTH(date) = $month";
} else {
	// Select from a start date
	$from = intval($_GET['range']);
	if ($from >= count($range_values) || $from < 0 ) $from = 0;

	// Use memcache if available
	if ($globals['memcache_host'] && $current_page < 4) {
		$memcache_key = 'topclicked_'.$globals['site_shortname'].$from.'_'.$current_page;
	}

	if ($range_values[$from] > 0) {
		// we use this to allow sql caching
		$from_time = '"'.date("Y-m-d H:i:00", time() - 86400 * $range_values[$from]).'"';
		if ($from > 0) {
			$status = "AND status = 'published'";
		} else {
			$status = "AND status in ('published', 'queued')";
		}
		$sql = "SELECT link_id, counter FROM sub_statuses, links, link_clicks WHERE sub_statuses.id = ".SitesMgr::my_id()." AND date > $from_time $status AND link = link_id AND link_clicks.id = link_id ORDER BY counter DESC ";
		$time_link = "date > $from_time";
	}
}

if (!($memcache_key
		&& ($rows = memcache_mget($memcache_key.'rows'))
		&& ($links = unserialize(memcache_mget($memcache_key)))) ) {
	// It's not in cache, or memcache is disabled
	$rows = $db->get_var("SELECT count(*) FROM sub_statuses WHERE $time_link $status");
	$rows = min(4*$page_size, $rows); // Only up to 4 pages
	if ($rows > 0) {
		$links = $db->get_results("$sql LIMIT $offset,$page_size");
		if ($memcache_key) {
			$ttl = 1800;
			memcache_madd($memcache_key.'rows', $rows, $ttl);
			memcache_madd($memcache_key, serialize($links), $ttl);
		}
	}
}


do_header(_('más visitadas') . ' | ' . $globals['site_name'], _('más visitadas'));
$globals['tag_status'] = 'published';
do_tabs('main', 'topclicked');
print_period_tabs();

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_active_stories();
do_banner_promotions();
do_best_stories();
do_best_comments();
do_vertical_tags('published');
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

if ($links) {
	$counter = 0;
	foreach($links as $dblink) {
		$link = Link::from_db($dblink->link_id);
		$link->show_clicks = true;
		$link->print_summary();
		$counter++; Haanga::Safe_Load('private/ad-interlinks.html', compact('counter', 'page_size'));
	}
}
do_pages($rows, $page_size);
echo '</div>'."\n";

do_footer_menu();
do_footer();

function print_period_tabs() {
	global $globals, $current_user, $range_values, $range_names, $month, $year;

	echo '<ul class="subheader">'."\n";
	if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) {
		$current_range = 0;
	}

	for($i=0; $i<count($range_values) /* && $range_values[$i] < 60 */; $i++) {
		if($i == $current_range)  {
			$active = ' class="selected"';
		} else {
			$active = "";
		}
		echo '<li'.$active.'><a href="topclicked.php?range='.$i.'">' .$range_names[$i]. '</a></li>'."\n";
	}
	echo '</ul>'."\n";
}
?>
