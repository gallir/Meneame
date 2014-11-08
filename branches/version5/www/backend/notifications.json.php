<?php
// The source code packaged with this file is Free Software, Copyright (C) 2012 by
// Ricardo Galli <gallir at gallir dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// Use the alternate server for api, if it exists
//$globals['alternate_db_server'] = 'backend';

include('../config.php');
$db->connect_timeout = 3;

if (! $current_user->user_id) die;

if (! empty($_GET['redirect'])) {
	do_redirect($_GET['redirect']);
	exit(0);
}

header('Content-Type: application/json; charset=utf-8');
http_cache(5);

$notifications = new stdClass();

$notifications->posts = (int) Post::get_unread_conversations($current_user->user_id);
$notifications->comments = (int) Comment::get_unread_conversations($current_user->user_id);
$notifications->privates = (int) PrivateMessage::get_unread($current_user->user_id);
$notifications->friends = count(User::get_new_friends($current_user->user_id));

$notifications->total = $notifications->posts + $notifications->privates + $notifications->friends + $notifications->comments;
echo json_encode($notifications);


function do_redirect($type) {
	global $globals, $current_user;
	$url = '/'; // If everything fails, it will be redirected to the home

	switch ($type) {
		case 'privates':
			$url = post_get_base_url('_priv');
			break;
		case 'posts':
			$url = post_get_base_url($current_user->user_login) . '/_conversation';
			break;
		case 'comments':
			$url = get_user_uri($current_user->user_login, 'conversation');
			break;
		case 'friends':
			$url = get_user_uri($current_user->user_login, 'friends_new');
			break;
	}
	header("HTTP/1.1 302 Moved");
	header('Location: ' . $url);
	header("Content-Length: 0");
}

