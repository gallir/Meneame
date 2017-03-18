<?php
defined('mnminclude') or die();

$rows = (int)$db->get_var('
    SELECT COUNT(*)
    FROM favorites
    WHERE (
        favorite_user_id = "'.(int)$user->id.'"
        AND favorite_type = "link"
    );
');

if ($rows === 0) {
    return Haanga::Load('user/empty.html');
}

$links = $db->get_col('
    SELECT link_id
    FROM links, favorites
    WHERE (
        favorite_user_id = "'.(int)$user->id.'"
        AND favorite_type = "link"
        AND favorite_link_id = link_id
    )
    ORDER BY favorite_link_readed ASC, link_date DESC
    LIMIT '.(int)$offset.', '.(int)$page_size.';
');

if (empty($links)) {
    return Haanga::Load('user/empty.html');
}

foreach ($links as $link_id) {
    $link = Link::from_db($link_id);
    $link->print_summary('short', 0, false, 'link_summary_favorites.html');
}
