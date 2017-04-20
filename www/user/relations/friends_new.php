<?php
defined('mnminclude') or die();

$prefered_id = (int)$user->id;
$prefered_type = 'new';

require __DIR__.'/friends-common.php';

if (($user->id == $current_user->user_id)) {
    User::update_new_friends_date();
}
