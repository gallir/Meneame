<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

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

$db->transaction();
$exists = total_subs($user, $id);
if (empty($_POST['change'])) {
	$dict['value'] = $exists;
} else {
	if ($exists) {
		delete_subs($user, $id);
		$dict['value'] = 0;
	} else {
		insert_sub($user, $id);
		$dict['value'] = 1;
	}
}
$db->commit();

echo json_encode($dict);

// end

function error($mess) {
	$dict['error'] = $mess;
	echo json_encode($dict);
    die;
}

function total_subs($uid, $id = false) {
	global $db;

	if ($id > 0) {
		$extra = "and pref_value=$id";
	}
	return intval($db->get_var("SELECT SQL_NO_CACHE count(*) FROM prefs WHERE pref_user_id=$uid and pref_key='sub_follow' $extra"));
}

function insert_sub($uid, $id) {
	global $db;

	$total = total_subs($uid);
	$db->query("REPLACE INTO prefs (pref_user_id, pref_key, pref_value) VALUES ($uid, 'sub_follow', $id)");

	// Check if it's the first subscription, if so, add the defaults
	if ($total == 0) {
		$site = SitesMgr::my_parent();
		$defaults = SitesMgr::get_sub_subs_ids($site);
		foreach ($defaults as $s) {
			$db->query("REPLACE INTO prefs (pref_user_id, pref_key, pref_value) VALUES ($uid, 'sub_follow', $s)");
		}
	}
}

function delete_subs($uid, $id = false) {
	global $db;

	if ($id > 0) {
		$extra = "and pref_value=$id";
	}
	$db->query("delete from prefs where pref_user_id=$uid and pref_key='sub_follow' $extra");
	if ($id > 0) { // If we deleted a specific sub, check
		check_delete_defaults($uid);
	}
}

function check_delete_defaults($uid) {
	// Check if the user is suscribed to all default
	global $db;

	// Get original site
	$site = SitesMgr::my_parent();
	$defaults = SitesMgr::get_sub_subs_ids($site);

	$suscriptions = $db->get_col("select pref_value from prefs where pref_user_id=$uid and pref_key='sub_follow'");
	if (count($defaults) == count($suscriptions) && count(array_diff($defaults, $suscriptions)) == 0) {
		delete_subs($uid);
	}
}


