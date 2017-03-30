<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

defined('mnminclude') or die();

try {
    $validator->checkKey();
    $validator->checkSiteSend();
    $validator->checkDiscard();
    $validator->checkDuplicates();
} catch (Exception $e) {
    return addFormError($validator->error);
}

// Check this one was not already queued
if ($link->votes == 0 && ($link->status !== 'queued')) {
    $link->enqueue();
}

die(header('Location: '. $link->get_permalink()));