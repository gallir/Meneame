<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

defined('mnminclude') or die();

try {
    $validator->checkKey();
} catch (Exception $e) {
    return addFormError($validator->error);
}

if (!empty($_POST['url']) && ($_POST['url'] !== $link->url) && !validateLinkUrl($link, $validator)) {
    return;
}

$link->read_content_type_buttons($_POST['type']);

// Check if the title contains [IMG], [IMGs], (IMG)... and mark it as image
if (preg_match('/[\(\[](IMG|PICT*)s*[\)\]]/i', $_POST['title'])) {
    $_POST['title'] = preg_replace('/[\(\[](IMG|PICT*)s*[\)\]]/i', ' ', $_POST['title']);
    $link->content_type = 'image';
} elseif (preg_match('/[\(\[](VID|VIDEO|Vídeo*)s*[\)\]]/i', $_POST['title'])) {
    $_POST['title'] = preg_replace('/[\(\[](VID|VIDEO|Vídeo*)s*[\)\]]/i', ' ', $_POST['title']);
    $link->content_type = 'video';
}

require_once mnminclude . 'tags.php';

$link->sub_id = intval($_POST['sub_id']);
$link->title = $_POST['title'];  // It also deletes punctuaction signs at the end
$link->tags = tags_normalize_string($_POST['tags']);
$link->site_properties = $site_properties;
$link->content = $_POST['bodytext']; // Warn, has to call $link->check_field_errors later

try {
    $validator->checkSiteSend();
    $validator->checkDiscard();
} catch (Exception $e) {
    return addFormError($validator->error);
}

// TODO: simplify this, return just $errors as array()
// as in editlink
if ($error = $link->check_field_errors()) {
    return addFormError($error);
}

$link->store();

// Check image upload or delete
if ($_POST['image_delete']) {
    $link->delete_image();
} else {
    $link->store_image_from_form('image');
}

die(header('Location: '.$globals['base_url'].'submit?step=3&id=' . $link->id));
