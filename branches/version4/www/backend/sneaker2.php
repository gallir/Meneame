<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2010 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'sneak.php');
include(mnminclude.'ban.php');

$foo_link = new Link;
$events = array();
$data = array();

// The client requests version number
if (!empty($_REQUEST['getv'])) {
	header('Content-Type: text/plain; charset=UTF-8');
	echo $globals['sneak_version'];
	die;
}

header('Content-Type: application/json; charset=UTF-8');
array_push($globals['cache-control'], 'no-cache, no-store');
http_cache();

$now = $globals['now'];
$now_f = round(microtime(true), 2);

if(!($time_f=floatval($_REQUEST['time'])) > 0 || $now-$time_f > 1200) {
	$time_f = $now-1200;
}

$time = intval($time_f);
$dbtime = date("YmdHis", intval($time));
$last_timestamp = $time_f;

if(!empty($_REQUEST['items']) && intval($_REQUEST['items']) > 0) {
	$max_items = intval($_REQUEST['items']);
}

if ($max_items < 1 || $max_items > 50) {
	$max_items = 50; // Avoid abuse
}


if (empty($_REQUEST['novote']) || empty($_REQUEST['noproblem'])) get_votes($dbtime);

// Get the logs
$logs = $db->get_results("select SQL_CACHE UNIX_TIMESTAMP(log_date) as time, log_type, log_ref_id, log_user_id from logs where log_date > $dbtime order by log_date desc limit $max_items");

if ($logs) {
	foreach ($logs as $log) {
		if ($current_user->user_id > 0) {
			if(!empty($_REQUEST['friends']) && $log->log_user_id != $current_user->user_id) {
				// Check the user is a friend
				if (User::friend_exists($current_user->user_id, $log->log_user_id) <= 0) continue;
			} elseif (!empty($_REQUEST['admin']) && $current_user->admin) {
				$user_level = $db->get_var("select user_level from users where user_id=$log->log_user_id");
				if ($user_level != 'admin' && $user_level != 'god') continue;
			}

		}
		switch ($log->log_type) {
			case 'link_new':
				if (empty($_REQUEST['nonew'])) get_story($log->time, 'new', $log->log_ref_id, $log->log_user_id);
				break;
			case 'link_publish':
				if (empty($_REQUEST['nopublished'])) get_story($log->time, 'published', $log->log_ref_id, $log->log_user_id);
				break;
			case 'comment_new':
				if (User::friend_exists($current_user->user_id, $log->log_user_id) < 0) continue;
				if (empty($_REQUEST['nocomment'])) get_comment($log->time, 'comment', $log->log_ref_id, $log->log_user_id);
				break;
			case 'link_depublished':
			case 'link_discard':
				if (empty($_REQUEST['noproblem']) && empty($_REQUEST['nodiscard'])) get_story($log->time, 'discarded', $log->log_ref_id, $log->log_user_id);
				break;
			case 'link_edit':
				if (empty($_REQUEST['noedit']) && $current_user->admin) get_story($log->time, 'edited', $log->log_ref_id, $log->log_user_id);
				break;
			case 'link_geo_edit':
				if (empty($_REQUEST['nogeoedit']) && $current_user->admin) get_story($log->time, 'geo_edited', $log->log_ref_id, $log->log_user_id);
				break;
			case 'comment_edit':
				if (User::friend_exists($current_user->user_id, $log->log_user_id) < 0) continue;
				if (empty($_REQUEST['nocomment'])) get_comment($log->time, 'cedited', $log->log_ref_id, $log->log_user_id);
				break;
			case 'post_new':
				if (User::friend_exists($current_user->user_id, $log->log_user_id) < 0) continue;
				if (empty($_REQUEST['nopost'])) get_post($log->time, 'post', $log->log_ref_id, $log->log_user_id);
				break;
		}
	}
}

// Only registered users can see the chat messages
if ($current_user->user_id > 0 && empty($_REQUEST['nochat'])) {
	check_chat();
	get_chat();
}
$db->barrier();


