<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

defined('mnminclude') or die();

// Store previous value for the log
$link_old = new stdClass;
$link_old->url = $link->url;
$link_old->title = $link->title;
$link_old->content = $link->content;
$link_old->tags = $link->tags;
$link_old->status = $link->status;
$link_old->sub_id = $link->sub_id;

if ($link->status === 'private') {
    $link->sub_id = 0;
} elseif ((int)$_POST['sub_id']) {
    $link->sub_id = (int)$_POST['sub_id'];
}

if ($link->sub_id != $link_old->sub_id) {
    $link->sub_changed = true; // To force to delete old statuses with another origin
}

$link->title = $_POST['title'];
$link->content = $_POST['bodytext'];

if ($error = $link->check_field_errors()) {
    return addFormError($error);
}

try {
    $validator->checkSiteSend();
} catch (Exception $e) {
    return;
}

// change the status
if (
    $_POST['status'] !== $link->status
    && (($_POST['status'] === 'autodiscard') || $current_user->admin || SitesMgr::is_owner())
    && preg_match('/^[a-z]{4,}$/', $_POST['status'])
    && (!$link->is_discarded() || $current_user->admin || SitesMgr::is_owner())
) {
    if (preg_match('/discard|abuse|duplicated|autodiscard/', $_POST['status'])) {
        // Insert a log entry if the link has been manually discarded
        $insert_discard_log = true;
    }

    $link->status = $_POST['status'];
}

$link->title = $link->get_title_fixed();
$link->content = $link->get_content_fixed();

$db->transaction();

if ($link->author == $current_user->user_id) {
    $link->store();
}

// Insert edit log/event if the link it's newer than 15 days
if ($globals['now'] - $link->date < 86400 * 15) {
    if ($insert_discard_log) {
        // Insert always a link and discard event if the status has been changed to discard
        Log::insert('link_discard', $link->id, $current_user->user_id);

        // Don't save edit log if it's discarded by an admin
        if ($link->author == $current_user->user_id) {
            $link->store();
            Log::insert('link_edit', $link->id, $current_user->user_id);
        }
    } elseif ($link->votes > 0) {
        Log::conditional_insert('link_edit', $link->id, $current_user->user_id, 60, serialize($link_old));
    }
}

$db->commit();

die(header('Location: '.$link->get_permalink()));
