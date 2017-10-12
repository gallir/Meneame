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

do_header(_('Mafia'));
do_admin_tabs('mafia');

$mafia = new Mafia($_REQUEST['url']);

Haanga::Load('admin/mafia/index.html', [
    'url' => $_REQUEST['url'],
    'mafia' => $mafia,
    'current' => $mafia->getCurrent(),
    'previous' => $mafia->getPrevious(),
    'next' => $mafia->getNext(),
]);

do_footer();
