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

$range_names  = array(_('24 horas'), _('una semana'), _('un mes'), _('un año'), _('todas'));
$range_values = array(1, 7, 30, 365, 0);

$offset=(get_current_page()-1)*$page_size;

$from = intval($_GET['range']);
if ($from >= count($range_values) || $from < 0 ) $from = 0;

// we use this to allow sql caching
$current_hour = date("Y-m-d H:00:00");

if($from == 0 ) {
	$from_time = "date_sub('$current_hour', interval $range_values[$from] day)";
	$sql = "SELECT link_id, count(*) as votes  FROM votes, links WHERE  vote_type='links' and vote_date > $from_time AND vote_link_id=link_id AND link_status = 'published' GROUP BY vote_link_id ORDER BY votes DESC ";
	$time_link = "link_modified > $from_time AND";
} elseif ($range_values[$from] > 0) {
	$from_time = "date_sub('$current_hour', interval $range_values[$from] day)";
	$sql = "SELECT link_id, link_votes as votes FROM links WHERE  link_date > $from_time AND  link_status = 'published' ORDER BY link_votes DESC ";
	$time_link = "link_date > $from_time AND";
} else {
	$sql = "SELECT link_id, link_votes as votes FROM links WHERE link_status = 'published' ORDER BY link_votes DESC ";
	$time_link = '';
}

do_header(_('más votadas'));
do_banner_top();
echo '<div id="container">' . "\n";
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
do_sidebar_top();
do_rightbar();
do_footer();


function do_sidebar_top() {
	global $db, $dblang, $range_values, $range_names;

	echo '<div id="sidebar">'."\n";
	do_mnu_faq('cloud');
	do_mnu_submit();
	do_mnu_sneak();

	/*
	echo '<div class="column-one-list-short">'."\n";
	echo '<ul>'."\n";

	if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;

	for($i=0; $i<count($range_values); $i++) {
		if($i == $current_range)  {
			$classornotclass = ' class="thiscat"';
		} else {
			$classornotclass = "";
		}
		echo '<li '.$classornotclass.'><a href="topstories.php?range='.$i.'">' .$range_names[$i]. '</a></li>'."\n";
	}

	echo '</ul>'."\n";
	echo '</div>'."\n";
	*/

	do_mnu_meneria();
	do_mnu_rss();

	echo '</div>';

}

function print_period_tabs() {
	global $globals, $current_user, $range_values, $range_names;

	if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;
	echo '<ul class="tabsub-shakeit">'."\n";
	for($i=0; $i<count($range_values); $i++) {
		if($i == $current_range)  {
			$active = ' class="tabsub-this"';
		} else {
			$active = "";
		}
		echo '<li><a '.$active.'href="topstories.php?range='.$i.'">' .$range_names[$i]. '</a></li>'."\n";
	}
	echo '</ul>'."\n";


	/*
	$active = array();
	$active[$option] = 'class="tabsub-this"';

	echo '<ul class="tabsub-shakeit">'."\n";
	echo '<li><a '.$active[1].' href="'.$globals['base_url'].'shakeit.php?view=all">'._('todas'). '</a></li>'."\n";
	echo '<li><a '.$active[2].' href="'.$globals['base_url'].'shakeit.php?view=discarded">'._('descartadas'). '</a></li>'."\n";
	if ($current_user->user_id > 0)
		echo '<li><a '.$active[3].' href="'.$globals['base_url'].'shakeit.php?view=recommended">'._('recomendadas'). '</a></li>'."\n";
	echo '</ul>'."\n";
	*/
}
?>
