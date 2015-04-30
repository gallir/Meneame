<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
	include('../config.php');
	include(mnminclude.'html1.php');
}

array_push($globals['cache-control'], 'no-cache');
http_cache();

$message = new PrivateMessage;

if (!empty($_POST['author'])) {
	$message_id = intval($_REQUEST['id']);
	if ($message_id > 0) {
		save_post($message_id);
	} else {
		save_post(0);
	}
} else {
	if (!empty($_REQUEST['id'])) {
			if (($message = PrivateMessage::from_db(intval($_REQUEST['id'])))) $message->print_edit_form();
	} else {
		// A new post
		$message = new PrivateMessage;
		if (!empty($_REQUEST['user_id'])) {
			$message->to = intval($_REQUEST['user_id']);
		}
		$message->author = $current_user->user_id;
		$message->print_edit_form();
	}
}

function save_post ($message_id) {
	global $link, $db, $message, $current_user, $globals, $site_key;


	$message = new PrivateMessage;

	$to_user = User::get_valid_username($_POST['to_user']);
	if (! $to_user) {
		echo 'ERROR: ' . _('nombre de usuario erróneo');
		die;
	}

	$to = User::get_user_id($to_user);
	if (! $to > 0) {
		echo 'ERROR: ' . _('usuario erróneo');
		die;
	}

	if ( ! PrivateMessage::can_send($current_user->user_id, $to) ) {
		echo 'ERROR: ' . _('el destinatario no lo tiene amigado');
		die;
	}

	$_POST['post'] = clean_text_with_tags($_POST['post'], 0, false, $globals['posts_len']);

	if (!empty($_FILES['image']['tmp_name'])) {
		$limit_exceded = Upload::current_user_limit_exceded($_FILES['image']['size']);
		if ($limit_exceded) {
			echo 'ERROR: ' . $limit_exceded;
			die;
		}
	}

	if (mb_strlen($_POST['post']) < 2) {
		echo 'ERROR: ' . _('texto muy corto');
		die;
	}

	if ($current_user->user_id != intval($_POST['author'])) die;

	// Check the post wasn't already stored
	$message->randkey = intval($_POST['key']);
	$message->author = $current_user->user_id;
	$message->to = $to;
	$message->content = $_POST['post'];

	$db->transaction();
	$dupe = intval($db->get_var("select count(*) from privates where user = $current_user->user_id and date > date_sub(now(), interval 5 minute) and randkey = $message->randkey FOR UPDATE"));
	if (! $dupe) {
		// Verify that there are a period of 1 minute between posts.
		if(intval($db->get_var("select count(*) from privates where user= $current_user->user_id and date > date_sub(now(), interval 15 second)"))> 0) {
			echo 'ERROR: ' . _('debe esperar 15 segundos entre mensajes');
			die;
		};

		// Verify that there less than X messages from the same user in a day
		if(intval($db->get_var("select count(*) from privates where user= $current_user->user_id and date > date_sub(now(), interval 1 day)"))> 160) {
			echo 'ERROR: ' . _('demasiados mensajes en un día');
			die;
		};
		$db->commit();
		$message->store();
		notify_user($current_user->user_id, $to, $message->content);
		User::add_notification($message->to, 'private');
	} else {
		$db->commit();
		echo 'ERROR: ' . _('mensaje grabado previamente');
		die;
	}

	// Check image upload or delete
	if ($_POST['image_delete']) {
		$message->delete_image();
	} elseif (!empty($_POST['tmp_filename']) && !empty($_POST['tmp_filetype']) ) {
		$message->move_tmp_image($_POST['tmp_filename'], $_POST['tmp_filetype']);
	} elseif(!empty($_FILES['image']['tmp_name'])) {
		$message->store_image($_FILES['image']);
	}

	$message = PrivateMessage::from_db($message->id); // Reread the object
	$message->print_summary();
}

function notify_user($from, $to, $text) {
	$sender = new User($from);
	$user = new User($to);
	if (! $user || ! $sender) return;

	if (! check_email($user->email)) return;
	if (! User::get_pref($to, 'notify_priv')) return;

	$url = 'http://'.get_server_name().post_get_base_url('_priv');
	$subject = "Notificación de mensaje privado de $sender->username";
	$message = "$sender->username " . _('escribió') . ":\n$url\n\n$text";
	require_once(mnminclude.'mail.php');
	send_mail($user->email, $subject, $message);
}
?>
