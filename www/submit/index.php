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

require __DIR__ . '/helpers.php';

$warning = $error = array();

$link = new Link;

$validator = new LinkValidator($link);

$validator->setErrorCallback('addFormError');
$validator->setWarningCallback('addFormWarning');

if (!empty($_REQUEST['id'])) {
    $link = getLinkByRequestId($link, $_REQUEST);
    $link->is_new = $link->status === 'discard';
}

$type = empty($_POST['type']) ? $link->content_type : $_POST['type'];

if ($type === 'article') {
    require __DIR__.'/article-'.getStep().'.php';
} else {
    require __DIR__.'/link-'.getStep().'.php';
}

do_footer();