if($last_timestamp == 0) $last_timestamp = $now_f;
if(intval($_REQUEST['r']) % 10 == 0) {
	update_sneakers($data);
	if ($current_user->user_id > 0) {
		// Update conversation counters
		$data['p_conv_c'] = Post::get_unread_conversations($current_user->user_id);
		$data['c_conv_c'] = Comment::get_unread_conversations($current_user->user_id);
		$data['n_friends_c'] = count(User::get_new_friends($current_user->user_id));
	}
}
$data['ts'] = $last_timestamp;
$data['v'] = $globals['sneak_version'];
if(count($events) > 0 ) {
	krsort($events);
}

$counter=0;
$data['events'] = array_values($events);
echo json_encode($data);

function check_chat() {
	global $db, $current_user, $now, $now_f, $globals, $events;
	if(empty($_POST['chat'])) return;
	$comment = trim(preg_replace("/[\r\n\t]/", ' ', $_REQUEST['chat']));
	$comment = clear_whitespace($comment);
	if ($current_user->user_id > 0 && strlen($comment) > 2) {
		// Sends a message back if the user has a very low karma
		if ($globals['min_karma_for_sneaker'] > 0 && $current_user->user_karma < $globals['min_karma_for_sneaker']) {
			$comment = _('no tienes suficiente karma para comentar en la fisgona').' ('.$current_user->user_karma.' < '.$globals['min_karma_for_sneaker'].')';
			send_chat_warn($comment);
			return;
		}
		$period = $now - 4;
		$counter = intval($db->get_var("select count(*) from chats where chat_time > $period and chat_uid = $current_user->user_id"));
		if ($counter > 0) {
			$comment = _('tranquilo charlatán').' ;-)';
			send_chat_warn($comment);
			return;
		}
		if(check_ban_proxy()) {
			send_chat_warn(_('proxy abierto no permitido'));
			return;
		}

		if (preg_match('/^!/', $comment)) {
			require_once('sneaker-stats.php');
			if(!($comment = check_stats($comment))) {
				send_chat_warn(_('comando no reconocido'));
			} else {
				send_string($comment);
			}
			return;
		} else {
			$comment = clean_text_with_tags($comment);
			$comment = preg_replace('/(^|[\s\.,¿#@])\/me([\s\.,\?]|$)/', "$1<i>$current_user->user_login</i>$2", $comment);
		}

		$from = $now - 1500;
		$db->query("delete from chats where chat_time < $from");
		if ((!empty($_REQUEST['admin']) || preg_match('/^#/', $comment)) && $current_user->admin) {
			$room = 'admin';
			$comment = preg_replace('/^# */', '', $comment);
		} elseif (!empty($_REQUEST['friends']) || preg_match('/^@/', $comment)) {
			$room = 'friends';
			$comment = preg_replace('/^@ */', '', $comment);
		} else {
			$room = 'all';
		}
		if (strlen($comment)>0) {
			$comment = $db->escape(trim(normalize_smileys($comment)));
			$db->query("insert into chats (chat_time, chat_uid, chat_room, chat_user, chat_text) values ($now_f, $current_user->user_id, '$room', '$current_user->user_login', '$comment')");
		}

	}
}

function send_string($mess) {
	global $current_user, $now, $globals, $events;

	$key = $now . ':chat:'.$id;
	$json['who'] = $current_user->user_login;
	$json['uid'] = $current_user->user_id;
	$json['ts'] = $now;
	$json['status'] =  _('chat');
	$json['type'] =  'chat';
	$json['votes'] = 0;
	$json['com'] = 0;
	$json['title'] = text_to_html($mess);
	$events[$key] = $json;
}

function send_chat_warn($mess) {
	$mess = '<strong>'._('Aviso').'</strong>: '.$mess;
	send_string($mess);
}

