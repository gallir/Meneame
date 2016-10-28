<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

global $globals;

function favorite_exists($user, $link, $type = 'link')
{
	global $db;

	$type = $db->escape($type);
	return intval($db->get_var("SELECT SQL_NO_CACHE count(*) FROM favorites WHERE favorite_user_id=$user and favorite_type='$type' and favorite_link_id=$link"));
}

function favorite_insert($user, $link, $type = 'link')
{
	global $db, $globals;

	$type = $db->escape($type);
	return $db->query("REPLACE INTO favorites (favorite_user_id, favorite_type, favorite_link_id) VALUES ($user, '$type', $link)");
}

function favorite_delete($user, $link, $type = 'link')
{
	global $db;

	$type = $db->escape($type);
	return $db->query("DELETE FROM favorites WHERE favorite_user_id=$user and favorite_type='$type' and favorite_link_id=$link");
}

function favorite_add_delete($user, $link, $type = 'link')
{
	global $globals;
	if (favorite_exists($user, $link, $type)) {
		favorite_delete($user, $link, $type);
		return 0;
	} else {
		favorite_insert($user, $link, $type);
		return 1;
	}
}

function favorite_get_readed($user, $link, $type = 'link')
{
	global $db;
	$type = $db->escape($type);

	return intval($db->get_var("SELECT SQL_NO_CACHE favorite_link_readed FROM favorites WHERE favorite_user_id=$user and favorite_type='$type' and favorite_link_id=$link"));

}


function favorite_change_read($user, $link, $type = 'link')
{
	global $db;

	$type = $db->escape($type);

	$new_read_status = favorite_get_readed($user, $link, $type) ? 0 : 1;

	$db->query("REPLACE INTO favorites (favorite_user_id, favorite_type, favorite_link_id, favorite_link_readed) VALUES ($user, '$type', $link, $new_read_status)");

	return $new_read_status;
}

function get_unread_favorites($user = 0, $type = 'link')
{
	global $db, $current_user;

	if (!$user && $current_user->user_id > 0) $user = $current_user->user_id;

	return intval($db->get_var("SELECT SQL_NO_CACHE count(*) FROM favorites WHERE favorite_user_id=$user and favorite_type='$type' and favorite_link_readed=0"));

}
