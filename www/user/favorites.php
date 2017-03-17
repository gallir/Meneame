<?php
defined('mnminclude') or die();

$rows = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_user_id=$user->id AND favorite_type='link'");
$links = $db->get_col("SELECT link_id FROM links, favorites WHERE favorite_user_id=$user->id AND favorite_type='link' AND favorite_link_id=link_id ORDER BY favorite_link_readed ASC, link_date DESC LIMIT $offset,$page_size");

if (empty($links)) {
    return;
}

foreach ($links as $link_id) {
    $link = Link::from_db($link_id);
    $link->print_summary('short', 0, false, 'link_summary_favorites.html');
}
