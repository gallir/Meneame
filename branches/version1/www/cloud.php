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
$max_pts = 36;
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
do_navbar(_('etiquetas'));
echo '<div id="contents">';
echo '<h2>+ '.$words_limit.'</h2>';


echo '<div style="margin: 20px 0 20px 0; line-height: '.$line_height.'pt; margin-left: 100px;">';
$res = $db->get_results("select tag_words, count(*) as count $from_where order by count desc limit $words_limit");
if ($res) {
	foreach ($res as $item) {
		$words[$item->tag_words] = $item->count;
	}
	ksort($words);
	foreach ($words as $word => $count) {
		$size = intval($min_pts + ($count-1)*$coef);
		echo '<span style="font-size: '.$size.'pt"><a href="index.php?search=tag:'.urlencode($word).$time_query.'">'.$word.'</a></span>&nbsp;&nbsp; ';
	}

}

echo '</div>';
echo '</div>';
do_sidebar_top();
do_footer();


function do_sidebar_top() {
	global $db, $dblang, $range_values, $range_names;

	echo '<div id="sidebar">'."\n";
	echo '<ul class="main-menu">'."\n";
	echo '<li>'."\n";
	echo '<div class="column-select-us">'."\n";
	echo '<ul>'."\n";

	if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;
	for($i=0; $i<count($range_values); $i++) {	
		if($i == $current_range)  {
			echo '<li class="thiscat">' .$range_names[$i]. '</li>'."\n";
		} else {
			echo '<li><a href="cloud.php?range='.$i.'">' .$range_names[$i]. '</a></li>'."\n";
		}
		
	}
	echo '</ul>'."\n";
	echo '</div>'."\n";
	echo '</li>'."\n";

	do_standard_links();
	echo '</ul>';
	echo '</div>';

}

?>
