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
    $link->is_new = !$link->votes && ($link->status === 'discard');
}

if (!empty($_POST['type'])) {
    $type = $_POST['type'];
} elseif (!empty($_GET['type'])) {
    $type = $_GET['type'];
} else {
    $type = $link->content_type;
}

// Avoid Chrome Error: ERR_BLOCKED_BY_XSS_AUDITOR
header('X-XSS-Protection: 0');

if ($type === 'article') {

    if (Link::getUserArticleDraftsCount() >= $globals['max_article_drafts'] and getStep() == 1) {
        require __DIR__.'/draft-limit.php';
    } else {
        require __DIR__.'/article-'.getStep().'.php';
    }

} else {
    require __DIR__.'/link-'.getStep().'.php';
}

do_footer();
