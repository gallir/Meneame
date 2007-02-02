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
	$max_pts = 17;
	$line_height = $max_pts * 0.75;

	$min_date = date("Y-m-d H:00:00", time() - 172800); // 48 hours
	if(!empty($globals['meta_categories'])) {
		$meta_cond = 'and link_category in ('.$globals['meta_categories'].')';
	}
	$from_where = "FROM tags, links WHERE tag_lang='$dblang' and tag_date > '$min_date' and link_id = tag_link_id and link_status $status $meta_cond GROUP BY tag_words";
	$max = max($db->get_var("select count(*) as words $from_where order by words desc limit 1"), 3);
	$coef = ($max_pts - $min_pts)/($max-1);

	$res = $db->get_results("select tag_words, count(*) as count $from_where order by count desc limit 30");
	if ($res) {
		echo '<div class="right-box center">';
		echo '<h4><a href="'.$globals['base_url'].'cloud.php">'._('etiquetas').'</a></h4>'."\n";
		foreach ($res as $item) {
			$words[$item->tag_words] = $item->count;
		}
		ksort($words);
		foreach ($words as $word => $count) {
			$size = round($min_pts + ($count-1)*$coef, 1);
			echo '<a style="font-size: '.$size.'pt" href="'.$globals['base_url'].'?search=tag:'.urlencode($word).'">'.$word.'</a>  ';
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
		echo '<h4>' . _('últimos comentarios'). '</h4><ul>';
		foreach ($res as $comment) {
			$foo_link->uri = $comment->link_uri;
			$link = $foo_link->get_permalink() . '#comment-'.$comment->comment_order;
			echo '<li>'.$comment->user_login.' '._('en').' <a  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_comment_tooltip.php\', \''.$comment->comment_id.'\', 10000);" href="'.$link.'">'.$comment->link_title.'</a></li>'."\n";
		}
		echo '</ul></div>';
	}
}

function do_best_comments() {
	global $db, $globals, $dblang;
	$foo_link = new Link();

	if ($globals['bot']) return; // We wont spend lot of CPU for another CPU :-)
	
	$min_date = date("Y-m-d H:00:00", time() - 22000); // about 6 hours
	$res = $db->get_results("select comment_id, comment_order, user_login, link_id, link_uri, link_title from comments, links, users  where comment_date > '$min_date' and comment_karma > 50 and comment_link_id = link_id and comment_user_id = user_id order by comment_karma desc limit 10");
	if ($res) {
		echo '<div class="right-box">';
		echo '<h4><a href="'.$globals['base_url'].'topcomments.php">'._('¿mejores? comentarios').'</a></h4><ul>'."\n";
		foreach ($res as $comment) {
			$foo_link->uri = $comment->link_uri;
			$link = $foo_link->get_permalink() . '#comment-'.$comment->comment_order;
			echo '<li>'.$comment->user_login.' '._('en').' <a  onmouseout="tooltip.clear(event);"  onclick="tooltip.clear(this);" onmouseover="return tooltip.ajax_delayed(event, \'get_comment_tooltip.php\', \''.$comment->comment_id.'\', 10000);" href="'.$link.'">'.$comment->link_title.'</a></li>'."\n";
		}
		echo '</ul></div>';
	}
}
?>
