<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called 'COPYING'.

defined('mnminclude') or die();

try {
    $validator->checkKey();
} catch (Exception $e) {
    return;
}

if (!validateLinkUrl($link, $validator)) {
    return;
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
$link->author = $current_user->user_id;

if (!empty($site_properties['rules']) && $site_properties['no_link'] == 2) {
    $link->rules = LCPBase::html($site_properties['rules']);
}

// Now stores new draft
$link->sent_date = $link->date = time();
if (($link->author == $current_user->user_id && $link->votes == 0) || $current_user->admin) {

    if (empty($_POST['randkey']) && ! empty($site_properties['no_link'])) {
        $link->randkey = rand(10000, 10000000);
        $link->key = md5($link->randkey.$current_user->user_id.$current_user->user_email.$site_key.get_server_name());
    } else {
        $link->randkey = $_POST['randkey'];
        $link->key = $_POST['key'];
    }

    $link->store();
}

die(header('Location: '.$globals['base_url'].'submit?step=2&id=' . $link->id));
