<?
// Don't check the user is logged
$globals['no_auth'] = true;

include('config.php');

header("Content-Type: text/plain");

// Chech for cache typical dirs
for ($i=0; $i<10; $i++) {
	if ( is_dir($globals['cache_dir'].'/0'.$i)) {
		echo "pong (" . SitesMgr::my_id() . ")\n"; // Force a DB access
		die;
	}
}
header('HTTP/1.1 500 Server error');

?>
