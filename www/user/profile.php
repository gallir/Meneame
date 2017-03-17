<?php
defined('mnminclude') or die();

$post = new Post;

if ($user->ignored() || !$post->read_last($user->id)) {
    $post = null;
}

if (!empty($user->url)) {
    $nofollow = ($user->karma < 10) ? 'rel="nofollow"' : '';
    $url = preg_match('/^http/', $user->url) ? $user->url : ('http://' . $user->url);
}

if ($current_user->user_id > 0 && $current_user->user_id != $user->id) {
    $friend_icon = User::friend_teaser($current_user->user_id, $user->id);
}

$rss = 'rss?sent_by=' . $user->id;
$rss_title = _('envíos en rss2');
$show_email = $current_user->user_id > 0 && !empty($user->public_info) && ($current_user->user_id == $user->id || $current_user->user_level === 'god');

if ($current_user->admin) {
    $nclones = $db->get_var('
        SELECT COUNT(DISTINCT clon_to)
        FROM clones WHERE (
            clon_from = "'.$user->id.'"
            AND clon_date > DATE_SUB(NOW(), INTERVAL 30 DAY)
        );
    ');
} else {
    $nclones = 0;
}

$user->all_stats();

$user->bio = $user->bio ?: '';

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
                $addresses[] = $ip_pattern;
            }
        }
    }
}

$strike = null;

if ($current_user->user_id == $user->id || $current_user->admin) {
    $strike = (new Strike($user))->getUserCurrentStrike();
}

$prefs = $user->get_prefs();

Haanga::Load('user/header.html', compact(
    'post', 'selected', 'rss', 'rss_title', 'prefs', 'strike',
    'user', 'my_latlng', 'url', 'nofollow', 'nclones', 'show_email',
    'entropy', 'percent', 'geo_form', 'addresses', 'friend_icon'
));

$views = array(
    'subs', 'history', 'commented', 'shaken', 'friends_shaken',
    'friends', 'friend_of', 'friends_new', 'favorites',
    'favorite_comments', 'shaken_comments', 'conversation', 'profile'
);

if ($user->ignored()) {
    require __DIR__.'/ignored.php';
} elseif (in_array($view, $views, true)) {
    require __DIR__.'/'.$view.'.php';
} else {
    require __DIR__.'/error.php';
}

Haanga::Load('user/footer.html');
