<?php
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// David Martín :: Suki_ :: <david at sukiweb dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called 'COPYING'.

$globals['skip_check_ip_noaccess'] = true;

require_once __DIR__.'/../config.php';
require_once mnminclude.'html1.php';
require_once __DIR__.'/libs/admin.php';

$selected_tab = 'strikes';

adminAllowed($selected_tab);

switch ($_REQUEST['op'] ?: 'list') {
    case 'list':
        do_header(_('listado de strikes'));
        do_admin_tabs($selected_tab);
        do_strike_list($selected_tab);
        break;

    case 'new':
        do_header(_('nuevo strike'));
        do_admin_tabs($selected_tab);
        do_strike_new($selected_tab);
        break;

    case 'save';
        do_strike_save();
        break;

    case 'cancel';
        do_strike_cancel();
        break;
}

do_footer();

function do_strike_list($selected_tab)
{
    global $db, $globals;

    $page_size = 40;
    $offset = (get_current_page() - 1) * $page_size;
    $search = $_REQUEST['s'];

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

    $rows = Strike::count($search);
    $strikes = Strike::listing($search, $_REQUEST['order_by'], $_REQUEST['order_mode'], $offset, $page_size);

    $order_mode = ($_REQUEST['order_mode'] === 'DESC') ? 'ASC' : 'DESC';

    Haanga::Load('admin/strikes/list.html', compact(
        'selected_tab', 'strikes', 'order_mode', 'search'
    ));

    do_pages($rows, $page_size, false);
}

function do_strike_new($selected_tab)
{
    global $db;

    if (!empty($_REQUEST['strike_user'])) {
        $user_login = clean_input_url($_REQUEST['strike_user']);
    } else {
        $user_login = null;
    }

    $user = new User();
    $user->username = $user_login;

    if (empty($user_login) || !$user->read()) {
        $strikes = $types = $reasons = array();
        $next = $error = null;

        return Haanga::Load('admin/strikes/new.html', compact(
            'selected_tab', 'user', 'strikes', 'types', 'next', 'reasons', 'error'
        ));
    }

    $strike = new Strike($user);

    if ($strike->getUserCurrentStrike()) {
        $error = 'Este usuario ya dispone de un strike aún no finalizado';
    } elseif ($user->level === 'disabled') {
        $error = 'Este usuario está actualmente baneado';
    } else {
        $error = null;
    }

    $now = date('Y-m-d H:i:s');

    $types = $strike->getUserTypes();
    $next = $strike->getNext();
    $reasons = Strike::$reasons;

    $strikes = array_map(function ($value) use ($now) {
        $value->cancel = ($value->expires_at > $now) && !$value->restored;

        return $value;
    }, $strike->getUserStrikes());

    Haanga::Load('admin/strikes/new.html', compact(
        'selected_tab', 'user', 'strikes', 'types', 'next', 'reasons', 'error'
    ));
}

function do_strike_save()
{
    global $db, $globals, $current_user;

    // Check user
    $user = new User();
    $user->id = intval($_POST['user_id']);

    if (!$user->read()) {
        die('Usuario inexistente');
    }

    $strike = new Strike($user, $_POST['type']);

    // Check strike reason
    if (!$strike->type) {
        die('Tipo de strike no válido');
    }

    // Save strike

    $strike->admin_id = $current_user->user_id;
    $strike->comment = clean_text($_POST['comment']);
    $strike->reason = clean_input_string($_POST['reason']);
    $strike->report_id = (int)$_POST['report_id'];

    $strike->store();

    // TODO: Do ban to user and save in admin_log with type strike.

    die(header('Location: '.$_SERVER['REQUEST_URI']));
}

function do_strike_cancel()
{
    $back = str_replace('op=cancel', 'op=new', $_SERVER['REQUEST_URI']);

    if ($strike = Strike::getById((int)$_REQUEST['id'])) {
        Strike::cancel($strike);
    }

    die(header('Location: '.$back));
}
