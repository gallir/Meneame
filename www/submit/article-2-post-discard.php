<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

defined('mnminclude') or die();

$sub_id = (int)$_POST['sub_id'];

$link->sub_changed = $link->sub_id != $sub_id;
$link->sub_id = $sub_id;

if ($link->sub_id === -1) {
    $link->sub_id = 0;
}

$link->title = $_POST['title'];
$link->site_properties = $site_properties;
$link->content = $_POST['bodytext'];
$link->nsfw = !empty($_POST['nsfw']);
$link->tags = tags_normalize_string(_('Artículo'));

if ($error = $link->check_field_errors()) {
    if ($globals['is_ajax']) {
        responseJson($error, false);
    }

    return addFormError($error);
}

try {
    $validator->checkSiteSend();
} catch (Exception $e) {
    if ($globals['is_ajax']) {
        responseJson($e->getMessage(), false);
    }

    return;
}

$link->title = $link->get_title_fixed();
$link->content = $link->get_content_fixed();

$link->store();

if (empty($_POST['publish'])) {
    if ($globals['is_ajax']) {
        responseJson(_('Guardado!'));
    }

    die(header('Location: '.getenv('REQUEST_URI')));
}

if ($link->sub_id) {
    $link->enqueue();
} else {
    $link->enqueuePrivate();
}

$link->read();

die(header('Location: '. $link->get_permalink()));
