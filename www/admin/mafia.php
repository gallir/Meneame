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

$selected_tab = 'mafia';

adminAllowed($selected_tab);

do_header(_('Mafia'));
do_admin_tabs($selected_tab);

$mafia = new Mafia($_REQUEST['uri'], $_REQUEST['published'], $_REQUEST['link_ids']);

Haanga::Load('admin/mafia/index.html', [
    'uri' => $_REQUEST['uri'],
    'published' => $_REQUEST['published'],
    'link_ids' => $_REQUEST['link_ids'],
    'mafia' => $mafia,
    'links' => $mafia->getLinks(),
    'users' => $mafia->getUsers(),
]);

do_footer();
