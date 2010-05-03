<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

$globals['ads'] = true;

$min_pts = 10;
$max_pts = 44;
$words_limit = 100;

$line_height = $max_pts * 0.75;

//$range_names  = array(_('24 horas'), _('48 horas'), _('una semana'), _('un mes'), _('un año'), _('todas'));
//$range_values = array(1, 2, 7, 30, 365, 0);
$range_names  = array(_('24 horas'), _('48 horas'), _('una semana'));
$range_values = array(1, 2, 7);

$from = check_integer('range');

if($from > count($range_values) || ! $range_values[$from] ) {
	$from = 0;
}
// we use this to allow sql caching
$from_time = '"'.date("Y-m-d H:00:00", time() - 86400 * $range_values[$from]).'"';
$from_where = "FROM tags, links WHERE  tag_lang='$dblang' and tag_date > $from_time and link_id = tag_link_id and link_status != 'discard'";
$from_where .= " GROUP BY tag_words";

$max = max($db->get_var("select count(*) as words $from_where order by words desc limit 1"), 2);
//echo "MAX= $max\n";

$coef = ($max_pts - $min_pts)/($max-1);


do_header(_('nube de etiquetas') . ' | men&eacute;ame');
do_tabs('main', _('etiquetas'), true);
print_period_tabs();

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_best_stories();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";


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
		echo '<span style="font-size: '.$size.'pt"><a href="'.$globals['base_url'].'search.php?p=tag&amp;q='.urlencode($word).'">'.$word.'</a></span>&nbsp;&nbsp; ';
	}

}

echo '</div>';
echo '</div>';
do_footer_menu();
do_footer();


function print_period_tabs() {
	global $globals, $current_user, $range_values, $range_names;

	if(!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= count($range_values)) $current_range = 0;
	echo '<ul class="subheader">'."\n";
	for($i=0; $i<count($range_values) && $range_values[$i] < 40; $i++) {
		if($i == $current_range)  {
			$active = ' class="selected"';
		} else {
			$active = "";
		}
		echo '<li'.$active.'><a href="cloud.php?range='.$i.'">' .$range_names[$i]. '</a></li>'."\n";
	}
	echo '</ul>'."\n";
}
?>
