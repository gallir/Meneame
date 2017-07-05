<?php
defined('mnminclude') or die();

if ($current_user->user_id != $user->id) {
    die(header('Location: '.$current_user->get_uri('notes_privates'), 301));
}

$globals['extra_js'][] = 'autocomplete/jquery.autocomplete.min.js';
$globals['extra_js'][] = 'jquery.user_autocomplete.js';
$globals['extra_css'][] = 'jquery.autocomplete.css';

echo '<div class="clearfix mb-20">';
echo '<a href="javascript:priv_new(0);" class="btn btn-mnm btn-inverted pull-right">+ '._('Nuevo privado').'</a>';
echo '</div>';

$count = $db->get_var('
    SELECT COUNT(*)
    FROM privates
    WHERE "'.(int)$current_user->user_id.'" IN (privates.user, privates.to);
');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$privates = $db->get_results('
    SELECT '.PrivateMessage::SQL.'
    WHERE "'.(int)$current_user->user_id.'" IN (privates.user, privates.to)
    ORDER BY date DESC
    LIMIT '.$offset.', '.$limit.';
', 'PrivateMessage');

if (empty($privates)) {
    return Haanga::Load('user/empty.html');
}

User::reset_notification($current_user->user_id, 'private');

echo '<ol class="comments-list">';

foreach ($privates as $message) {
    echo '<li>';

    $message->print_summary();

    if (!$message->date_read && ($message->to == $current_user->user_id)) {
        $message->mark_read();
    }

    echo '</li>';
}

echo "</ol>\n";

do_pages($count, $limit);
