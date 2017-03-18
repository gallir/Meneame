<?php
defined('mnminclude') or die();

$rows = -1;
$comments = $db->get_results('
    SELECT comment_id, link_id, comment_type
    FROM comments, links
    WHERE (
        comment_user_id = "'.(int)$user->id.'"
        AND link_id = comment_link_id
    )
    ORDER BY comment_date DESC
    LIMIT '.(int)$offset.', '.(int)$page_size.';
');

if (empty($comments)) {
    return Haanga::Load('user/empty.html');
}

require __DIR__.'/libs-comments.php';

print_comment_list($comments, $user);
