<?php
defined('mnminclude') or die();

$query = '
    FROM posts, votes
    WHERE (
        vote_user_id = "'.(int)$user->id.'"
        AND vote_type = "posts"
        AND post_id = vote_link_id
        AND post_user_id != vote_user_id
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

require __DIR__ . '/notes-common.php';
