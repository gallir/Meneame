<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (!defined('mnmpath')) {
    include('../config.php');
    include(mnminclude.'html1.php');
}

array_push($globals['cache-control'], 'no-cache');
http_cache();

if (empty($current_user->user_id)) {
    die('ERROR: '._('Esta acción sólo es posible para usuarios registrados'));
}

$post = new Post;

if (isset($_GET['id'])) {
    $post->id = (int)$_GET['id'];

    if (empty($post->id) || $post->read()) {
        $post->print_edit_form();
    }
} elseif (empty($_POST['post_id']) || preg_match('/^[0-9]+$/', $_POST['post_id'])) {
    save_post((int)$_POST['post_id']);
} else {
    die('ERROR: '._('No se ha podido obtener el post indicado'));
}

function save_post ($post_id)
{
    global $link, $db, $post, $current_user, $globals, $site_key;

    $post = new Post;

    $_POST['post'] = clean_text_with_tags($_POST['post'], 0, false, $globals['posts_len']);

    if (($current_user->user_level === 'god') && $_POST['admin']) {
        $post->admin = true;
    }

    if (!empty($_FILES['image']['tmp_name'])) {
        if ($limit_exceded = Upload::current_user_limit_exceded($_FILES['image']['size'])) {
            die('ERROR: '.$limit_exceded);
        }
    }

    if (mb_strlen($_POST['post']) < 5) {
        die('ERROR: '._('texto muy corto'));
    }

    if ($post_id > 0) {
        $post->id = $post_id;

        if (!$post->read()) {
            die('ERROR: '._('No se ha podido obtener el post indicado'));
        }

        if (
            ($_POST['key'] == $post->randkey) &&
            // Allow the author of the post
            (
                (
                    (intval($_POST['user_id']) == $current_user->user_id) &&
                    ($current_user->user_id == $post->author) &&
                    ((time() - $post->date) < $globals['posts_edit_time'])
                ) ||
                // Allow the admin
                (
                    ($current_user->user_level === 'god') &&
                    ((time() - $post->date) < ($globals['posts_edit_time_admin'] * 1.5))
                )
            )
        ) {
            $post->content = trim($_POST['post']);

            if (empty($post->content)) {
                die('ERROR: '._('no es posible guardar una nota sin contenido'));
            }

            $poll = new Poll;

            $poll->read('post_id', $post->id);
            $poll->post_id = $post->id;

            $db->transaction();

            try {
                $poll->storeFromArray($_POST);
            } catch (Exception $e) {
                die('ERROR: '.$e->getMessage());
            }

            $post->store();

            $db->commit();

            $post->poll = $poll;

            store_image($post);
        } else {
            die('ERROR: '._('no tiene permisos para grabar'));
        }
    } else {
        if ($current_user->user_id != intval($_POST['user_id'])) {
            die;
        }

        if ($current_user->user_karma < $globals['min_karma_for_posts']) {
            die('ERROR: '._('el karma es muy bajo'));
        }

        // Check the post wasn't already stored
        $post->randkey = intval($_POST['key']);
        $post->author = $current_user->user_id ;
        $post->content = $_POST['post'];

        // Verify that there are a period of $globals['posts_period'] minute between posts.
        if (intval($db->get_var("select count(*) from posts where post_user_id = $current_user->user_id and post_date > date_sub(now(), interval ".$globals['posts_period']." second)"))) {
            die('ERROR: '._('debe esperar entre notas'));
        }

        $same_text = $post->same_text_count();
        $same_links = $post->same_links_count(10);

        $r = $db->get_var("select count(*) from posts where post_user_id = $current_user->user_id and post_date > date_sub(now(), interval 5 minute) and post_randkey = $post->randkey FOR UPDATE");

        if (is_null($r) || intval($r) || $same_text) {
            die('ERROR: '._('comentario grabado previamente'));
        }

        $poll = new Poll;

        try {
            $poll->readFromArray($_POST);
        } catch (Exception $e) {
            die('ERROR: '.$e->getMessage());
        }

        $db->transaction();

        if ($same_links > 2) {
            $reduction = $same_links * 0.2;

            $user = new User($current_user->user_id);
            $user->add_karma(-$reduction, _('demasiados enlaces al mismo dominio en las notas'));

            syslog(LOG_NOTICE, "Meneame: post_edit decreasing $reduction of karma to $user->username (now $user->karma)");
        }

        $post->store();

        if ($poll->isStorable()) {
            $poll->post_id = $post->id;
            $poll->store();
        }

        $db->commit();

        $post->poll = $poll;

        store_image($post);
    }

    $post->print_summary();
}

function store_image($post)
{
    // Check image upload or delete
    if ($_POST['image_delete']) {
        $post->delete_image();
    } else {
        $post->store_image_from_form('image');
    }

    $post->media_date = time(); // To show the user the new thumbnail
}
