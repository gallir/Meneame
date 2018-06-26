<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
    require_once __DIR__ . '/../config.php';
    require_once mnminclude.'html1.php';
}

array_push($globals['cache-control'], 'no-cache');
http_cache();

header('Content-Type: application/json; charset=utf-8');

$link_id = $comment = $link = false;

if (isset($_REQUEST['reply_to']) && ($reply_to = intval($_REQUEST['reply_to'])) > 0) {
    $res = $db->get_row("select comment_link_id, comment_order from comments where comment_id=$reply_to");

    if (!$res) {
        die;
    }

    $link_id = $res->comment_link_id;
    $comment = new Comment();

    if ($res->comment_order) {
        $comment->content = '#'.$res->comment_order.' ';
    }
} elseif (!empty($_REQUEST['id']) && ($id = intval($_REQUEST['id'])) > 0) {
    $comment = Comment::from_db($id);

    if (!$comment) {
        die;
    }

    $link_id = $comment->link;
}

$link = Link::from_db($link_id);

if (!$link) {
    die;
}

if (
    !$current_user->authenticated
    || $current_user->user_karma < $globals['min_karma_for_comments']
    || (
        $current_user->user_date > $globals['now'] - $globals['min_time_for_comments']
        && $current_user->user_id != $link->author
    )
) {
    die;
}

if ($_POST['process'] === 'editcomment') {
    save_comment($comment, $link);
} else {
    print_edit_form($comment, $link);
}

function print_edit_form($comment, $link)
{
    global $current_user, $site_key, $globals, $reply_to;

    $data = array();

    $html = '';

    if ($comment->id == 0) {
        $comment->randkey = rand(1000000, 100000000);
    }

    $html .= '<div class="commentform">';
    $html .= '<form action="'.$globals['base_url']."comment_ajax?reply_to=$reply_to&amp;link=$link->id&amp;id=$comment->id&amp;user=$current_user->user_id".'" class="comment" method="post" enctype="multipart/form-data" id="c_edit_form">';
    $html .= '<input type="hidden" name="randkey" value="'.$comment->randkey.'" />';

    $html .= '<input type="hidden" name="process" value="editcomment" />';
    $html .= '<input type="hidden" name="key" value="'.md5($comment->randkey.$site_key).'" />';
    $html .= '<input type="hidden" name="id" value="'.$comment->id.'" />';

    $html .= Haanga::Load('comment_edit.html', compact('link', 'comment'), true);

    $html .= '</form></div>';

    $data['html'] = $html;
    $data['error'] = '';

    echo json_encode($data);
}

function save_comment($comment, $link)
{
    global $db, $current_user, $globals, $site_key;

    $data = array();

    if ($comment->id == 0) {
        $comment = Comment::save_from_post($link, false); // New comment
    } else {
        $comment = check_and_save($comment, $link); // Edit, others requirements
    }

    if (is_a($comment, 'Comment', false)) {
        $comment = Comment::from_db($comment->id);
        $comment->link_object = $link;

        $data['html'] = $comment->print_summary(null, true, true);
        $data['error'] = '';
    } else {
        $data['html'] = '';
        $data['error'] = $comment;
    }

    echo json_encode($data);
}

function check_and_save($comment, $link)
{
    global $db, $current_user, $globals, $site_key;

    // Warning, trillion of checkings :-(
    // TODO: unify with Comment::save_from_post(), careful with the differences
    // Check image limits

    if (!empty($_FILES['image']['tmp_name'])) {
        if ($limit_exceded = Upload::current_user_limit_exceded($_FILES['image']['size'])) {
            return $limit_exceded;
        }
    }

    $user_id = intval($_POST['user_id']);

    if (
        $current_user->authenticated
        && intval($_POST['id']) == $comment->id
        && $_POST['key'] === md5($comment->randkey.$site_key)
        && mb_strlen(trim($_POST['comment_content'])) > 2
        // Allow the author of the post
        && (
            (
                $user_id == $current_user->user_id
                && $current_user->user_id == $comment->author
                && time() - $comment->date < $globals['comment_edit_time'] * 1.5
            ) || (
                $current_user->user_level === 'god'
                && (
                    $comment->author != $current_user->user_id
                    || $comment->type === 'admin'
                )
            )
        )
    ) {
        $comment->content = clean_text_with_tags($_POST['comment_content'], 0, false, 10000);

        if ($current_user->user_level === 'god') {
            $comment->type = ($_POST['type'] === 'admin') ? 'admin' : 'normal';
        }

        if (!$current_user->admin) {
            $comment->get_links();
        }

        if ($current_user->user_id == $comment->author && $comment->banned && $current_user->Date() > $globals['now'] - 86400) {
            syslog(LOG_NOTICE, "Meneame: editcomment not stored, banned link ($current_user->user_login)");
            return _('comentario no insertado, enlace a sitio deshabilitado (y usuario reciente)');
        }

        $comment->content = User::removeReferencesToIgnores($comment->content);
        $comment->content = User::removeReferencesToLinkCommentsIgnores($link, $comment->content);

        if (mb_strlen($comment->content) > 0) {
            $comment->store();
        }

        // Check image upload or delete
        if ($_POST['image_delete']) {
            $comment->delete_image();
        } else {
            $comment->store_image_from_form('image');
        }

        return $comment;
    }

    return _('error actualizando, probablemente tiempo de edición excedido');
}