function get_chat() {
	global $db, $events, $last_timestamp, $max_items, $current_user, $time_f;

	if (!empty($_REQUEST['admin'])) $anonymizer = 'http://anonym.to/?';
	if (!empty($_REQUEST['admin']) || !empty($_REQUEST['friends'])) $chat_items = $max_items * 2;
	else $chat_items = $max_items;
	$res = $db->get_results("select * from chats where chat_time > $time_f order by chat_time desc limit $chat_items");
	if (!$res) return;
	foreach ($res as $event) {
		$json['uid'] = $uid = $event->chat_uid;
		if ($uid != $current_user->user_id) {

			// CHECK ADMIN MODE
			// If the message is for admins check this user is also admin
			if ($event->chat_room == 'admin' && ! $current_user->admin) continue;

			// If this user is in "admin" mode, check the sender is also admin
			if (!empty($_REQUEST['admin']) && $current_user->admin) {
				$user_level = $db->get_var("select user_level from users where user_id=$uid");
				if ($user_level != 'admin' && $user_level != 'god') continue;
			} else  {
				// CHECK FRIENDSHIP
				$friendship = User::friend_exists($current_user->user_id, $uid);
				$reverse_friendship = User::friend_exists($uid, $current_user->user_id);
				// This user is ignored by the writer
				if ($friendship < 0 || $reverse_friendship < 0) continue;

				if (!empty($_REQUEST['friends']) && $friendship <= 0) {
						continue;
				}
				// Check that both users are friends in case the chat is for friends
				if ($event->chat_room == 'friends') {
					if ($friendship <= 0 || $reverse_friendship <= 0) continue;
					$json['status'] = _('amigo');
				}
			}
		}

		switch ($event->chat_room) {
			case 'friends':
				$json['status'] = _('amigo');
				break;
			case 'admin':
				$json['status'] = 'admin';
				break;
			default:
				$json['status'] = _('chat');
				break;
		}

		$json['who'] = $event->chat_user;
		$json['ts'] = intval($event->chat_time);
		$json['type'] = 'chat';
		$json['votes'] = 0;
		$json['com'] = 0;
		$json['link'] = 0;


		$chat_text = put_smileys(text_to_html(preg_replace("/[\r\n]+/", ' ¬ ', preg_replace('/&&user&&/', $current_user->user_login, $event->chat_text))));

		// Add the anonymizer for links to external pages if in admin room
		if ($anonymizer) {
			if (preg_match_all('/href="(http.+?)"/', $chat_text, $matches)) {
				foreach ($matches[1] as $url) {
					$parsed = parse_url($url);
					if ($parsed['host'] != get_server_name()) {
						$anon = preg_replace('{href="'.preg_quote($url).'"}', "href=\"$anonymizer$url\"", $chat_text);
						if ($anon) $chat_text = $anon;
					}
				}
			}
		}

		$json['title'] = $chat_text;
		if ($uid >0) $json['icon'] = get_avatar_url($uid, -1, 20);
		$key = $event->chat_time . ':chat:'.$uid;

		$events[$key] = $json;
		if($event->chat_time > $last_timestamp) $last_timestamp = $event->chat_time;
	}
}


