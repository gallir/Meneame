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
	do_navbar(_('noticias en la cola') . ' &#187; ' . _('búsqueda'));
	echo '<div id="contents">'."\n";
	echo '<h2>'._('búsqueda en pendientes'). ': "'.$search_txt.'" </h2>';
	$order_by = '';
} else {
	do_header(_('noticias pendientes'));
	do_navbar(_('noticias en la cola'));
	echo '<div id="contents">'."\n";
	echo '<h2>'._('noticias pendientes').'</h2>'."\n";
	$order_by = " ORDER BY link_date DESC ";
}

// tabs

echo '<div style="margin: 0 0 0 100px;">'."\n";
echo '<div class="sub-nav">'."\n";
// echo '<div class="shakeit-nav">'."\n";
echo '<ul>'."\n";

$view = clean_input_string($_REQUEST['view']);
$cat = check_integer('category');

switch ($view) {
	case 'discarded':
		// Show only discarded in four days
		$from_where = "FROM links WHERE link_date > date_sub(now(), interval 4 day) and link_status='discard' and (link_votes >0 || link_author = $current_user->user_id)";
		echo '<li><a href="shakeit.php">'._('todas'). '</a></li>'."\n";
		if ($current_user->user_id > 0)
			echo '<li><a href="shakeit.php?view=recommended">'._('recomendadas'). '</a></li>'."\n";
		echo '<li class="active"><a href="shakeit.php?view=discarded">'._('descartadas').'</a></li>'."\n";
		echo '</ul><br /></div>'."\n";
	break;
	case 'recommended':
		if ($current_user->user_id > 0 && !$search) {
			$threshold = $db->get_var("select friend_value from friends where friend_type='affiliate' and friend_from = $current_user->user_id and friend_to=0");
			if(!$threshold) $threshold = 0;
			else $threshold = $threshold * 0.95;
			
			// Show last in four days
			$from_where = "FROM links, friends WHERE link_date > date_sub(now(), interval 4 day) and link_status='queued' and friend_type='affiliate' and friend_from = $current_user->user_id and friend_to=link_author and friend_value > $threshold";
			$order_by = " ORDER BY link_date DESC ";	
			echo '<li><a href="shakeit.php">'._('todas'). '</a></li>'."\n";
			echo '<li class="active"><a href="shakeit.php?view=recommended">'._('recomendadas').'</a></li>'."\n";
			echo '<li><a href="shakeit.php?view=discarded">'._('descartadas'). '</a></li>'."\n";
			echo '</ul><br /></div>'."\n";
			break;
		}
	case 'all':
	default:
		if ($search) 
			$from_where = "FROM links WHERE link_status!='published' AND $search";
		else 
			// Show last in seven days
			$from_where = "FROM links WHERE link_date > date_sub(now(), interval 7 day) and link_status='queued'";
		echo '<li class="active"><a href="shakeit.php">'._('todas').'</a></li>'."\n";
		if ($current_user->user_id > 0)
			echo '<li><a href="shakeit.php?view=recommended">'._('recomendadas'). '</a></li>'."\n";
		echo '<li><a href="shakeit.php?view=discarded">'._('descartadas'). '</a></li>'."\n";
		echo '</ul><br /></div>'."\n";
	break;
}

echo '</div>';  // Left margin
// end of tabs

if($cat) {
	$from_where .= " AND link_category=$cat ";
}

$link = new Link;
$rows = $db->get_var("SELECT count(*) $from_where $order_by");
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
do_footer();


function do_sidebar_shake() {
	global $db, $dblang, $globals;

	echo '<div id="sidebar">';
	echo '<ul class="main-menu">';

/******* Disabled temporarely
	echo '<li>' . "\n";
	echo '<div class="note-temp">' . "\n";
	echo '<strong>'._('¡Tu voto es importante!').'</strong><br/><br/>';
	//echo _('Menea las noticias que te parecen m&aacute;s interesantes. Cuando reciba suficientes votos será promovida a la página principal. ') . '<br/><br/>';
	echo '<strong>'._('Usa las categorías para conseguir que la lista sea más corta. ').'</strong>'._(' Así no te perderás entradas interesantes de tus temas preferidos.')."\n";
	echo '</div>' . "\n";
	echo '</li>' . "\n";
********/

// 	echo '<li><div class="boxed"><div>';

	// Categories box

	do_categories ('shakeit', check_integer('category'));

	/*** This search is never used 
	*******************************
	echo '<li>'. "\n";
	echo '<div class="shakeit-form">'. "\n";
	echo '<label for="search">'._('búsqueda en pendientes').'</label>'; "\n";
	echo '<form class="shakeit-form" action="">'; "\n";
	echo '<input class="shakeit-form-input" type="text" id="search2" name="search" value="'; "\n";
	if (!empty($_REQUEST['search'])) echo htmlspecialchars(strip_tags($_REQUEST['search']));
	echo '"/>'; "\n";
	echo '<input class="shakeit-form-submit" type="submit" id="search-button" value="'._('buscar').'" />'; "\n";
	echo '</form>'. "\n";
	echo '</div>'. "\n";
	echo '</li>'. "\n";
	******/
	//do_banner_right_a(); // right side banner
	echo '<li><div class="mnu-bugs"><a href="http://meneame.wikispaces.com/Bugs">'._("reportar un bug").'</a></div></li>' . "\n";
	do_rss_box();
	echo '</ul>'. "\n";
	echo '</div>'. "\n";

}

?>
