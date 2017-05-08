<?php

require "../lib/Haanga.php";
$config = array(
    'cache_dir' => 'tmp/',
    'template_dir' => 'django-yui-layout-templates/',
);

if (is_callable('xcache_isset')) {
    /* don't check for changes in the template for the next 5 min */
    $config['check_ttl'] = 300;
    $config['check_get'] = 'xcache_get';
    $config['check_set'] = 'xcache_set';
}

Haanga::Configure($config);

$files = array();
foreach (glob("django-yui-layout-templates/*.html") as $html) {
    if (is_file($html)) {
        $files[basename($html)] = TRUE;
    }
}

if (!isset($_GET['layout']) || !isset($files[$_GET['layout']])) {
    $_GET['layout'] = key($files);
}

$blocks = array(
    '1' => 'Content on div 1',
    '2' => 'Content on div 2',
    '3' => 'Content on div 3',
    '4' => 'Content on div 4',
    'title' => $_GET['layout']." template",
);

$debug = TRUE;
$sql_queries = array(
    array('sql' => 'select * from foobar', 'time' => '1'),
    array('sql' => 'select * from php', 'time' => '1'),
);

$files = array_keys($files);
$time  = microtime(TRUE);
$mem   = memory_get_usage();

Haanga::Load($_GET['layout'], compact('debug', 'files', 'sql_queries'), FALSE, $blocks);
var_dump(array(
 'memory (mb)' => (memory_get_usage()-$mem)/(1024*1024), 
 'time' => microtime(TRUE)-$time
 ));
