<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called 'COPYING'.

defined('mnminclude') or die();

if (empty($_GET['write'])) {
    try {
        $validator->checkKey();
    } catch (Exception $e) {
        return;
    }
}

$anti_spam = empty($site_properties['no_anti_spam']);

try {
    $validator->checkDrafts();
    $validator->checkVotesMin();

    if ($anti_spam) {
        $validator->checkKarmaMin();
        $validator->checkBanUser();
        $validator->checkClones();
        $validator->checkUserNotPulished(24, $globals['limit_user_24_hours']);
        $validator->checkUserQueued(3);
        $validator->checkUserSame();
        $validator->checkUserIP();
        $validator->checkUserNegatives();
    }
} catch (Exception $e) {
    return;
}

$link->status = 'discard';
$link->content_type = 'article';
$link->author = $current_user->user_id;
$link->sent_date = $link->date = time();

if ($link->author == $current_user->user_id) {
    $link->store();
}

die(header('Location: '.$globals['base_url'].'submit?step=2&id='.$link->id));



