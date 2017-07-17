<?php

require __DIR__ . '/../vendor/autoload.php';

$config = array(
    'cache_dir' => __DIR__ . '/tmp/',
    'autoload' => true,
    'template_dir' => __DIR__,
    'debug' => TRUE,
    'use_hash_filename' => FALSE,
    'compiler' => array(
        'allow_exec' => TRUE,
        'global' => array('test_global', 'global1'),
    )
);

Haanga::Configure($config);

date_default_timezone_set('UTC');

@mkdir(__DIR__ . "/tmp/");
foreach (glob(__DIR__ . "/tmp/*/*") as $file) {
    @unlink($file);
}
