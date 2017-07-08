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
    require __DIR__.'/article-2-post.php';
}

$globals['extra_js'][] = '//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.4/js/standalone/selectize.min.js';
$globals['extra_css'][] = '//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.4/css/selectize.bootstrap3.min.css';

$globals['extra_vendor_js'][] = 'quill/quill.min.js';
$globals['extra_vendor_css'][] = 'quill/quill.snow.css';

do_header(_('Enviar un artículo') . ' 2/3', _('Enviar un artículo'));

$link->discarded = $link->is_discarded();
$link->status_text = $link->get_status_text();
$link->is_sub_owner = SitesMgr::is_owner();

$link->change_status = !$link->is_new
    && ($link->votes > 0 && ($link->status !== 'published' || $current_user->user_level === 'god' || $link->is_sub_owner)
    && ((!$link->discarded && $current_user->user_id == $link->author) || $current_user->admin || $link->is_sub_owner));

if (mb_strlen($link->url_description) > 40) {
    $link->content = $link->url_description;
}

$link->chars_left = $site_properties['intro_max_len'] - mb_strlen(html_entity_decode($link->content, ENT_COMPAT, 'UTF-8'), 'UTF-8');

if ($link->is_new) {
    $subs_main = get_subs_main();
    $subs_subscriptions = get_subs_subscriptions($subs_main);

    $subs_main = array_filter($subs_main, function ($value) use ($site) {
        $properties = SitesMgr::get_extended_properties($value->id);

        return ($value->id != $site->id) && !empty($properties['no_link']);
    });

    $subs_subscriptions = array_filter($subs_subscriptions, function ($value) use ($site) {
        $properties = SitesMgr::get_extended_properties($value->id);

        return ($value->id != $site->id) && !empty($properties['no_link']);
    });
} else {
    $subs_main = $subs_subscriptions = array();
}
$link->key = md5($link->randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name());
Haanga::Load('story/submit/article-2.html', array(
    'subs_main' => $subs_main,
    'subs_subscriptions' => $subs_subscriptions,
    'site' => $site,
    'site_properties' => $site_properties,
    'link' => $link,
    'error' => $error,
    'warning' => $warning,
    'current_user' => $current_user
));
