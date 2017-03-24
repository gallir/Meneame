<?php

require_once __DIR__ . '/../init.php';

OAuth2Server::getInstance()->checkAccess();

$user_id = OAuth2Server::getInstance()->getServer()->getAccessTokenData(\OAuth2\Request::createFromGlobals())['user_id'];
$user = new User();
$user->id = $user_id;
$user->read();

$userParsed = array(
    'id' => $user->id,
    'username' => $user->username,
    'date' => $user->date,
    'email' => $user->email,
    'avatar' => get_avatar_url($user->id, -1, 40),
    'karma' => $user->karma
);

echo json_encode($userParsed);