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

$selected_tab = 'admin_users';

adminAllowed($selected_tab);

switch ($_REQUEST['op'] ?: 'list') {
    case 'list':

        do_list($selected_tab);
        break;

    case 'new':
        do_new($selected_tab);
        break;
}

do_footer();

function do_list($selected_tab)
{
    do_header(_('Usuarios Administradores'));
    do_admin_tabs($selected_tab);

    Haanga::Load('admin/admin_users/list.html', [
        'selected_tab' => $selected_tab,
        'sections' => AdminUser::sections(),
        'list' => AdminUser::listing()
    ]);
}

function do_new($selected_tab)
{
    $row = null;

    if (empty($_GET['id']) || !($row = User::getById($_GET['id']))) {
        die(header('Location: '.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
    }

    $error = null;

    try {
        do_save($row);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    do_header(_('Usuarios Administradores'));
    do_admin_tabs($selected_tab);

    Haanga::Load('admin/admin_users/new.html', [
        'row' => $row,
        'error' => $error,
        'sections' => AdminUser::sectionsJoindedUserId($row->user_id)
    ]);
}

function do_save($row)
{
    if (empty($_POST['save']) || $_POST['save'] !== 'true') {
        return;
    }

    AdminUser::relateAdminWithSectionIds($row->user_id, (array)$_POST['section_ids']);

    die(header('Location: '.$_SERVER['REQUEST_URI']));
}
