<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

$user_id = intval($_GET['user']);

$limit = 250;
$show_all = false;

switch ($_GET['type']) {
    case 'comment':
        $type_in = '("comment")';
        break;

    case 'post':
        $type_in = '("post")';
        break;

    default:
        $type_in = '("comment", "post")';
        break;
}

if ($user_id > 0) {
    if ($current_user->user_id) {
        $show_all = true;
    }

    if ($user_id == $current_user->user_id) {
        $limit = 5000;
    } else {
        $limit = 500;
    }
}

header('Content-Type: text/html; charset=utf-8');

$media = $db->get_results(DbHelper::queryPlain('
    SELECT `id`, `type`, `version`, UNIX_TIMESTAMP(`date`) AS `date`, `mime`, `user` AS `uid`, `user_login` AS `user`
    FROM `media`, `users`
    WHERE (
        `type` IN '.$type_in.'
        AND `version` = 0
        AND `user_id` = `media`.`user`
        '.($user_id ? (' AND `user` = "'.$user_id.'"') : '').'
    )
    ORDER BY `date` DESC
    LIMIT '.$limit.';
'));

if (empty($media)) {
    return;
}

if ($show_all === false) {
    $media_ids = implode(',', array_map(function($value) {
        return $value->id;
    }, $media));

    $comments_karma = array();
    $comments = $db->get_results(DbHelper::queryPlain('
        SELECT comment_id, comment_karma
        FROM comments
        WHERE comment_id IN ('.$media_ids.');
    '));

    foreach ($comments as $comment) {
        $comments_karma[$comment->comment_id] = $comment->comment_karma;
    }

    $posts_karma = array();
    $posts = $db->get_results(DbHelper::queryPlain('
        SELECT post_id, post_karma
        FROM posts
        WHERE post_id IN ('.$media_ids.');
    '));

    foreach ($posts as $post) {
        $posts_karma[$post->post_id] = $post->post_karma;
    }
} else {
    $comments_karma = $posts_karma = array();
}

$images = array();

foreach ($media as $image) {
    $karma = null;

    if ($show_all === false) {
        switch ($image->type) {
            case 'comment':
                $karma = isset($comments_karma[$image->id]) ? $comments_karma[$image->id] : 0;
                break;

            case 'post':
                $karma = isset($posts_karma[$image->id]) ? $posts_karma[$image->id] : 0;
                break;

            default:
                $karma = 0;
                break;
        }
    }

    if ($show_all || ($karma > -10)) {
        $image->url = Upload::get_url($image->type, $image->id, $image->version, $image->date, $image->mime);
        $images[] = $image;
    }
}

if ($images) {
    Haanga::Load("backend/gallery.html", compact('images'));
}
