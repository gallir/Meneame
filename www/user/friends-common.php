<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>

defined('mnminclude') or die();

require_once __DIR__ . '/../backend/pager.php';

if (empty($prefered_id) || !is_int($prefered_id)) {
    return Haanga::Load('user/empty.html');
}

$page = empty($_GET['page']) ? 1 : (int) $_GET['page'];

$dbusers = array();
$limit = 40;
$offset = ($page - 1) * $limit;

switch ($prefered_type) {
    case 'from':
        $query = '
            FROM friends, users
            WHERE (
                friend_type = "manual"
                AND friend_from = "'.$prefered_id.'"
                AND user_id = friend_to
                AND friend_value > 0
            )
        ';

        $count = (int) $db->get_var('SELECT COUNT(*) '.$query.';');

        $dbusers = $db->get_results('
            SELECT friend_to AS who, UNIX_TIMESTAMP(friend_date) AS `date`
            '.$query.'
            ORDER BY user_login ASC
            LIMIT '.$offset.', '.$limit.';
        ');

        break;

    case 'to':
        $query = '
            FROM friends, users
            WHERE (
                friend_type = "manual"
                AND friend_to = "'.$prefered_id.'"
                AND user_id = friend_from
                AND friend_value > 0
            )
        ';
        $count = (int) $db->get_var('SELECT COUNT(*) '.$query.';');

        $dbusers = $db->get_results('
            SELECT friend_from AS who, UNIX_TIMESTAMP(friend_date) AS `date`
            '.$query.'
            ORDER BY user_login ASC
            LIMIT '.$offset.', '.$limit.';
        ');

        break;

    case 'new':
        if ($prefered_id != $current_user->user_id) {
            return Haanga::Load('user/empty.html');
        }

        $new_friends = User::get_new_friends($prefered_id);
        $count = count($new_friends);

        if ($count) {
            $dbusers = $db->get_results('
                SELECT friend_from AS who, UNIX_TIMESTAMP(friend_date) AS `date`
                FROM friends, users
                WHERE (
                    friend_type = "manual"
                    AND friend_to = "'.$prefered_id.'"
                    AND friend_from in ('.implode(',', $new_friends).')
                    AND user_id = friend_from
                )
                ORDER BY friend_date DESC
                LIMIT '.$offset.', '.$limit.';
            ');
        }

        break;

    case 'ignored':
        if ($prefered_id != $current_user->user_id) {
            return Haanga::Load('user/empty.html');
        }

        $query = '
            FROM friends, users
            WHERE (
                friend_type = "manual"
                AND friend_from = "'.$prefered_id.'"
                AND user_id = friend_to
                AND friend_value < 0
            )
        ';

        $count = (int) $db->get_var('SELECT COUNT(*) '.$query.';');

        $dbusers = $db->get_results('
            SELECT friend_to AS who, UNIX_TIMESTAMP(friend_date) AS `date`
            '.$query.'
            ORDER BY user_login ASC
            LIMIT '.$offset.', '.$limit.';
        ');

        break;

    default:
        return Haanga::Load('user/empty.html');
}

if (empty($dbusers)) {
    return Haanga::Load('user/empty.html');
}

$friend = new User;

foreach ($dbusers as $dbuser) {
    $friend->id = $dbuser->who;
    $friend->read();

    $title = $friend->username;

    if ($dbuser->date > 0) {
        $title .= sprintf(' %s %s', _('desde'), get_date_time($dbuser->date));
    }

    echo '<div class="friends-item">';
    echo '<a href="' . get_user_uri($friend->username) . '" title="' . $title . '">';
    echo '<img class="avatar" src="' . get_avatar_url($friend->id, $friend->avatar, 20) . '" width="20" height="20" alt="' . $friend->username . '"/>';
    echo $friend->username;
    echo '</a>';

    if ($current_user->user_id > 0 && $current_user->user_id != $friend->id) {
        echo User::friend_teaser($current_user->user_id, $friend->id);
    }

    echo '</div>';
}

do_pages($count, $limit);
