<?php
defined('mnminclude') or die();

if ($current_user->user_id != $user->id) {
    die(header('Location: '.$current_user->get_uri('notes_privates'), 301));
}

$globals['extra_js'][] = 'autocomplete/jquery.autocomplete.min.js';
$globals['extra_js'][] = 'jquery.user_autocomplete.js';

if (isset($_GET['q'])) {
    $q = trim(str_replace(['"', "'", '\\', '%'], '', strip_tags($_GET['q'])));
} else {
    $q = '';
}

Haanga::Load('user/notes_private_header.html', [
    'q' => $q
]);

$where = '"'.(int)$current_user->user_id.'" IN (`privates`.`user`, `privates`.`to`)';

if ($q) {
    $q_where = str_replace(' ', '%', $q);

    $where .= ' AND (
        `texts`.`content` LIKE "%'.$q_where.'%"
        OR `users`.`user_login` = "'.$q_where.'"
        OR `users_to`.`user_login` = "'.$q_where.'"
    )';
}

$count = $db->get_var('
    SELECT SQL_CACHE COUNT(*)
    FROM `privates`
    LEFT JOIN `users` on (
        `user_id` = `privates`.`user`
    )
    LEFT JOIN `users` as `users_to` on (
        `users_to`.`user_id` = `privates`.`to`
    )
    LEFT JOIN `texts` ON (
        `texts`.`key` = "privates"
        AND `texts`.`id` = `privates`.`id`
    )
    WHERE ('.$where.');
');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$privates = $db->get_results('
    SELECT '.PrivateMessage::SQL.'
    WHERE ('.$where.')
    ORDER BY `date` DESC
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
