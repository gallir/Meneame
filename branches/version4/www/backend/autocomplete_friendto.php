<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

if (! $current_user->user_id) {
	do_error(_('debe autentificarse'), 403);
}

header('Content-Type: text/plain; charset=UTF-8');

$q = '';
if (isset($_GET['q'])) {
    $q = mb_strtolower(trim($_GET['q']));
}
if (!$q) {
    return;
}

$q = $db->escape($q);
$users = $db->get_col("select user_login from users, friends where friend_type = 'manual' and friend_to = $current_user->user_id and friend_value > 0 and user_id = friend_from and user_login like '$q%'");

if ($users) {
	foreach ($users as $user) {
		echo mb_strtolower($user)."\n";
	}
}

?>
