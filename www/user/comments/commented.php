<?php
defined('mnminclude') or die();

$query = '
    FROM comments, links
    WHERE (
        comment_user_id = "'.(int)$user->id.'"
        AND link_id = comment_link_id
    )
';

$comments = $db->get_results('
    SELECT SQL_CACHE comment_id, link_id, comment_type
    '.$query.'
    ORDER BY comment_date DESC
    LIMIT '.(int)$offset.', '.(int)$limit.';
');

if (empty($comments)) {
    return Haanga::Load('user/empty.html');
}

require __DIR__.'/libs-comments.php';

print_comment_list($comments, $user);

do_pages(-1, $limit);