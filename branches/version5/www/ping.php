<?php
// Don't check the user is logged
$globals['no_auth'] = true;
$globals['no_lounge'] = true;
include('config.php');
$globals['force_ssl'] = false;

header("Content-Type: text/plain");

if (empty($globals['maintenance'])) {
	// Check cache dir
	if ( ! is_dir($globals['cache_dir'])) {
		ping_error('cache directory not available');
	}
	if ( ! is_writeable($globals['cache_dir'])) {
		ping_error('cache directory not writeble');
	}

	// Check access to DB
	$db->connect();
	if ( ! $db->connected ) { // Force DB access
		ping_error('DB not available');
	}

	// Check memcache
	if ( memcache_menabled() ) {
		$data = array(1, 2, 3);
		memcache_madd('ping', $data, 10);
		$result = memcache_mget('ping');
		if (! $result || $data != $result) {
			ping_error('memcache failed');
		}
	}
}

echo "pong\n";

function ping_error($log) {
	header('HTTP/1.1 500 Server error');
	if (! empty($log)) {
		echo("ERROR ping: $log");
		syslog(LOG_INFO, "ERROR ping: $log");
	}
	die;
}

