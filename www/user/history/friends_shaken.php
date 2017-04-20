<?php
defined('mnminclude') or die();

if ($globals['bot']) {
    return Haanga::Load('user/empty.html');
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

$query = '
    FROM votes
    WHERE (
        vote_type = "links"
        AND vote_user_id IN ('.implode(',', array_map('intval', $friends)).')
        AND vote_value > 0
    )
';

$count = (int)$db->get_var('SELECT COUNT(*) '.$query.';');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$links = $db->get_results('
    SELECT DISTINCT vote_link_id AS link_id
    '.$query.'
    ORDER BY vote_link_id DESC
    LIMIT '.(int)$offset.', '.(int)$limit.';
');

if (empty($links)) {
    return Haanga::Load('user/empty.html');
}

foreach ($links as $dblink) {
    $link = Link::from_db($dblink->link_id);
    $link->do_inline_friend_votes = true;
    $link->print_summary();
}

do_pages($count, $limit);
