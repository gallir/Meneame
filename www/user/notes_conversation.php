<?php
defined('mnminclude') or die();

$query = '
    FROM posts, conversations
    WHERE (
        conversation_user_to = "'.(int)$user->id.'"
        AND conversation_type = "post"
        AND post_id = conversation_from
    )
';

$count = $db->get_var('SELECT COUNT(*) '.$query.';');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$posts = $db->get_results('
    SELECT '.Post::SQL.'
    INNER JOIN (
        SELECT post_id
        '.$query.'
        ORDER BY post_id DESC
        LIMIT '.$offset.', '.$limit.'
    ) AS `id`
    USING (post_id)
    ORDER BY post_id DESC;
', 'Post');

if ($time_read > 0 && $user->id == $current_user->user_id) {
    Post::update_read_conversation($time_read);
}

require __DIR__ . '/notes-common.php';
