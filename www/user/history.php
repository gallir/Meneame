<?php
defined('mnminclude') or die();

$query = '
    FROM links
    WHERE link_author = "'.(int)$user->id.'"
';

$count = $db->get_var('SELECT COUNT(*) '.$query.';');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$links = $db->get_col('
    SELECT link_id
    '.$query.'
    ORDER BY link_date DESC
    LIMIT '.$offset.', '.$limit.';
');

if (empty($links)) {
    return Haanga::Load('user/empty.html');
}

Link::$original_status = true; // Show status in original sub

foreach ($links as $link_id) {
    $link = Link::from_db($link_id);

    if ($link && $link->votes > 0) {
        $link->print_summary('short');
    }
}

do_pages($count, $limit);
