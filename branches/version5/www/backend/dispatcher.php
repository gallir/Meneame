<?

chdir(dirname(__FILE__));
$script = './'.$globals['path'][1];

if (! preg_match('/\.php$/', $script)) {
	$script .= '.php';
}

if (!file_exists($script) || (include $script) === FALSE) {
    include_once '../config.php';
    not_found("script no found", 404);
}