// Check last votes
function get_votes($dbtime) {
	global $db, $events, $last_timestamp, $foo_link, $max_items, $current_user;

	$res = $db->get_results("select SQL_CACHE vote_id, unix_timestamp(vote_date) as timestamp, vote_value, INET_NTOA(vote_ip_int) as vote_ip, vote_user_id, link_id, link_title, link_uri, link_status, link_date, link_votes, link_anonymous, link_comments from votes, links where vote_type='links' and vote_date > $dbtime and link_id = vote_link_id and vote_user_id != link_author order by vote_date desc limit $max_items");
	if (!$res) return;
	foreach ($res as $event) {
		if ($current_user->user_id > 0) {
			if (!empty($_REQUEST['friends']) && $event->vote_user_id != $current_user->user_id) {
				// Check the user is a friend
				if (User::friend_exists($current_user->user_id, $event->vote_user_id) <= 0) {
					continue;
				} elseif ($event->vote_value < 0) {
					// If the vote is negative, verify also the other user has selected as friend to the current one
					if (User::friend_exists($event->vote_user_id, $current_user->user_id) <= 0) {
						continue;
					}
				}
			} elseif (!empty($_REQUEST['admin']) && $current_user->admin) {
				$user_level = $db->get_var("select user_level from users where user_id=$event->vote_user_id");
				if ($user_level != 'admin' && $user_level != 'god') continue;
			}
		}
		if ($event->vote_value >= 0) {
			if ($_REQUEST['novote']) continue;
			if ($event->link_status == 'published' && $_REQUEST['nopubvotes']) continue;
		} else {
			if ($_REQUEST['noproblem']) continue;
		}
		$foo_link->id=$event->link_id;
		$foo_link->uri=$event->link_uri;
		$foo_link->get_relative_permalink();
		$uid = $event->vote_user_id;
		if($event->vote_user_id > 0) {
			$res = $db->get_row("select user_login from users where user_id = $event->vote_user_id");
			$user = $res->user_login;
		} else {
			$user= preg_replace('/\.[0-9]+$/', '', $event->vote_ip);
		}
		if ($event->vote_value >= 0) {
			$type = 'vote';
			$who = $user;
		} else {
			$type = 'problem';
			$who = get_negative_vote($event->vote_value);
			// Show user_login if she voted more than N negatives in one minute
			if($current_user->user_id > 0 && $current_user->admin) {
				$negatives_last_minute = $db->get_var("select count(*) from votes where vote_type='links' and vote_user_id=$event->vote_user_id and vote_date > date_sub(now(), interval 30 second) and vote_value < 0");
				if($negatives_last_minute > 2 ) {
					$who .= "<br>($user)";
				}
			}
		}
		$json['status'] = get_status($event->link_status);
		$json['type'] = $type;
		$json['ts'] = $event->timestamp;
		$json['votes'] = $event->link_votes+$event->link_anonymous;
		$json['com'] = $event->link_comments;
		$json['link'] = $foo_link->get_relative_permalink();
		$json['title'] = $event->link_title;
		$json['who'] = $who;
		$json['uid'] = $event->vote_user_id;
		$json['id'] = $event->link_id;
		if ($event->vote_user_id >0) $json['icon'] = get_avatar_url($event->vote_user_id, -1, 20);
		$key = $event->timestamp . ':votes:'.$event->vote_id;;
		$events[$key] = $json;
		if($event->timestamp > $last_timestamp) $last_timestamp = $event->timestamp;
	}
}


function get_story($time, $type, $linkid, $userid) {
	global $db, $events, $last_timestamp, $foo_link;
	$event = $db->get_row("select SQL_CACHE user_login, user_level, link_title, link_uri, link_status, link_votes, link_anonymous, link_comments, link_author from links, users where link_id = $linkid and user_id=$userid");
	if (!$event) return;

	$foo_link->id=$linkid;
	$foo_link->uri=$event->link_uri;
	$json['link'] = $foo_link->get_relative_permalink();
	$json['id'] = $linkid;
	$json['status'] = get_status($event->link_status);
	$json['ts'] = $time;
	$json['type'] = $type;
	$json['votes'] = $event->link_votes+$event->link_anonymous;
	$json['com'] = $event->link_comments;
	$json['title'] = $event->link_title;

	if ($event->link_author != $userid && ($event->user_level == 'admin' || $event->user_level == 'god')) {
		// Edited by admin, don't show the author
		$json['uid'] = 0;
		$json['who'] = 'admin';
	} else {
		$json['who'] = $event->user_login;
		$json['uid'] = $userid;
		if ($userid >0) $json['icon'] = get_avatar_url($userid, -1, 20);
	}

	$key = $time . ':'.$type.':'.$linkid;
	$events[$key] = $json;
	if($time > $last_timestamp) $last_timestamp = $time;
}

