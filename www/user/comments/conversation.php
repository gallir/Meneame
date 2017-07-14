<?php
defined('mnminclude') or die();

$count = (int)$db->get_var('
    SELECT SQL_CACHE COUNT(DISTINCT(conversation_from))
    FROM conversations
    WHERE (
        conversation_user_to = "'.(int)$user->id.'"
        AND conversation_type = "comment"
    );
');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$comments = $db->get_results('
    SELECT SQL_CACHE comment_id, link_id, comment_type
    FROM comments
    INNER JOIN links ON (link_id = comment_link_id)
    INNER JOIN (
        SELECT DISTINCT(conversation_from)
        FROM conversations
        WHERE (
            conversation_user_to = "'.(int)$user->id.'"
            AND conversation_type = "comment"
        )
        ORDER BY conversation_time DESC
        LIMIT '.(int)$offset.', '.(int)$limit.'
    ) AS convs ON convs.conversation_from = comments.comment_id;
');

if (empty($comments)) {
    return Haanga::Load('user/empty.html');
}

require __DIR__.'/libs-comments.php';

$last_read = print_comment_list($comments, $user);

do_pages($count, $limit);

if ($last_read > 0 && ($current_user->user_id == $user->id)) {
    Comment::update_read_conversation($timestamp_read);
}
