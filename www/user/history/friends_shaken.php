<?php
defined('mnminclude') or die();

if ($globals['bot']) {
    return Haanga::Load('user/empty.html');
}

$friends = $db->get_col('
    SELECT SQL_CACHE friend_to
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
    FROM links, votes
    WHERE (
        vote_type = "links"
        AND vote_user_id IN ('.implode(',', array_map('intval', $friends)).')
        AND vote_value > 0
        AND link_content_type != "article"
        AND link_id = vote_link_id
    )
';

$count = (int)$db->get_var('SELECT SQL_CACHE COUNT(DISTINCT link_id) '.$query.';');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$links = $db->get_results('
    SELECT SQL_CACHE DISTINCT link_id
    '.$query.'
    ORDER BY link_id DESC
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
