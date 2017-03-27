<?php
chdir(__DIR__);

$script = './'.$globals['path'][1];

if (!preg_match('/\.php$/', $script)) {
    $script .= '.php';
}

if (!is_file($script)) {
    require_once __DIR__ . '/../config.php';
    do_error("script no found", 404);
}

$globals['script'] = '/backend/'.$globals['path'][1];

if ((include $script) === false) {
    require_once __DIR__ . '/../config.php';
    do_error("bad request", 400);
}
