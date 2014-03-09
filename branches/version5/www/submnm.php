<?

#var_dump($globals['path']); die;
$path = $globals['path'];
$globals['submnm'] = $path[1];
include_once 'config.php';

$globals['site_shortname'] = $globals['submnm'];
if (empty($globals['submnm']) || ! SitesMgr::my_id()) {
	not_found();
}

$forbidden_routes = array('m', 'user', 'legal', 'notame', 'mobile', 'register', 'login', 'trends', 'backend');

if (! isset($routes[$path[2]]) || in_array($path[2], $forbidden_routes)) {
	syslog(LOG_INFO, "Forbidden in subs: ".$path[2]);
	// Redirect to the root
	$uri = preg_split('/\/+/', $_SERVER['REQUEST_URI'], 10, PREG_SPLIT_NO_EMPTY);
	$uri = array_slice($uri, 2);
	$uri = '/'.implode('/', $uri);
	header("Location: $uri");
	die;
}

$globals['path'] = array_slice($path, 2);
$globals['base_url'] .= $path[0] . '/' . $path[1] . '/';
$res = include './'.$routes[$path[2]];
if ($res === FALSE) {
	not_found($path[1]);
}

