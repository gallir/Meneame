<?php
defined('mnminclude') or die();

if ($globals['bot']) {
    return;
}

$friends = $db->get_col('
    SELECT friend_to
    FROM friends
    WHERE (
        friend_type = "manual"
        AND friend_from = "'.(int)$user->id.'"
        AND friend_value > 0
    );
');

if (empty($friends)) {
    return Haanga::Load('user/empty.html');
}

$links = $db->get_results('
    SELECT DISTINCT vote_link_id AS link_id
    FROM votes
    WHERE (
        vote_type = "links"
        AND vote_user_id IN ('.implode(',', array_map('intval', $friends)).')
        AND vote_value > 0
    )
    ORDER BY vote_link_id DESC
    LIMIT '.(int)$offset.', '.(int)$page_size.';
');

if (empty($links)) {
    return Haanga::Load('user/empty.html');
}

foreach ($links as $dblink) {
    $link = Link::from_db($dblink->link_id);
    $link->do_inline_friend_votes = true;
    $link->print_summary();
}