function get_comment($time, $type, $commentid, $userid) {
	global $db, $events, $last_timestamp, $foo_link, $max_items, $globals;
	$event = $db->get_row("select SQL_CACHE user_login, comment_user_id, comment_type, comment_order, link_id, link_title, link_uri, link_status, link_date, link_votes, link_anonymous, link_comments from comments, links, users where comment_id = $commentid and link_id = comment_link_id and user_id=$userid");
	if (!$event) return;
	$foo_link->id=$event->link_id;
	$foo_link->uri=$event->link_uri;
	$json['link'] = $foo_link->get_relative_permalink()."/000$event->comment_order";
	$json['id'] = $commentid;
	$json['status'] = get_status($event->link_status);
	$json['ts'] = $time;
	$json['type'] = $type;
	$json['votes'] = $event->link_votes+$event->link_anonymous;
	$json['com'] = $event->link_comments;
	$json['title'] = $event->link_title;
	if ( $event->comment_type == 'admin') {
		$json['who'] = get_server_name();
		$userid = 0;
	} else {
		$json['who'] = $event->user_login;
	}
	$json['uid'] = $userid;
	if ($userid >0) $json['icon'] = get_avatar_url($userid, -1, 20);
	$key = $time . ':'.$type.':'.$commentid;
	$events[$key] = $json;
	if($time > $last_timestamp) $last_timestamp = $time;
}

function get_post($time, $type, $postid, $userid) {
	global $db, $current_user, $events, $last_timestamp, $foo_link, $max_items;
	$event = $db->get_row("select SQL_CACHE user_login, post_user_id, post_content from posts, users where post_id = $postid and user_id=$userid");
	if (!$event) return;
	$json['link'] = post_get_base_url($postid);
	$json['ts'] = $time;
	$json['type'] = $type;
	$json['who'] = $event->user_login;
	$json['status'] = _('nótame');
	$json['title'] = put_smileys(text_to_summary(preg_replace('/(@[\S.-]+)(,\d+)/','$1',$event->post_content),130));
	$json['votes'] = 0;
	$json['com'] = 0;
	$json['uid'] = $userid;
	$json['id'] = $postid;
	if ($userid >0) $json['icon'] = get_avatar_url($userid, -1, 20);
	$key = $time . ':'.$type.':'.$postid;
	$events[$key] = $json;
	if($time > $last_timestamp) $last_timestamp = $time;
}

function get_status($status) {
	switch ($status) {
		case 'published':
			$status = _('publicada');
			break;
		case 'queued':
			$status = _('pendiente');
			break;
		case 'duplicated':
		case 'abuse':
		case 'autodiscard':
		case 'discard':
			$status = _('descartada');
			break;
	}
	return $status;
}

function update_sneakers(&$data) {
	global $db, $globals, $current_user;
	$key = $globals['user_ip'] . '-' . intval($_REQUEST['k']);
	$db->query("replace into sneakers (sneaker_id, sneaker_time, sneaker_user) values ('$key', unix_timestamp(now()), $current_user->user_id)");
	if($_REQUEST['r'] % 100 == 0) {
		$from = $globals['now']-120;
		$db->query("delete from sneakers where sneaker_time < $from");
	}
	$ccntu = $db->get_var("select count(*) from sneakers where sneaker_user > 0 and sneaker_id not like 'jabber/%'");
	$ccntj = $db->get_var("select count(*) from sneakers where sneaker_user > 0 and sneaker_id like 'jabber/%'");
	$ccnta = $db->get_var("select count(*) from sneakers where sneaker_user = 0");
	$ccnt = $ccntu+$ccnta+$ccntj . " ($ccntu+$ccntj+$ccnta)";
	$data['ccnt'] = $ccnt;
}

function update_conversations(&$data) {
	global $db, $globals, $current_user;

	$data['p_conv_c'] = Post::get_unread_conversations($current_user->user_id);
	$data['c_conv_c'] = Comment::get_unread_conversations($current_user->user_id);
}

?>
