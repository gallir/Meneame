<?
// Don't check the user is logged
$globals['no_auth'] = true;
$globals['no_lounge'] = true;

include('config.php');

header("Content-Type: text/plain");

// Chech for cache typical dirs
for ($i=0; $i<10; $i++) {
	if ( is_dir($globals['cache_dir'].'/0'.$i)) {
		if (empty($globals['maintenance'])) {
			echo "pong (" . SitesMgr::my_id() . ")\n"; // Force a DB access
		} else {
			echo "pong\n"; 
		}
		die;
	}
}
header('HTTP/1.1 500 Server error');

?>
