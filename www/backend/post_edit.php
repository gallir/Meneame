<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
	include(__DIR__.'/../config.php');
	include(mnminclude.'html1.php');
}

array_push($globals['cache-control'], 'no-cache');
http_cache();

$post = new Post;
if (!empty($_REQUEST['user_id'])) {
	$post_id = intval($_REQUEST['post_id']);
	if ($post_id > 0) {
		save_post($post_id);
	} else {
		save_post(0);
	}
} else {
	if (!empty($_REQUEST['id'])) {
		// She wants to edit the post
		$post->id = intval($_REQUEST['id']);
		if ($post->read()) $post->print_edit_form();
	} else {
		// A new post
		if (!$post->read_last($current_user->user_id) || time() - $post->date > $globals['posts_period']) {
			$post = new Post;
			$post->author=$current_user->user_id;
			$post->print_edit_form();
		} else {
			echo 'Error: ' . _('debe esperar entre notas');
			die;
		}
	}
}

function save_post ($post_id) {

	global $link, $db, $post, $current_user, $globals, $site_key;


	$post = new Post;
	$_POST['post'] = clean_text_with_tags($_POST['post'], 0, false, $globals['posts_len']);

	if ($current_user->user_level == 'god' && $_POST['admin'] == true) {
		$post->admin = true;
	}

	if (!empty($_FILES['image']['tmp_name'])) {
		$limit_exceded = Upload::current_user_limit_exceded($_FILES['image']['size']);
		if ($limit_exceded) {
			echo 'ERROR: ' . $limit_exceded;
			die;
		}
	}

	if (mb_strlen($_POST['post']) < 5) {
		echo 'ERROR: ' . _('texto muy corto');
		die;
	}
	if ($post_id > 0) {
		$post->id = $post_id;
		if (! $post->read()) die;
		if(
			// Allow the author of the post
			((intval($_POST['user_id']) == $current_user->user_id &&
			$current_user->user_id == $post->author &&
			time() - $post->date < 3600) ||
			// Allow the admin
			($current_user->user_level == 'god' && time() - $post->date < $globals['posts_edit_time_admin'] * 1.5)) &&
			$_POST['key']  == $post->randkey ) {
			$post->content=$_POST['post'];
			if (strlen($post->content) > 0 ) {
				$post->store();
				store_image($post);
			}
		} else {
			echo 'ERROR: ' . _('no tiene permisos para grabar');
			die;
		}
	} else {

		if ($current_user->user_id != intval($_POST['user_id'])) die;

		if ($current_user->user_karma < $globals['min_karma_for_posts']) {
			echo 'ERROR: ' . _('el karma es muy bajo');
			die;
		}

		// Check the post wasn't already stored
		$post->randkey=intval($_POST['key']);
		$post->author=$current_user->user_id ;
		$post->content=$_POST['post'];

		// Verify that there are a period of 1 minute between posts.
		if(intval($db->get_var("select count(*) from posts where post_user_id = $current_user->user_id and post_date > date_sub(now(), interval ".$globals['posts_period']." second)"))> 0) {
			echo 'ERROR: ' . _('debe esperar entre notas');
			die;
		};

		$same_text = $post->same_text_count();
		$same_links = $post->same_links_count(10);

		$db->transaction();
		$r = $db->get_var("select count(*) from posts where post_user_id = $current_user->user_id and post_date > date_sub(now(), interval 5 minute) and post_randkey = $post->randkey FOR UPDATE");
		$dupe = intval($r);
		if (! is_null($r) && ! $dupe && ! $same_text) {
			if ($same_links > 2) {
				$reduction = $same_links * 0.2;
				$user = new User($current_user->user_id);
				$user->add_karma(-$reduction, _('demasiados enlaces al mismo dominio en las notas'));
				syslog(LOG_NOTICE, "Meneame: post_edit decreasing $reduction of karma to $user->username (now $user->karma)");
			}
			$post->store();
			$db->commit();
			store_image($post);
		} else {
			$db->commit();
			echo 'ERROR: ' . _('comentario grabado previamente');
			die;
		}
	}


	$post->print_summary();
}

function store_image($post) {
	// Check image upload or delete
	if ($_POST['image_delete']) {
		$post->delete_image();
	} else {
		$post->store_image_from_form('image');
	}

	$post->media_date = time(); // To show the user the new thumbnail
}

