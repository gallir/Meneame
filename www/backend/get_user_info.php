<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>
if (!defined('mnmpath')) {
    include_once __DIR__ . '/../config.php';

    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: public, s-maxage=300');
}

if (empty($_GET['id'])) {
    die;
}

$id = (int)$_GET['id'];
$user = new User;

if ($id > 0) {
    $user->id = $id;
} else {
    $user->username = $_GET['id'];
}

if (!$user->read()) {
    die(_('Usuario inexistente'));
}

if ($user->avatar) {
    echo '<div style="float: left; margin-right: 10px;"><img class="avatar big" src="' . get_avatar_url($user->id, $user->avatar, 80) . '" width="80" height="80" alt="' . $user->username . '"/></div>';
}

echo '<strong>' . _('Usuario') . ':</strong>&nbsp;' . $user->username;

$user->print_medals();

if ($current_user->user_id > 0 && $current_user->user_id != $user->id) {
    echo '&nbsp;' . User::friend_teaser($current_user->user_id, $user->id);
}

if ($user->names) {
    echo '<br /><strong>' . _('Nombre') . ':</strong>&nbsp;' . $user->names;
}

if ($user->url) {
    echo '<br /><strong>' . _('Web') . ':</strong>&nbsp;' . $user->url;
}

echo '<br /><strong>' . _('Karma') . ':</strong>&nbsp;' . $user->karma;
echo '<br /><strong>' . _('Usuario desde') . ':</strong>&nbsp;' . get_date($user->date);

if ($user->bio) {
    echo '<br clear="left"><strong>' . _('Bio') . '</strong>: <br />'.text_to_html($user->bio);
}
