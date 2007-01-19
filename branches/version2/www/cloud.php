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

$min_pts = 10;
$max_pts = 44;
$words_limit = 100;

$line_height = $max_pts * 0.75;

$range_names  = array(_('48 horas'), _('última semana'), _('último mes'), _('último año'), _('todas'));
$range_values = array(172800, 604800, 2592000, 31536000, 0);


if(($from = check_integer('range')) >= 0 && $from < count($range_values) && $range_values[$from] > 0 ) {
	$from_time = time() - $range_values[$from];
	$from_where = "FROM tags, links WHERE  tag_lang='$dblang' and tag_date > FROM_UNIXTIME($from_time) and link_id = tag_link_id and link_status != 'discard'";
	$time_query = "&amp;from=$from_time";
} else {
	$from_where = "FROM tags, links WHERE tag_lang='$dblang' and link_id = tag_link_id and link_status != 'discard'";
}
$from_where .= " GROUP BY tag_words";

$max = max($db->get_var("select count(*) as words $from_where order by words desc limit 1"), 2);
//echo "MAX= $max\n";

$coef = ($max_pts - $min_pts)/($max-1);


do_header(_('nube de etiquetas'));
do_banner_top();
echo '<div id="'.$globals['css_container'].'">'."\n";
echo '<div id="contents">';
do_tabs('main', _('etiquetas'), true);
echo '<div class="topheading"><h2>+ '.$words_limit.'</h2></div>';
echo '<div style="margin: 0px 0 20px 0; line-height: '.$line_height.'pt; margin-left: 25px;">';
$res = $db->get_results("select tag_words, count(*) as count $from_where order by count desc limit $words_limit");
if ($res) {
	foreach ($res as $item) {
		$words[$item->tag_words] = $item->count;
	}
	ksort($words);
	foreach ($words as $word => $count) {
		$size = round($min_pts + ($count-1)*$coef, 1);
		echo '<span style="font-size: '.$size.'pt"><a href="'.$globals['base_url'].'?search=tag:'.urlencode($word).$time_query.'">'.$word.'</a></span>&nbsp;&nbsp; ';
	}

}

echo '</div>';
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
	echo '<div class="column-one-list-short">'."\n";
	echo '<ul>'."\n";

	if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;
	for($i=0; $i<count($range_values); $i++) {
		if($i == $current_range)  {
			$classornotclass = ' class="thiscat"';
		} else {
			$classornotclass = "";
		}
		echo '<li '.$classornotclass.'><a href="cloud.php?range='.$i.'">' .$range_names[$i]. '</a></li>'."\n";
	}
	echo '</ul>';
	echo '</div>'."\n";
	do_mnu_meneria();
	do_mnu_rss();
	echo '</div>';

}

?>
