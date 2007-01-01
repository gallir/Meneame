<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
//include(mnminclude.'link.php');
include(mnminclude.'user.php');

$globals['ads'] = true;
$page_size = 20;

$range_names  = array( _('todas'), _('24 horas'), _('última semana'), _('último mes'), _('último año'));
$range_values = array(0, 86400, 604800, 2592000, 31536000);

$offset=(get_current_page()-1)*$page_size;

$items = array(_('usuario'),  _('karma'), _('noticias'), _('noticias publicadas'), _('comentarios'), _('votos totales'));

$sortby = preg_replace('/ /', '_', $_GET['sortby']);
if(intval($sortby) < 0 || intval($sortby) > 5) 
	$sortby = 4;
else $sortby = intval($sortby); // Be safe, always!!!!


switch ($sortby) {
	case 0:
		$select = "SELECT user_id ";
		$from_where = " FROM users ";
		$order_by = " ORDER BY user_login ";
		break;
	case 1:
		$select = "SELECT user_id ";
		$from_where = " FROM users ";
		$order_by = " ORDER BY user_karma DESC ";
		break;
	case 2:
		$select = "SELECT user_id, count(*) as count ";
		$from_where = " FROM links, users WHERE  link_status != 'discard' AND link_author=user_id GROUP BY link_author";
		$order_by = " ORDER BY count DESC ";
		break;
	case 3:
		$select = "SELECT user_id, count(*) as count ";
		$from_where = " FROM links, users WHERE  link_status = 'published' AND link_author=user_id GROUP BY link_author";
		$order_by = " ORDER BY count DESC ";
		break;
	case 4:
		$select = "SELECT user_id, count(*) as count ";
		$from_where = " FROM comments, users WHERE comment_user_id=user_id GROUP BY comment_user_id";
		$order_by = " ORDER BY count DESC ";
		break;
	case 5:
		$select = "SELECT user_id, count(*) as count ";
		$from_where = " FROM votes, users WHERE vote_type='links' and vote_user_id=user_id GROUP BY vote_user_id";
		$order_by = " ORDER BY count DESC ";
		break;
}
// Sort by votes

do_header(_('usuarios'));
do_navbar('<a href="'.$globals['base_url'].'topusers.php">'._('usuarios') . '</a> &#187; ' . _('estadísticas'));
echo '<div class="topheading"><h2>'._('estadísticas de usuarios').'</h2></div>';

echo '<table><tr>';

// Print headers
for($i=0; $i<count($items); $i++) {
	echo '<th class="short">';
	if($i==$sortby) 
		echo '<span class="info_s">'.$items[$i].'</span>';
	elseif ($i <= 3) { 
		// Don't show order by votes or comment
		// Too much CPU and disk IO consuption
		echo '<a href="topusers.php?sortby='.$i.'">'.$items[$i].'</a>';
	} else {
		echo $items[$i];
	}
	echo '</th>';
}

echo '</tr>';
$user = new User;
$rows = $db->get_var("SELECT count(*) as count $from_where");
$users = $db->get_results("$select $from_where $order_by LIMIT $offset,$page_size");
if ($users) {
	foreach($users as $dbuser) {
		$user->id=$dbuser->user_id;
		$user->read();
		$user->all_stats();
		echo '<tr>';
		echo '<td><a href="'.get_user_uri($user->username).'"><img src="'.get_avatar_url($user->id, $user->avatar, 20).'" width="20" height="20" alt="avatar" />'.$user->username.'</a></td>';
		echo '<td class="short">'.$user->karma.'</td>';
		echo '<td class="short">'.$user->total_links.'</td>';
		if($user->total_links>0) 
			echo '<td class="short">'.$user->published_links.'&nbsp;('.intval($user->published_links/$user->total_links*100).'%)</td>';
		else 
			echo '<td class="short">'.$user->published_links.'&nbsp;(-)</td>';
		echo '<td class="short">'.$user->total_comments.'</td>';
		echo '<td class="short">'.$user->total_votes.'</td>';
		echo '</tr>';
	}
}
echo "</table>\n\n";
do_pages($rows, $page_size, false);
do_footer();


?>
