<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function do_vertical_tags() {
	global $db, $globals, $dblang;

	if (!empty($globals['tag_status'])) {
		$status = '= "'. $globals['tag_status']. '"';
	} else {
		$status = "!= 'discarded'";
	}
	$min_pts = 8;
	$max_pts = 16;
	$line_height = $max_pts * 0.75;

	$min_date = $db->get_var("select min(link_date) from links where link_date > date_sub(now(), interval 96 hour)");
	$from_where = "FROM tags, links WHERE tag_lang='$dblang' and tag_date > '$min_date' and link_id = tag_link_id and link_status $status GROUP BY tag_words";
	$max = max($db->get_var("select count(*) as words $from_where order by words desc limit 1"), 2);
	$coef = ($max_pts - $min_pts)/($max-1);

	$res = $db->get_results("select tag_words, count(*) as count $from_where order by count desc limit 40");
	if ($res) {
		echo '<div class="right-box">';
		foreach ($res as $item) {
			$size = round($min_pts + ($item->count-1)*$coef);
			echo '<a style="font-size: '.$size.'pt" href="index.php?search=tag:'.urlencode($item->tag_words).'">'.$item->tag_words.'</a> ';
			//echo "$item->tag_words ($item->count) ";
		}
		echo '</div>';
	}
}
?>
