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


$search = get_search_clause();
$search_txt = htmlspecialchars(strip_tags($_REQUEST['search']));
if($search) {
	do_header(_('búsqueda de'). ' "'.$search_txt.'"');
	do_banner_top();
	echo '<div id="container">' . "\n";
	echo '<div id="contents">'."\n";
	echo '<h2>'._('búsqueda en pendientes'). ': "'.$search_txt.'" </h2>';
	$order_by = '';
} else {
	do_header(_('noticias pendientes'));
	do_banner_top();
	echo '<div id="container">' . "\n";
	echo '<div id="contents">'."\n";
	do_tabs("main","shakeit");
	$order_by = " ORDER BY link_date DESC ";
}

// dropdown

// echo '<div class="dropdown-01"><em>';
// $ul_drawn = false;

$view = clean_input_string($_REQUEST['view']);
$cat = check_integer('category');

switch ($view) {
	case 'discarded':
		// Show only discarded in four days
		$from_time = '"'.date("Y-m-d H:00:00", time() - 86400*4).'"';
		$from_where = "FROM links WHERE link_date > $from_time and link_status='discard' and (link_votes >0 || link_author = $current_user->user_id)";
		print_shakeit_tabs('4');
		$globals['tag_status'] = 'discard';
		break;
	case 'popular':
		// Show  the hihgher karma first
		$from_time = '"'.date("Y-m-d H:00:00", time() - 86400*2).'"';
		$from_where = "FROM links WHERE link_date > $from_time and link_status='queued' and link_karma > 10";
		$order_by = " ORDER BY link_karma DESC ";	
		print_shakeit_tabs('2');
		$globals['tag_status'] = 'queued';
		break;
	case 'recommended':
		if ($current_user->user_id > 0 && !$search) {
			$threshold = $db->get_var("select friend_value from friends where friend_type='affiliate' and friend_from = $current_user->user_id and friend_to=0");
			if(!$threshold) $threshold = 0;
			else $threshold = $threshold * 0.95;
			
			// Show last in four days
			$from_time = '"'.date("Y-m-d H:00:00", time() - 86400*4).'"';
			$from_where = "FROM links, friends WHERE link_date >  $from_time and link_status='queued' and friend_type='affiliate' and friend_from = $current_user->user_id and friend_to=link_author and friend_value > $threshold";
			$order_by = " ORDER BY link_date DESC ";	
			print_shakeit_tabs('3');
		}
		$globals['tag_status'] = 'queued';
		break;
	case 'all':
	default:
		if ($search)
			$from_where = "FROM links WHERE link_status!='published' AND $search";
		else
			// Show last in seven days
			$from_time = '"'.date("Y-m-d H:00:00", time() - 86400*7).'"';
			$from_where = "FROM links WHERE link_date > $from_time and link_status='queued'";
		print_shakeit_tabs('1');
		$globals['tag_status'] = 'queued';
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

function print_shakeit_tabs($option) {
	global $globals, $current_user;

	$active = array();
	$active[$option] = 'class="tabsub-this"';

	echo '<ul class="tabsub-shakeit">'."\n";
	echo '<li><a '.$active[1].' href="'.$globals['base_url'].'shakeit.php"><strong>'._('todas'). '</strong></a></li>'."\n";
	if ($current_user->user_id > 0) {
		echo '<li><a '.$active[2].' href="'.$globals['base_url'].'shakeit.php?view=popular">'._('popular'). '</a></li>'."\n";
		echo '<li><a '.$active[3].' href="'.$globals['base_url'].'shakeit.php?view=recommended">'._('recomendadas'). '</a></li>'."\n";
	}
	echo '<li><a '.$active[4].' href="'.$globals['base_url'].'shakeit.php?view=discarded">'._('descartadas'). '</a></li>'."\n";
	echo '</ul>'."\n";
}

?>
