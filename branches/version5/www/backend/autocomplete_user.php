<?
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail dot com> and Menéame COmunicaciones
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

header('Content-Type: text/plain; charset=UTF-8');

$q = '';
if (isset($_GET['q'])) {
    $q = mb_strtolower(trim($_GET['q']));
}
if (!$q) {
    return;
}

$q = $db->escape($q);

if (isset($_GET['friends']) && $_GET['friends']) {
	if (! $current_user->user_id) return;
	$from = "users, friends";
	$where = "friend_type = 'manual' and friend_to = $current_user->user_id and friend_value > 0 and user_id = friend_from and user_login like '$q%'";
} else {
	$from = "users";
	$where = "user_login like '$q%'";
}

$users = $db->get_results("select user_id, user_login, user_avatar from $from where $where order by user_login asc limit 20");

if ($users) {
	foreach ($users as $user) {
		if (isset($_GET['avatar']) && $_GET['avatar']) {
			if ($user->user_avatar > 0) {
				$avatar = get_avatar_url($user->user_id, $user->user_avatar, 20);
			} else {
				$avatar = get_no_avatar_url(20);
			}
		} else {
			$avatar = $user->user_avatar;
		}
		echo mb_strtolower($user->user_login).'|'.$avatar."\n";
	}
}

?>
