#! /usr/bin/env php
<?php
include(__DIR__.'/../www/config.php');

echo "\n".date('Y-m-d H:i:s')."\n";

foreach (Strike::pastNotRestored() as $strike) {
    Strike::restoreStrike($strike->id);

    LogAdmin::insert('strike_restore', $strike->user_id, 0, $strike->karma_old, $strike->karma_restore);

    echo "\n".sprintf('Restored Strike %s to user %s (%s -> %s)', $strike->id, $strike->user_login, $strike->karma_old, $strike->karma_restore);
}

echo "\n";
