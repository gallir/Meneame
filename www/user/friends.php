<?php
defined('mnminclude') or die();

$prefered_id = $user->id;
$prefered_admin = $user->admin;

switch ($option) {
    case 3:
        $prefered_type = 'new';
        break;
    case 2:
        $prefered_type = 'ignored';
        break;
    case 1:
        $prefered_type = 'to';
        break;
    default:
        $prefered_type = 'from';
}

echo '<div style="padding: 5px 0px 10px 5px">';
echo '<div id="' . $prefered_type . '-container">' . "\n";

require __DIR__.'/../backend/get_friends_bars.php';

echo '</div>' . "\n";
echo '</div>' . "\n";

// Post processing
if (($option == 3) && ($user->id == $current_user->user_id)) {
    User::update_new_friends_date();
}
