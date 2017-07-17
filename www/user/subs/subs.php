<?php
defined('mnminclude') or die();

if ($current_user->admin && $user->id == $current_user->user_id) {
    $where = '(owner = "' . (int) $user->id . '" OR owner = 0)';
} else {
    $where = 'owner = "' . (int) $user->id . '"';
}

$subs = $db->get_results('
    SELECT SQL_CACHE *
    FROM subs
    WHERE (
        sub = 1
        AND ' . $where . '
    );
');

require __DIR__ . '/subs-common.php';
