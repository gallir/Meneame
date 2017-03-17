<?php
defined('mnminclude') or die();

if ($globals['bot']) {
    return;
}

$rows = -1; //$db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_user_id=$user->id");
$links = $db->get_results("SELECT vote_link_id as id, vote_value FROM votes WHERE vote_type='links' and vote_user_id=$user->id ORDER BY vote_date DESC LIMIT $offset,$page_size");

if (empty($links)) {
    return;
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

echo '<br/><span style="color: #FF6400;"><strong>' . _('Nota') . '</strong>: ' . _('sólo se visualizan los votos de los últimos meses') . '</span><br />';
