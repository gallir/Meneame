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

return Haanga::Load('user/profile.html', compact(
    'user', 'url', 'nofollow', 'show_email', 'entropy', 'percent',
    'addresses', 'strike'
));
