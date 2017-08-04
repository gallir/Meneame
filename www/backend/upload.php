<?php

if (!defined('mnmpath')) {
    require_once __DIR__.'/../config.php';
    require_once mnminclude.'html1.php';
}

header('Content-Type: application/json; charset=utf-8');

$link = false;

if (!empty($_REQUEST['id']) && ($id = intval($_REQUEST['id'])) > 0) {
    $link = Link::from_db($id);
}

if (!$link || !$current_user->authenticated || ($current_user->user_id != $link->author)) {
    die;
}

$version = 1 + (int)$db->get_var('
    SELECT version
    FROM `media`
    WHERE (
        user = "'.$current_user->user_id.'"
        AND type = "link"
        AND id = "'.$link->id.'"
    )
    ORDER BY version DESC
    LIMIT 1;
');

$media = new Upload('link', $link->id, $version);

if (($result = $media->from_temporal($_FILES['image'])) === true) {
    $response =[
        'url' => Upload::get_url('link', $id, $version, 0, $media->mime),
        'error' => false,
    ];
} else {
    $response = [
        'url' => false,
        'error' => $result,
    ];
}

die(json_encode($response));
