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

$media = new Upload('link', $link->id, 0);

if (($result = $media->from_temporal($_FILES['image'])) === true) {
    $response =[
        'url' => Upload::get_url('link', $id, 0, 0, $media->mime),
        'error' => false,
    ];
} else {
    $response = [
        'url' => false,
        'error' => $result,
    ];
}

die(json_encode($response));
