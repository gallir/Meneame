<?php
defined('mnminclude') or die();

$subs = $db->get_results('
    SELECT subs.*
    FROM subs, prefs
    WHERE (
        pref_user_id = "' . (int) $user->id . '"
        AND pref_key = "sub_follow"
        AND subs.id = pref_value
    )
    ORDER BY name ASC;
');

require __DIR__.'/subs-common.php';
