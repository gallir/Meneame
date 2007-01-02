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
	$min_pts = 9;
	$max_pts = 16;
	$line_height = $max_pts * 0.75;

	$min_date = $db->get_var("select min(link_date) from links where link_date > date_sub(now(), interval 48 hour)");
	$from_where = "FROM tags, links WHERE tag_lang='$dblang' and tag_date > '$min_date' and link_id = tag_link_id and link_status $status GROUP BY tag_words";
	$max = max($db->get_var("select count(*) as words $from_where order by words desc limit 1"), 3);
	$coef = ($max_pts - $min_pts)/($max-1);

	$res = $db->get_results("select tag_words, count(*) as count $from_where order by count desc limit 30");
	if ($res) {
		echo '<div class="right-box">';
		echo '<h2><a href="'.$globals['base_url'].'cloud.php">'._('etiquetas').'</a></h2>'."\n";
		foreach ($res as $item) {
			$size = round($min_pts + ($item->count-1)*$coef, 1);
			echo '<a style="font-size: '.$size.'pt" href="'.$globals['base_url'].'?search=tag:'.urlencode($item->tag_words).'">'.$item->tag_words.'</a> ';
			//echo "$item->tag_words ($item->count) ";
		}
		echo '</div>';
	}
}

function do_last_comments() {
	global $db, $globals, $dblang;
	$foo_link = new Link();

	$res = $db->get_results("select comment_id, comment_order, user_login, link_id, link_uri, link_title from comments, links, users where comment_link_id = link_id and comment_user_id = user_id order by comment_date desc limit 10");
	if ($res) {
		echo '<div class="right-box">';
		echo '<h2>' . _('últimos comentarios'). '</h2><ul>';
		foreach ($res as $comment) {
			$foo_link->uri = $comment->link_uri;
			$link = $foo_link->get_permalink() . '#comment-'.$comment->comment_order;
			echo '<li>'.$comment->user_login.' '._('en').' <a  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_comment_tooltip.php\', \''.$comment->comment_id.'\', 10000);" href="'.$link.'">'.$comment->link_title.'</a></li>'."\n";
		}
		echo '</ul></div>';
	}
}
?>
