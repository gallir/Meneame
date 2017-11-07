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

$selected_tab = 'preguntame';

adminAllowed($selected_tab);

switch ($_REQUEST['op'] ?: 'list') {
    case 'list':
        do_header(_('Pregúntame'));
        do_admin_tabs($selected_tab);
        do_list($selected_tab);
        break;

    case 'new':
        do_new($selected_tab);
        break;
}

do_footer();

function do_list($selected_tab)
{
    global $db, $globals;

    $page_size = 40;
    $offset = (get_current_page() - 1) * $page_size;

    Haanga::Load('admin/preguntame/list.html', [
        'selected_tab' => $selected_tab,
        'list' => Preguntame::listing($offset, $page_size)
    ]);

    do_pages(Preguntame::count(), $page_size, false);
}

function do_new($selected_tab)
{
    $row = null;

    if ($id = (int)$_GET['id']) {
        $row = Preguntame::getById($id);
    }

    $row = $row ?: (new Preguntame);

    try {
        do_save($row);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    do_header(_('Pregúntame'));
    do_admin_tabs($selected_tab);

    Haanga::Load('admin/preguntame/new.html', compact(
        'selected_tab', 'row', 'error'
    ));
}

function do_save($row)
{
    if (empty($_POST['save']) || $_POST['save'] !== 'true') {
        return;
    }

    $row->title = $_POST['title'];
    $row->subtitle = $_POST['subtitle'];
    $row->link = $_POST['link'];
    $row->image = $_POST['image'];
    $row->start_at = $_POST['start_at'];
    $row->end_at = $_POST['end_at'];
    $row->sponsored = !empty($_POST['sponsored']);
    $row->enabled = !empty($_POST['enabled']);

    $row->store();

    die(header('Location: '.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
}
