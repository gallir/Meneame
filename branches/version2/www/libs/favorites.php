<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function favorite_exists($user, $link) {
		global $db;
		return intval($db->get_var("SELECT SQL_NO_CACHE count(*) FROM favorites WHERE favorite_user_id=$user and favorite_link_id=$link"));
}

function favorite_insert($user, $link) {
	global $db, $globals;
	return $db->query("REPLACE INTO favorites (favorite_user_id, favorite_link_id) VALUES ($user, $link)");
}

function favorite_delete($user, $link) {
		global $db;
		return $db->query("DELETE FROM favorites WHERE favorite_user_id=$user and favorite_link_id=$link");
}

function favorite_add_delete($user, $link) {
	global $globals;
	if(favorite_exists($user, $link)) {
		favorite_delete($user, $link);
		return '<img src="'.$globals['base_url'].'img/common/heart_add.png" alt="add" width="16" height="16" />';
	} else {
		favorite_insert($user, $link);
		return '<img src="'.$globals['base_url'].'img/common/heart_delete.png" alt="del" width="16" height="16" />';
	}
}

function favorite_teaser($user, $link) {
	global $globals;
	if (favorite_exists($user, $link)) {
		return '<img src="'.$globals['base_url'].'img/common/heart_delete.png" alt="del" width="16" height="16" />';
	} else {
		return '<img src="'.$globals['base_url'].'img/common/heart_add.png" alt="add" width="16" height="16" />';
	}
}
