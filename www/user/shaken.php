<?php
defined('mnminclude') or die();

if ($globals['bot']) {
    return Haanga::Load('user/empty.html');
}

$query = '
    FROM votes
    WHERE (
        vote_type = "links"
        AND vote_user_id = "'.(int)$user->id.'"
    )
';

$count = (int)$db->get_var('SELECT COUNT(*) '.$query.';');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$links = $db->get_results('
    SELECT vote_link_id AS id, vote_value
    '.$query.'
    ORDER BY vote_date DESC
    LIMIT '.(int)$offset.', '.(int)$limit.';
');

if (empty($links)) {
    return Haanga::Load('user/empty.html');
}

foreach ($links as $linkdb) {
    $link = Link::from_db($linkdb->id);

    if ($link->author == $user->id) {
        continue;
    }

    echo '<div style="max-width: 60em">';

    $link->print_summary('short', 0, false);

    if ($linkdb->vote_value < 0) {
        echo '<div class="box" style="z-index:1;margin:0 0 -5x 0;background:#FF3333;position:relative;top:-5px;left:85px;width:8em;padding: 1px 1px 1px 1px;border-color:#f00;opacity:0.9;text-align:center;font-size:0.9em;color:#fff;text-shadow: 0 1px 0 #000">';
        echo get_negative_vote($linkdb->vote_value);
        echo "</div>\n";
    }

    echo "</div>\n";
}

do_pages($count, $limit);

echo '<br/><span style="color: #FF6400;"><strong>' . _('Nota') . '</strong>: ' . _('sólo se visualizan los votos de los últimos meses') . '</span><br />';
