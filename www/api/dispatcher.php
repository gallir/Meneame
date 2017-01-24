<?php
chdir(__DIR__);

$script = './'.$globals['path'][1];

if (!preg_match('/\.php$/', $script)) {
	$script .= '.php';
}

if (!is_file($script)) {
    include_once '../config.php';
    do_error('script no found', 404);
}

$globals['script'] = '/api/'.$globals['path'][1];

if ((include $script) === FALSE) {
    include_once '../config.php';
    do_error("bad request", 400);
}
