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

$globals['ads'] = true;

$page_size = 20;

$range_names  = array(_('24 horas'), _('48 horas'), _('una semana'), _('un mes'), _('un año'), _('todas'));
$range_values = array(1, 2, 7, 30, 365, 0);

$offset=(get_current_page()-1)*$page_size;

$from = intval($_GET['range']);
if ($from >= count($range_values) || $from < 0 ) $from = 0;


if ($range_values[$from] > 0) {
	// we use this to allow sql caching
	$from_time = '"'.date("Y-m-d H:00:00", time() - 86400 * $range_values[$from]).'"';
	$sql = "SELECT link_id, link_votes as votes FROM links WHERE  link_published_date > $from_time AND  link_status = 'published' ORDER BY link_votes DESC ";
	$time_link = "link_published_date > $from_time AND";
} else {
	$sql = "SELECT link_id, link_votes as votes FROM links WHERE link_status = 'published' ORDER BY link_votes DESC ";
	$time_link = '';
}

do_header(_('más votadas'));
do_banner_top();
echo '<div id="container">'."\n";
do_sidebar();
echo '<div id="contents">';
$globals['tag_status'] = 'published';
do_tabs('main', 'popular');
print_period_tabs();


$link = new Link;

//$rows = $db->get_var("SELECT count(*) as votes $from_where $order_by");
$rows = $db->get_var("SELECT count(*) FROM links WHERE $time_link link_status = 'published'");

$links = $db->get_results("$sql LIMIT $offset,$page_size");
if ($links) {
	foreach($links as $dblink) {
		$link->id=$dblink->link_id;
		$link->read();
		$link->print_summary();
	}
}
do_pages($rows, $page_size);
echo '</div>';
do_footer();

function print_period_tabs() {
	global $globals, $current_user, $range_values, $range_names;

	if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;
	echo '<ul class="tabsub-shakeit">'."\n";
	for($i=0; $i<count($range_values) && $range_values[$i] < 10; $i++) {
		if($i == $current_range)  {
			$active = ' class="tabsub-this"';
		} else {
			$active = "";
		}
		echo '<li><a '.$active.'href="topstories.php?range='.$i.'">' .$range_names[$i]. '</a></li>'."\n";
	}
	echo '</ul>'."\n";
}
?>
