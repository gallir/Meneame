<?php
/**
 *   Simple example rendering a user list
 *   ------------------------------------
 *   
 *   @credit - adapt from ptemplates sample
 */
require "../lib/Haanga.php";

Haanga::registerAutoload();
Haanga::setCacheDir('tmp/');
Haanga::setTEmplateDir('inheritance/');

$time_start = microtime(true);

Haanga::Load('page.html', array(
    'title' => microtime(TRUE),
    'users' => array(
        array(
            'username' =>           'peter',
            'tasks' => array('school', 'writing'),
            'user_id' =>            1,
        ),
        array(
            'username' =>           'anton',
            'tasks' => array('go shopping'),
            'user_id' =>            2,
        ),
        array(
            'username' =>           'john doe',
            'tasks' => array('write report', 'call tony', 'meeting with arron'),
            'user_id' =>            3
        ),
        array(
            'username' =>           'foobar',
            'tasks' => array(),
            'user_id' =>            4
        )
    )
));

echo "in ".(microtime(true) - $time_start)." seconds\n<br/>";
