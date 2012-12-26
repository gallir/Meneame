<?
// It warms up the template systems and cache
include('config.php');
include(mnminclude.'html1.php');

// Chech for cache typical dirs
for ($i=0; $i<10; $i++) {
	if ( is_dir($globals['cache_dir'].'/0'.$i)) {
		echo "ping";
		die;
	}
}
header('HTTP/1.1 500 Server error');

?>
