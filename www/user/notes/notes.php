<?php
defined('mnminclude') or die();

$query = '
    FROM posts
    WHERE post_user_id = "'.(int)$user->id.'"
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

if (empty($posts)) {
    return Haanga::Load('user/empty.html');
}

require __DIR__ . '/notes-common.php';
