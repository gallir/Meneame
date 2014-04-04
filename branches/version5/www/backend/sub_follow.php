<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'favorites.php');

header('Content-Type: application/json; charset=UTF-8');

if(!($id=intval($_POST['id']))) {
	error(_('falta el ID'). " $id");
}

if (! $current_user->user_id) {
	error(_('usuario incorrecto'));
}
$user = $current_user->user_id;

if (! check_security_key($_POST['key'])) {
	error(_('clave de control incorrecta'));
}

$exists = intval($db->get_var("SELECT SQL_NO_CACHE count(*) FROM prefs WHERE pref_user_id=$user and pref_key='sub_follow' and pref_value=$id"));
if (empty($_POST['change'])) {
	$dict['value'] = $exists;
} else {
	if ($exists) {
		$db->query("delete from prefs where pref_user_id=$user and pref_key='sub_follow' and pref_value=$id");
		$dict['value'] = 0;
	} else {
		$db->query("REPLACE INTO prefs (pref_user_id, pref_key, pref_value) VALUES ($user, 'sub_follow', $id)");
		$dict['value'] = 1;
	}
}

echo json_encode($dict);

function error($mess) {
	$dict['error'] = $mess;
	echo json_encode($dict);
    die;
}

?>
