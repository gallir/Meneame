<?php
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once('./config.php');

function do_redirection($url, $code = 301)
{
    if (empty($_GET['quiet'])) {
        redirect($url, $code);
    }

    exit;
}

$id = intval($_GET['id']);

if (empty($id)) {
    require(mnminclude.$globals['html_main']);

    do_error(_('enlace inexistente'), 404);

    exit;
}

switch ($_GET['what']) {
    /* From notifier */
    case 'privates':
        do_redirection($current_user->get_uri('notes_privates'));

    case 'posts':
        do_redirection($current_user->get_uri('notes_conversation'));

    case 'comments':
        do_redirection($current_user->get_uri('conversation'));

    case 'friends':
        do_redirection($current_user->get_uri('friends_new'));

    case 'post':
        do_redirection($globals['scheme'].'//'.get_server_name().post_get_base_url($id));

    case 'comment':
        $c = new Comment();
        $c->id = $id;

        do_redirection($globals['scheme'].'//'.get_server_name().$c->get_relative_individual_permalink());

    case 'favorites':
        do_redirection($current_user->get_uri('conversation'));
}

$l = Link::from_db($id, null, false);

if (empty($l)) {
    exit;
}

$l->add_click();

if (
    !$globals['mobile']
    && !$globals['mobile_version']
    && !empty($l->url)
    && ($current_user->user_id > 0)
    && (empty($globals['https']) || preg_match('/^https:/', $l->url))
    && User::get_pref($current_user->user_id, 'use_bar')
    && $db->get_var("select blog_type from blogs where blog_id = $l->blog") !== 'noiframe'
) {
    // we use always http to load no https pages
    do_redirection($globals['scheme'].'//'.get_server_name().$globals['base_url'].'b/'.$id, 307);
}

do_redirection($l->url ?: $l->get_permalink());
