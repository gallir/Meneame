<?

chdir(dirname(__FILE__));
$script = './'.$globals['path'][1];

if (! preg_match('/\.php$/', $script)) {
	$script .= '.php';
}

if ((include $script) === FALSE) {
	echo "failed $script\n";
    include_once 'config.php';
    not_found();
}



