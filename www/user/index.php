<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
require_once __DIR__ . '/../config.php';
require_once mnminclude . 'html1.php';
require_once mnminclude . 'geo.php';
require_once mnminclude . 'favorites.php';

$page_size = $globals['page_size'];

if ($globals['bot'] && get_current_page() > 2) {
    do_error('Pages exceeded', 404);
}

$offset = (get_current_page() - 1) * $page_size;

if (!empty($_SERVER['PATH_INFO'])) {
    $url_args = preg_split('/\/+/', $_SERVER['PATH_INFO'], 6, PREG_SPLIT_NO_EMPTY);

    array_shift($url_args);

    $_REQUEST['login'] = clean_input_string($url_args[0]);
    $_REQUEST['view'] = $url_args[1];
    $_REQUEST['uid'] = intval($url_args[2]);

    if (!$_REQUEST['uid'] && is_numeric($_REQUEST['view'])) {
        // This is a empty view but an user_id, change it
        $_REQUEST['uid'] = intval($_REQUEST['view']);
        $_REQUEST['view'] = '';
    }
} else {
    $_REQUEST['login'] = clean_input_string($_REQUEST['login']);
    $_REQUEST['uid'] = intval($_REQUEST['uid']);

    if (!empty($_REQUEST['login'])) {
        die(header('Location: ' . html_entity_decode(get_user_uri($_REQUEST['login'], clean_input_string($_REQUEST['view'])))));
    }
}

$login = clean_input_string($_REQUEST['login']);

if (empty($login)) {
    if ($current_user->user_id > 0) {
        die(header('Location: ' . html_entity_decode(get_user_uri($current_user->user_login))));
    } else {
        die(header('Location: ' . $globals['base_url']));
    }
}

$uid = $_REQUEST['uid']; // Should be clean before

$user = new User();

if ($current_user->admin) {
    // Check if it's used UID
    if (empty($uid)) {
        die(redirect(html_entity_decode(get_user_uri_by_uid($login, $_REQUEST['view']))));
    }

    $user->id = $uid;
} else {
    if ($uid > 0) {
        // Avoid anonymous and non admins users to use the id, it's a "duplicated" page
        die(redirect(html_entity_decode(get_user_uri($login, $_REQUEST['view']))));
    }

    $user->username = $login;
}

if (!$user->read()) {
    do_error(_('usuario inexistente'), 404);
}

$globals['search_options'] = array('u' => $user->username);

$view = clean_input_string($_REQUEST['view']) ?: 'history';

// The profile's use marked the current one as friend
if ($current_user->user_id) {
    $user->friendship_reverse = User::friend_exists($user->id, $current_user->user_id);
} else {
    $user->friendship_reverse = 0;
}

// For editing notes and sending privates
if ($current_user->user_id == $user->id || $current_user->admin || $user->friendship_reverse) {
    $globals['extra_js'][] = 'ajaxupload.min.js';
}

// Enable user AdSense
// do_user_ad: 0 = noad, > 0: probability n/100
// 100 if the user is the current one
if ($globals['external_user_ads'] && !empty($user->adcode)) {
    $globals['user_adcode'] = $user->adcode;
    $globals['user_adchannel'] = $user->adchannel;

    if ($current_user->user_id == $user->id || $current_user->admin) {
        $globals['do_user_ad'] = 100;
    } else {
        $globals['do_user_ad'] = $user->karma * 2;
    }
}

$globals['noindex'] = true;

// Check if it should be index AND if they are valids options, otherwise call do_error()
switch ($view) {
    case 'history':
    case 'shaken':
    case 'favorites':
    case 'friends_shaken':
        $menu = 'history';
        break;

    case 'friends':
    case 'friend_of':
    case 'friends_new':
    case 'ignored':
        $menu = 'relations';
        break;

    case 'commented':
    case 'conversation':
    case 'shaken_comments':
    case 'favorite_comments':
        $menu = 'comments';
        $globals['search_options']['w'] = 'comments';
        break;

    case 'subs':
    case 'subs_follow':
        $menu = 'subs';
        $globals['noindex'] = false;
        break;

    default:
        do_error(_('opción inexistente'), 404);
        break;
}

// Add canonical address
$globals['extra_head'] = '<link rel="canonical" href="//' . get_server_name() . get_user_uri($user->username) . '" />' . "\n";

$header_title = $user->username;

if (!empty($user->names)) {
    $header_title .= ' ('.$user->names.')';
}

// Used to show the user the number of unread answers to her comments
if ($current_user->user_id == $user->id) {
    $globals['extra_comment_conversation'] = ' [' . Comment::get_unread_conversations($user->id) . ']';
} else {
    $globals['extra_comment_conversation'] = '';
}

do_header($header_title);

$user->all_stats();
$user->bio = $user->bio ?: '';

if ($current_user->user_id == $user->id || $current_user->admin) {
    $strike = (new Strike($user))->getUserCurrentStrike();
} else {
    $strike = null;
}

Haanga::Load('user/header.html', compact('user', 'menu', 'strike'));
Haanga::Load('user/submenu.html', [
    'options' => ($options = Tabs::optionsFromProfile($view)),
    'cols' => (int)(12 / count($options)),
    'view' => $view
]);

if ($user->ignored()) {
    Haanga::Load('user/ignored.html');
} else {
    require __DIR__.'/'.$view.'.php';
}

Haanga::Load('user/footer.html');

do_footer();
