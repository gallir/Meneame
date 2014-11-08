<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

global $globals;

function favorite_exists($user, $link, $type='link') {
		global $db;

	$type = $db->escape($type);
	return intval($db->get_var("SELECT SQL_NO_CACHE count(*) FROM favorites WHERE favorite_user_id=$user and favorite_type='$type' and favorite_link_id=$link"));
}

function favorite_insert($user, $link, $type='link') {
	global $db, $globals;

	$type = $db->escape($type);
	return $db->query("REPLACE INTO favorites (favorite_user_id, favorite_type, favorite_link_id) VALUES ($user, '$type', $link)");
}

function favorite_delete($user, $link, $type='link') {
	global $db;

	$type = $db->escape($type);
	return $db->query("DELETE FROM favorites WHERE favorite_user_id=$user and favorite_type='$type' and favorite_link_id=$link");
}

function favorite_add_delete($user, $link, $type='link') {
	global $globals;
	if(favorite_exists($user, $link, $type)) {
		favorite_delete($user, $link, $type);
		return 0;
	} else {
		favorite_insert($user, $link, $type);
		return 1;
	}
}

