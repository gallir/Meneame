<?
// The source code packaged with this file is Free Software, Copyright (C) 2008 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'linkmobile.php');
include(mnminclude.'commentmobile.php');
include(mnminclude.'html1-mobile.php');

$globals['ads'] = true;

$link = new LinkMobile;


if (!isset($_REQUEST['id']) && !empty($_SERVER['PATH_INFO'])) {
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO'], 3, PREG_SPLIT_NO_EMPTY);
	$link->uri = $db->escape($url_args[0]);
	if (! $link->read('uri') ) {
		not_found();
	}
} else {
	$url_args = preg_split('/\/+/', $_REQUEST['id']);
	$link->id=intval($url_args[0]);
	if(is_numeric($url_args[0]) && $link->read('id') ) {
		// Redirect to the right URL if the link has a "semantic" uri
		if (!empty($link->uri) && !empty($globals['base_story_url'])) {
			if (!empty($url_args[1])) $extra_url = '/' . urlencode($url_args[1]);
			header('Location: ' . $link->get_permalink(). $extra_url);
			die;
		}
	} else {
		not_found();
	}
}


if ($link->is_discarded()) {
	// Dont allow indexing of discarded links
	if ($globals['bot']) not_found();
	$globals['ads'] = false;
}


// Check for a page number which has to come to the end, i.e. ?id=xxx/P or /story/uri/P
$last_arg = count($url_args)-1;
if ($last_arg > 0) {
	if ($url_args[$last_arg] > 0) {
		$requested_page = $current_page =  (int) $url_args[$last_arg];
		array_pop($url_args);
	}
}

$order_field = 'comment_order';

if ($globals['comments_page_size'] && $link->comments > $globals['comments_page_size']*$globals['comments_page_threshold']) {
	if (!$current_page) $current_page = 1; // previously: ceil($link->comments/$globals['comments_page_size']);
	$offset=($current_page-1)*$globals['comments_page_size'];
	$limit = "LIMIT $offset,".$globals['comments_page_size'];
} 


if ($_POST['process']=='newcomment') {
    $comment = new Comment;
    $new_comment_error = $comment->save_from_post($link);
}

// Set globals
$globals['link'] = $link;
$globals['link_id'] = $link->id;
$globals['link_permalink'] = $globals['link']->get_permalink();

// to avoid search engines penalisation
if ($link->status == 'discard') {
	$globals['noindex'] = true;
}


do_header($link->title, 'post');

do_tabs("main",_('noticia'), true);


echo '<div id="newswrap">'."\n";
$link->print_summary();

do_comment_pages($link->comments, $current_page);

echo '<div class="comments">';


$comments = $db->get_col("SELECT SQL_CACHE comment_id FROM comments WHERE comment_link_id=$link->id ORDER BY $order_field $limit");
if ($comments) {
	echo '<ol class="comments-list">';
	$comment = new CommentMobile;
	foreach($comments as $comment_id) {
		$comment->id=$comment_id;
		$comment->read();
		echo '<li>';
		$comment->print_summary($link, 700, true);
		echo '</li>';
		echo "\n";
	}
	echo "</ol>\n";
}

echo '</div>' . "\n";

if($link->date > $globals['now']-$globals['time_enabled_comments'] && $link->comments < $globals['max_comments'] && 
	$current_user->authenticated && 
	($current_user->user_karma > $globals['min_karma_for_comments'] || $current_user->user_id == $link->author)) {
        print_comment_form();
}

do_comment_pages($link->comments, $current_page);

echo '</div>';

$globals['tag_status'] = $globals['link']->status;
do_footer();

// Show the error if the comment couldn't be inserted
if (!empty($new_comment_error)) {
    echo '<script type="text/javascript">';
    echo '$(function(){alert(\''._('comentario no insertado'). ":  $new_comment_error".'\')});';
    echo '</script>';
}



function do_comment_pages($total, $current) {
	global $db, $globals;

	if ( ! $globals['comments_page_size'] || $total <= $globals['comments_page_size']*$globals['comments_page_threshold']) return;
	
	if (! empty($globals['base_story_url'])) {
		$query = $globals['link_permalink'];
	} else {
		$query=preg_replace('/\/[0-9]+(#.*)$/', '', $_SERVER['QUERY_STRING']);
		if(!empty($query)) {
			$query = htmlspecialchars($query);
			$query = "?$query";
		}
	}

	$total_pages=ceil($total/$globals['comments_page_size']);
	if (! $current) $current = 1;
	
	echo '<div class="pages">';

	if($current==1) {
		echo '<span class="nextprev">&#171;</span>';
	} else {
		$i = $current-1;
		echo '<a href="'.get_comment_page_url($i, $total_pages, $query).'">&#171;</a>';
	}

	$dots_before = $dots_after = false;
	for ($i=1;$i<=$total_pages;$i++) {
		if($i==$current) {
			echo '<span class="current">'.$i.'</span>';
		} else {
			if ($total_pages < 5 || $i < 2 || abs($i-$total_pages) < 1) {
				echo '<a href="'.get_comment_page_url($i, $total_pages, $query).'" title="'._('ir a página')." $i".'">'.$i.'</a>';
			} else {
				if ($i<$current && !$dots_before) {
					$dots_before = true;
					echo '<span class="empty">...</span>';
				} elseif ($i>$current && !$dots_after) {
					$dots_after = true;
					echo '<span class="empty">...</span>';
				}
			}
		}
	}

	if($current<$total_pages) {
		$i = $current+1;
		echo '<a href="'.get_comment_page_url($i, $total_pages, $query).'">&#187;</a>';
	} else {
		echo '<span class="nextprev">&#187;</span>';
	}
	echo "</div>\n";

}

function get_comment_page_url($i, $total, $query) {
	global $globals;
	if ($i == 1) return $query;
	else return $query.'/'.$i;
}

function print_comment_form() {
	global $link, $current_user, $globals;

	if (!$link->votes > 0) return;
	echo '<form action="" method="post">'."\n";
	echo '<fieldset>'."\n";
	echo '<label for="comment">'. _('texto del comentario').'</label>'."\n";
	echo '<div><textarea name="comment_content" id="comment" rows="8"></textarea></div>'."\n";
	echo '<input type="submit" name="submit" value="'._('enviar').'" />'."\n";
	// Allow gods to put "admin" comments which does not allow votes
	if ($current_user->user_level == 'god') {
		echo '&nbsp;&nbsp;<label>'._('admin').' <input name="type" type="checkbox" value="admin"/></label>'."\n";
	}
	echo '<input type="hidden" name="process" value="newcomment" />'."\n";
	echo '<input type="hidden" name="randkey" value="'.rand(1000000,100000000).'" />'."\n";
	echo '<input type="hidden" name="link_id" value="'.$link->id.'" />'."\n";
	echo '<input type="hidden" name="user_id" value="'.$current_user->user_id.'" />'."\n";
	echo "</fieldset>\n";
	echo '</form>'."\n";

}
?>
