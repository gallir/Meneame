<?php
defined('mnminclude') or die();

$query = '
    FROM links, favorites
    WHERE (
        favorite_user_id = "'.(int)$user->id.'"
        AND favorite_type = "link"
        AND link_content_type = "article"
        AND favorite_link_id = link_id
    )
';

$count = (int)$db->get_var('SELECT COUNT(*) '.$query.';');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$links = $db->get_col('
    SELECT link_id
    '.$query.'
    ORDER BY favorite_link_readed ASC, link_date DESC
    LIMIT '.(int)$offset.', '.(int)$limit.';
');

if (empty($links)) {
    return Haanga::Load('user/empty.html');
}

foreach ($links as $link_id) {
    Link::from_db($link_id)->print_summary('short', 0, false, 'link_summary_favorites.html');
}

do_pages($count, $limit);
