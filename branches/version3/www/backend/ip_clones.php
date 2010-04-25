<?php
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at uib dot es> and 
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>

include_once('../config.php');
include_once('pager.php');

header('Content-Type: text/html; charset=utf-8');

if (! $current_user->admin) {
	echo _('usuario no autorizado');
	die;
}

$user_id = intval($_GET['id']);
if (! $user_id > 0) {
	echo _('usuario no identificado');
	die;
}

if (!isset($_GET['p']))  {
    $users_page = 1;
} else $users_page = intval($_GET['p']);

$users_page_size = 20;
$users_offset=($users_page-1)*$users_page_size;


$from = "and clon_date > date_sub(now(), interval 30 day)";

$nclones = $db->get_var("select count(distinct user_id) from clones, users where clon_from = $user_id and user_id = clon_to $from");
$clones = $db->get_results("select distinct user_id, user_login, user_avatar from clones, users where clon_from = $user_id and user_id = clon_to $from order by clon_date desc limit $users_offset,$users_page_size");

if (! $clones) {
	print _('no hay clones para este usuario');
	die;
}

echo '<div style="padding-top: 20px">';
echo '<div class="voters-list">';

foreach ($clones as  $clon) {
	$highlight = '';
	$details = '';
	$ips = $db->get_col("select distinct clon_ip from clones where clon_from = $user_id and clon_to = $clon->user_id $from");
	foreach ($ips as $ip) {
		$details .= preg_replace('/\.[0-9]+$/', '', $ip).', ';
		if (preg_match('/COOK:/', $ip)) {
			$highlight = 'style="color:#ff0000"';
		}
	}
	echo '<div class="item" '.$highlight.'>';
	echo '<a '.$highlight.' href="'.get_user_uri($clon->user_login).'/'.$clon->user_id.'" title="'.$details.'" target="_blank">';
	echo '<img src="'.get_avatar_url($clon->user_id, $clon->user_avatar, 20).'" width="20" height="20" alt=""/>';
   	echo $clon->user_login.'</a>';
	echo '</div>';
}

echo "</div>\n";

do_contained_pages($user_id, $nclones, $users_page, $users_page_size, 'ip_clones.php', 'voters', 'modalContent');
echo '</div>';

?>
