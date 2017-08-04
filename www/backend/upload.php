<?php

if (! defined('mnmpath')) {
    require_once __DIR__ . '/../config.php';
    require_once mnminclude.'html1.php';
}

header('Content-Type: application/json; charset=utf-8');

$link = false;

if (!empty($_REQUEST['id']) && ($id = intval($_REQUEST['id'])) > 0) {
    $link = Link::from_db($id);
}

if (!$link || !$current_user->authenticated || $current_user->user_id != $link->author) {
    die;
}

$sql = 'SELECT version 
        FROM `media` 
        WHERE user = ' . $current_user->user_id . '
        AND type="link"
        AND id=' . $link->id . '
        ORDER BY version DESC';

$version = intval($db->get_var($sql));
$version++;

$media = new Upload('link', $link->id, $version);
if (true === $result =  $media->from_temporal($_FILES['image'])) {
    echo json_encode([
        'url' => Upload::get_url('link', $id, $version, 0, $media->mime),
        'error' => false
    ]);
} else {
    echo json_encode([
        'url' => false,
        'error' => $result
    ]);
}