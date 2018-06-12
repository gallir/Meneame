<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

defined('mnminclude') or die();

$link->poll = new Poll;

$link->poll->read('link_id', $link->id);
$link->poll->link_id = $link->id;

$db->transaction();

try {
    $link->poll->storeFromArray($_POST);
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}

$db->commit();
