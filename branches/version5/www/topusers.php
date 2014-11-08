<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

include_once(mnminclude.'ban.php');

$page_size = $globals['page_size'] * 2;

$items = array(_('usuario'),  _('karma'), _('noticias'), _('noticias publicadas'), _('comentarios'), _('votos últimos 2 meses'));

if (check_ban($globals['user_ip'], 'proxy')) {
	return;
}


// Warn, empty() return true even if sortby=0
if ( !strlen($_GET['sortby']) > 0) {
	$sortby = 1;
} else {
	$sortby = intval($_GET['sortby']);
	if($sortby < 0 || $sortby > 3)
		$sortby = 1;
}

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
		$from_where = " FROM links, users WHERE user_level not in ('disabled', 'autodisabled') and link_author=user_id GROUP BY link_author";
		$order_by = " ORDER BY count DESC ";
		break;
	case 3:
		$select = "SELECT user_id, count(*) as count ";
		$from_where = " FROM links, users WHERE user_level not in ('disabled', 'autodisabled') and link_status = 'published' AND link_author=user_id GROUP BY link_author";
		$order_by = " ORDER BY count DESC ";
		break;
	case 4:
		$select = "SELECT user_id, count(*) as count ";
		$from_where = " FROM comments, users WHERE user_level not in ('disabled', 'autodisabled') and comment_user_id=user_id GROUP BY comment_user_id";
		$order_by = " ORDER BY count DESC ";
		break;
	case 5:
		$select = "SELECT user_id, count(*) as count ";
		$from_where = " FROM votes, users WHERE vote_type='links' and vote_user_id=user_id GROUP BY vote_user_id";
		$order_by = " ORDER BY count DESC ";
		break;
}

$sql = "$select $from_where $order_by LIMIT $page_size";
if (! ($users = unserialize(memcache_mget($sql))) ) {
	$users = $db->get_results($sql);
	memcache_madd($sql, serialize($users), 3600);
}


do_header(_('usuarios') . ' | ' . $globals['site_name']);

echo '<div id="singlewrap">' . "\n";
echo '<div class="topheading"><h2>'._('estadísticas de usuarios').'</h2></div>';

echo '<table class="decorated"><tr>';

// Print headers
for($i=0; $i<count($items); $i++) {
	echo '<th class="short">';
	if($i==$sortby)
		echo '<span class="info_s">'.$items[$i].'</span>';
	elseif ($i <= 3 && $i > 0) {
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

if ($users) {
	foreach($users as $dbuser) {
		$user->id=$dbuser->user_id;
		$user->read();
		$user->all_stats();
		echo '<tr>';
		echo '<td style="font-size:100%"><a href="'.get_user_uri($user->username).'" class="tooltip u:'.$user->id.'"><img class="avatar" src="'.get_avatar_url($user->id, $user->avatar, 20).'" width="20" height="20" alt="avatar"/>&nbsp;'.$user->username.'</a></td>';
		echo '<td class="short">'.$user->karma.'&nbsp;';
		$user->print_medals();
		echo '</td>';
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
echo "</div>\n";
do_footer_menu();
do_footer();

