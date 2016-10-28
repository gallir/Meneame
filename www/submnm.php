<?php

$path = $globals['path'];
$globals['submnm'] = preg_replace('/[^\p{L}\d_]/u', ':', $path[1]);
include_once __DIR__.'/config.php';

$forbidden_routes = array('m', 'user', 'legal', 'notame', 'mobile', 'register', 'login', 'trends');

if (in_array($path[2], $forbidden_routes)) {
	// syslog(LOG_INFO, "Forbidden in subs: ".$path[2]);
	// Redirect to the root
	$uri = preg_split('/\/+/', $_SERVER['REQUEST_URI'], 10, PREG_SPLIT_NO_EMPTY);
	$uri = array_slice($uri, 2);
	$uri = '/'.implode('/', $uri);
	header("Location: $uri");
	die;
}

$globals['site_shortname'] = $globals['submnm'];
if (empty($globals['submnm']) || ! ($info = SitesMgr::get_info())) {
	not_found();
}

$globals['path'] = array_slice($path, 2);
$globals['base_url'] .= $path[0] . '/' . $path[1] . '/';

if ( !empty($routes[$path[2]]) ) {
	$to_include = $routes[$path[2]];

	if (!is_file(include __DIR__.'/'.$to_include)) {
		not_found($path[1]);
	}

	include __DIR__.'/'.$to_include;
} else {
	// Try with story
	include __DIR__.'/story.php';
}
