<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once __DIR__ . '/../config.php';
require_once mnminclude . 'html1.php';

$globals['ads'] = false;

force_authentication();

if (!SitesMgr::can_send()) {
    die(header('Location: '.$globals['base_url']));
}

$site = SitesMgr::get_info();
$site_properties = SitesMgr::get_extended_properties();

function do_page_error($title, $info = '', $syslog = null)
{
    global $current_user;

    if ($syslog) {
        syslog(LOG_NOTICE, 'MENEAME ['.$current_user->user_login.'] '.$syslog);
    }

    Haanga::Load('story/submit/error.html', array(
        'title' => $title,
        'info' => $info
    ));

    do_footer();

    exit;
}

function report_duplicated($url)
{
    global $globals;

    if (!($found = Link::duplicates($url))) {
        return false;
    }

    $link = new Link;
    $link->id = $found;
    $link->read();

    Haanga::Load('link/duplicated.html', compact('globals', 'link'));

    do_footer();

    exit;
}
