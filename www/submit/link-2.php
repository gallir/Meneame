<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called 'COPYING'.

defined('mnminclude') or die();

if (empty($link->id)) {
    returnToStep(1);
}

if ($_POST) {
    require __DIR__.'/link-2-post.php';
}

do_header(_('Enviar historia') . ' 2/3', _('Enviar historia'));

$link->status = $link->sub_status;
$link->discarded = $link->is_discarded();
$link->status_text = $link->get_status_text();
$link->is_sub_owner = SitesMgr::is_owner();

$link->chars_left = $site_properties['intro_max_len'] - mb_strlen(html_entity_decode($link->content, ENT_COMPAT, 'UTF-8'), 'UTF-8');

$link->change_url = !$link->is_new && $link->url && ($current_user->admin || $current_user->user_level === 'blogger');
$link->change_status = !$link->is_new
    && ($link->votes > 0 && ($link->status !== 'published' || $current_user->user_level === 'god' || $link->is_sub_owner)
    && ((!$link->discarded && $current_user->user_id == $link->author) || $current_user->admin || $link->is_sub_owner));

$link->url_title = mb_substr($link->url_title, 0, 200);

if (mb_strlen($link->url_description) > 40) {
    $link->content = $link->url_description;
}

$link->chars_left = $site_properties['intro_max_len'] - mb_strlen(html_entity_decode($link->content, ENT_COMPAT, 'UTF-8'), 'UTF-8');

$link->has_thumb();
$link->key = md5($link->randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name());
Haanga::Load('story/submit/link-2.html', array(
    'site_properties' => $site_properties,
    'link' => $link,
    'error' => $error,
    'warning' => $warning,
    'related' => $link->get_related(6)
));
