<?php
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// David Martín :: Suki_ :: <david at sukiweb dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called 'COPYING'.

$globals['skip_check_ip_noaccess'] = true;

include('../config.php');
include(mnminclude.'html1.php');
include('libs/admin.php');

$page_size = 40;
$offset = (get_current_page() - 1) * $page_size;

$operation = $_REQUEST['op'] ?: 'list';
$search = $_REQUEST['s'];
$orderBy = $_REQUEST['order_by'];

if (!empty($_REQUEST['tab'])) {
    $selected_tab = clean_input_string($_REQUEST['tab']);
} else {
    $selected_tab = 'strikes';
}

if (!empty($_REQUEST['type'])) {
    $type = clean_input_string($_REQUEST['type']);
} else {
    $type = 'all';
}

if (!empty($_REQUEST['strike_date'])) {
    $strike_date = clean_input_string($_REQUEST['strike_date']);
} else {
    $strike_date = 'all';
}

if (!empty($_REQUEST['strike_user'])) {
    $user_login = clean_input_url($_REQUEST['strike_user']);
} else {
    $user_login = null;
}

switch ($operation) {
    case 'list':
        do_header(_('listado de strikes'));
        do_admin_tabs($selected_tab);
        do_strike_list($selected_tab, $search, $type, $strike_date, $orderBy);
        break;

    case 'new':
        do_header(_('nuevo strike'));
        do_admin_tabs($selected_tab);
        do_new_strike($selected_tab, $orderBy, $user_login);
        break;

    case 'save';
        do_save_strike();
        break;
}

do_footer();

function do_strike_list($selected_tab, $search, $type, $strike_date, $orderBy)
{
    global $db, $offset, $page_size, $globals;

    if (empty($orderBy)) {
        $orderBy = 'strike_date';
        $orderMode = 'DESC';
    } else {
        $orderBy = preg_replace('/[^a-z_]/i', '', $orderBy);
        $orderMode = ($orderBy === 'strike_date') ? 'DESC' : 'ASC';
    }

    $total = Strike::count();
    $strikes = Strike::list($orderBy, $orderMode, $offset, $page_size);

    Haanga::Load('admin/strikes/list.html', compact('selected_tab', 'strikes'));

    do_pages($rows, $page_size, false);
}

function do_new_strike($selected_tab, $orderBy, $user_login)
{
    global $db, $offset, $page_size, $globals;

    $strikes = $types = $reasons = $banned = array();

    $user = new User();

    if (($user->username = $user_login) && $user->read()) {
        $types = Strike::getUserValidTypes($user->id);
        $strikes = Strike::getUserStrikes($user->id);
        $reasons = Strike::$reasons;
        $banned = array_filter($strikes, function($value) {
            return ($value->type === 'ban');
        });
    }

    Haanga::Load('admin/strikes/new.html', compact(
        'selected_tab', 'user', 'strikes', 'types', 'reasons', 'banned'
    ));
}

function do_save_strike()
{
    global $db, $globals, $current_user;

    // Check user
    $user = new User();
    $user->id = intval($_POST['user_id']);

    if (!$user->read()) {
        die('Usuario inexistente');
    }

    // Check strike type
    $type = $_POST['type'];

    // Check strike reason
    if (!Strike::isValidTypeForUser($user->id, $type)) {
        die('Tipo de strike no válido o ya aplicado');
    }

    // Save strike
    $strike = new Strike($user, $type);
    $strike->admin_id = $current_user->user_id;
    $strike->comment = clean_text($_POST['comment']);
    $strike->reason = clean_input_string($_POST['reason']);

    $strike->store();

    // TODO: Do ban to user and save in admin_log with type strike.

    die(header('Location: '.$_SERVER['REQUEST_URI']));
}
