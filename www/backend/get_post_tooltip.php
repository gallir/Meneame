<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>
if (! defined('mnmpath')) {
    require_once __DIR__ . '/../config.php';

    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: public, s-maxage=300');
}

if (empty($_GET['id'])) {
    die();
}

if (preg_match('/(.+)-(\d+)/u', $_GET['id'], $matches)) {
    $id = 0;
    $date = $matches[2];
    $user_id = explode(',', $matches[1]);

    if (count($user_id) === 2) {
        $user = $db->escape($user_id[0]);
        $post_id = intval($user_id[1]);
    } else {
        $user = $db->escape($matches[1]);
        $date = $matches[2];
        $post_id = 0;
    }

    if ($post_id) {
        $id = (int)$db->get_var('
            SELECT post_id FROM posts, users
            WHERE (
                post_user_id = user_id
                AND post_id = "'.(int)$post_id.'"
            )
            ORDER BY post_date DESC
            LIMIT 1;
        ');
    }

    // In case of not found in previous case or postid was not given
    if ($id === 0) {
        $id = (int)$db->get_var('
            SELECT post_id
            FROM posts, users
            WHERE (
                user_login = "'.$user.'"
                AND post_user_id = user_id
                AND post_date < FROM_UNIXTIME('.$date.')
            )
            ORDER BY post_date DESC
            LIMIT 1;
        ');
    }

    if ($id === 0) {
        // Check if the user exists
        $uid = (int)$db->get_var('SELECT user_id FROM users WHERE user_login = "'.$user.'" LIMIT 1;');

        if ($uid === 0) {
            die('<strong>Error: </strong>' . _('usuario inexistente'));
        }

        // Redirect to the user info backend
        die(header('Location: get_user_info.php?id='.$uid));
    }
} else {
    $id = intval($_GET['id']);
}

$post = new Post;
$post->id = $id;
$post->read();

$post->read or die();

$post->show_avatar = true;

echo '<div class="comment-body">';

if (
    ($current_user->user_id > 0)
    && !$current_user->admin
    && (User::friend_exists($current_user->user_id, $post->author) < 0)
) {
    echo _('[USUARIO IGNORADO]');
} else {
    $post->print_text();
}

echo '</div>';
