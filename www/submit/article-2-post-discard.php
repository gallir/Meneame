<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

defined('mnminclude') or die();

$link->sub_id = intval($_POST['sub_id'] ?: $site->id);
$link->title = $_POST['title'];
$link->site_properties = $site_properties;
$link->content = $_POST['bodytext'];
$link->tags = tags_normalize_string(_('Artículo'));

if ($error = $link->check_field_errors()) {
    return addFormError($error);
}

try {
    $validator->checkSiteSend();
} catch (Exception $e) {
    return;
}

$link->content = form_to_article($_POST['bodytext']);
$link->store();

die(header('Location: '.$globals['base_url'].'submit?step=3&id=' . $link->id));
