<?php
defined('mnminclude') or die();

if ($globals['bot']) {
    return;
}

$friends = $db->get_col("select friend_to from friends where friend_type = 'manual' and friend_from = $user->id and friend_value > 0");
$links = array();

if ($friends) {
    $friends_list = implode(',', $friends);
    $sql = "select distinct vote_link_id as link_id from votes where vote_type = 'links' and vote_user_id in ($friends_list) and vote_value > 0 order by vote_link_id desc";
    $links = $db->get_results("$sql LIMIT $offset,$page_size");
}

if (empty($links)) {
    return;
}

foreach ($links as $dblink) {
    $link = Link::from_db($dblink->link_id);
    $link->do_inline_friend_votes = true;
    $link->print_summary();
}
