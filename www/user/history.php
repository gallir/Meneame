<?php
defined('mnminclude') or die();

$rows = $db->get_var('
    SELECT COUNT(*)
    FROM links
    WHERE link_author = "'.$user->id.'";
');

$links = $db->get_col('
    SELECT link_id
    FROM links
    WHERE link_author = "'.$user->id.'"
    ORDER BY link_date DESC
    LIMIT '.$offset.', '.$page_size.';
');

if (empty($links)) {
    return;
}

Link::$original_status = true; // Show status in original sub

foreach ($links as $link_id) {
    $link = Link::from_db($link_id);

    if ($link && $link->votes > 0) {
        $link->print_summary('short');
    }
}
