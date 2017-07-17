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

if ($link->check_field_errors()) {
    returnToStep(2, $link->id);
}

$link->key = md5($link->randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name());

if ($_POST) {
    require __DIR__.'/link-3-post.php';
}

do_header(_('Enviar historia') . ' 3/3', _('Enviar historia'));

Haanga::Load('story/submit/link-3.html', array(
    'site_properties' => $site_properties,
    'link' => $link,
    'error' => $error,
    'warning' => $warning
));
