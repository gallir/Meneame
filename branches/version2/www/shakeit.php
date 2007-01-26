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

//header('Cache-Control: max-age=0, must-revalidate');
header('Cache-Control: no-cache');

$offset=(get_current_page()-1)*$page_size;
$globals['ads'] = true;


do_header(_('noticias pendientes'));
do_banner_top();
echo '<div id="'.$globals['css_container'].'">'."\n";
echo '<div id="contents">'."\n";
do_tabs("main","shakeit");
$order_by = " ORDER BY link_date DESC ";


$view = clean_input_string($_REQUEST['view']);
$cat = check_integer('category');

// Select friends if it's the default for the user and there is no extra arguments
if ($current_user->user_id > 0 && ($current_user->user_comment_pref & 2) > 0) {
	$globals['link_to_all'] = '?view=all';
	if (empty($view)) 
		$view = 'friends';
}

switch ($view) {
	case 'friends':
		$from_time = '"'.date("Y-m-d H:00:00", time() - $globals['time_enabled_votes']).'"';
		$from_where = "FROM links, friends WHERE link_date >  $from_time and link_status='queued' and friend_type='manual' and friend_from = $current_user->user_id and friend_to=link_author";
		$order_by = " ORDER BY link_date DESC ";	
		print_shakeit_tabs(2);
		$globals['tag_status'] = 'queued';
		break;
	case 'popular':
		// Show  the hihgher karma first
		$from_time = '"'.date("Y-m-d H:00:00", time() - 86400*2).'"';
		$from_where = "FROM links WHERE link_date > $from_time and link_status='queued' and link_karma > 10";
		$order_by = " ORDER BY link_karma DESC ";	
		print_shakeit_tabs(3);
		$globals['tag_status'] = 'queued';
		break;
	case 'discarded':
		// Show only discarded in four days
		$from_time = '"'.date("Y-m-d H:00:00", time() - 86400*4).'"';
		$from_where = "FROM links WHERE link_date > $from_time and link_status='discard' and (link_votes >0 || link_author = $current_user->user_id)";
		print_shakeit_tabs(5);
		$globals['tag_status'] = 'discard';
		break;
	case 'all':
	default:
		$globals['tag_status'] = 'queued';
		// Show last in seven days
		$from_time = '"'.date("Y-m-d H:00:00", time() - $globals['time_enabled_votes']).'"';
		if ($globals['meta_current'] > 0) {
			$from_where = "FROM links WHERE link_status='queued' and link_category in (".$globals['meta_categories'].") ";
			print_shakeit_tabs();
		} else {
			$from_where = "FROM links WHERE link_date > $from_time and link_status='queued'";
			print_shakeit_tabs(1);
		}
		break;
}

// fora en posar dropdown echo '</div>';  // Left margin
// end of tabs

if($cat) {
	$from_where .= " AND link_category=$cat ";
}

$link = new Link;
$rows = $db->get_var("SELECT count(*) $from_where");
$links = $db->get_col("SELECT link_id $from_where $order_by LIMIT $offset,$page_size");
if ($links) {
	foreach($links as $link_id) {
		$link->id=$link_id;
		$link->read();
		$link->print_summary();
	}
}
do_pages($rows, $page_size);
echo '</div>'."\n";
do_sidebar_shake();
do_rightbar();
do_footer();


function do_sidebar_shake() {
	global $db, $dblang, $globals;

	echo '<div id="sidebar">';

	do_mnu_faq('shakeit');
	do_mnu_submit();
	do_mnu_sneak();

	// Categories box

	do_mnu_categories ('shakeit', check_integer('category'));

	//do_banner_right_a(); // right side banner
	do_mnu_tools();
	do_mnu_bugs();
	do_mnu_rss();
	echo '</div>'. "\n";

}

function print_shakeit_tabs($option=-1) {
	global $globals, $current_user, $db;

	$active = array();
	if ($option > 0) {
		$active[$option] = 'class="tabsub-this"';
	}

	echo '<ul class="tabsub-shakeit">'."\n";
	echo '<li><a '.$active[1].' href="'.$globals['base_url'].'shakeit.php'.$globals['link_to_all'].'"><strong>'._('todas'). '</strong></a></li>'."\n";
	// Do metas' list
	$metas = $db->get_results("SELECT category_id, category_name, category_uri FROM categories WHERE category_parent = 0 ORDER BY category_id ASC");
	if ($metas) {
		foreach ($metas as $meta) {
			if ($meta->category_id == $globals['meta_current']) $active_meta = 'class="tabsub-this"';
			else $active_meta = '';
			echo '<li><a '.$active_meta.' href="'.$globals['base_url'].'shakeit.php?meta='.$meta->category_uri.'"><strong>'.$meta->category_name. '</strong></a></li>'."\n";
		}
	}

	if ($current_user->user_id > 0) {
		echo '<li><a '.$active[2].' href="'.$globals['base_url'].'shakeit.php?view=friends">'._('amigos'). '</a></li>'."\n";
	}
	echo '<li><a '.$active[3].' href="'.$globals['base_url'].'shakeit.php?view=popular">'._('popular'). '</a></li>'."\n";
	echo '<li><a '.$active[5].' href="'.$globals['base_url'].'shakeit.php?view=discarded">'._('descartadas'). '</a></li>'."\n";
	echo '</ul>'."\n";
}

?>
