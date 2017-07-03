<?php
defined('mnminclude') or die();

$nofollow = ($user->karma < 10) ? 'rel="nofollow"' : '';

if ($user->url) {
    $url = (strpos($user->url, 'http') === 0) ? $user->url : ('http://' . $user->url);
} else {
    $url = null;
}

if ($user->total_links > 1) {
    $entropy = intval(($user->blogs() - 1) / ($user->total_links - 1) * 100);
}

if ($user->total_links > 0 && $user->published_links > 0) {
    $percent = intval($user->published_links / $user->total_links * 100);
} else {
    $percent = 0;
}

$addresses = array();

if ($current_user->user_id == $user->id || ($current_user->user_level === 'god' && !$user->admin)) {
    // gods and admins know each other for sure, keep privacy
    $dbaddresses = $db->get_results("select distinct(vote_ip_int) as ip from votes where vote_type in ('links', 'comments', 'posts') and vote_user_id = $user->id order by vote_date desc limit 30");

    // Try with comments
    if (!$dbaddresses) {
        $dbaddresses = $db->get_results("select distinct(comment_ip_int) as ip from comments where comment_user_id = $user->id and comment_date > date_sub(now(), interval 30 day) order by comment_date desc limit 30");
    }

    if ($dbaddresses) {
        foreach ($dbaddresses as $dbaddress) {
            $ip_pattern = preg_replace('/[\.\:][0-9a-f]+$/i', '', inet_dtop($dbaddress->ip));

            if (!in_array($ip_pattern, $addresses)) {
                $addresses[] = $ip_pattern.'.XXX';
            }
        }
    }
}

if ($current_user->user_id == $user->id || $current_user->admin) {
    $strike = (new Strike($user))->getUserCurrentStrike();
} else {
    $strike = null;
}

$show_email = $current_user->user_id > 0 && !empty($user->public_info) && ($current_user->user_id == $user->id || $current_user->user_level === 'god');

$clones_from = "and clon_date > date_sub(now(), interval 30 day)";

if ($current_user->admin) {
    $nclones = $db->get_var("select count(distinct clon_to) from clones where clon_from = $user->id $clones_from");
}

if ($current_user->user_id > 0 && $current_user->user_id != $user->id) {
    $friend_icon = User::friend_teaser($current_user->user_id, $user->id);
}

return Haanga::Load('user/profile.html', compact(
    'user', 'url', 'nofollow', 'show_email', 'entropy', 'percent', 'nclones',
    'addresses', 'strike', 'friend_icon'
));
