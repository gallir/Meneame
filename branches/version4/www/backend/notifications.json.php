<?
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

header('Content-Type: application/json; charset=utf-8');
http_cache(5);

if (isset($_GET['totals'])) {
	$do_totals = true;
} else {
	$do_totals = false;
}


$cache_key = 'notifications_'.$current_user->user_id.'_'.$do_totals;
if(memcache_mprint($cache_key)) {
    exit(0);
}

$notifications = new stdClass();

$notifications->posts = Post::get_unread_conversations($current_user->user_id);
$notifications->comments = Comment::get_unread_conversations($current_user->user_id);
$notifications->privates = PrivateMessage::get_unread($current_user->user_id);
$notifications->friends = count(User::get_new_friends($current_user->user_id));

if ($do_totals) {
	$notifications->total = $notifications->posts + $notifications->privates + $notifications->friends + $notifications->comments;
	$response = json_encode($notifications);
} else {
	$objects = array();

	$obj = new stdClass();
	$obj->count = $notifications->privates;
	$obj->text = _('privados nuevos');
	$obj->icon = $globals['base_static'].'img/common/icon_message-02.png';
	$obj->width=19;
	$obj->height=19;
	$obj->url = post_get_base_url('_priv');
	$objects[] = $obj;

	$obj = new stdClass();
	$obj->count = $notifications->posts;
	$obj->text = _('respuestas a notas');
	$obj->icon = $globals['base_static'].'img/common/icon_post-02.png';
	$obj->width=19;
	$obj->height=19;
	$obj->url = post_get_base_url($current_user->user_login) . '/_conversation';
	$objects[] = $obj;

	$obj = new stdClass();
	$obj->count = $notifications->comments;
	$obj->text = _('respuestas a comentarios');
	$obj->icon = $globals['base_static'].'img/common/icon_comment-01.png';
	$obj->width=19;
	$obj->height=19;
	$obj->url = get_user_uri($current_user->user_login, 'conversation');
	$objects[] = $obj;

	$obj = new stdClass();
	$obj->count = $notifications->friends;
	$obj->text = _('nuevos amigos');
	$obj->icon = $globals['base_static'].'img/common/icon_friend_bi_00.png';
	$obj->width=18;
	$obj->height=16;
	$obj->url = get_user_uri($current_user->user_login, 'friends_new');
	$objects[] = $obj;

	$response = json_encode($objects);
}

memcache_madd($cache_key, $response, 2);
echo $response;

