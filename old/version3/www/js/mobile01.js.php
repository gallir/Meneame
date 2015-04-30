<?
include('../config.php');
header('Content-Type: application/x-javascript; charset=utf-8');
header('Cache-Control: max-age=864000');
header("Expires: " . gmdate("r", $globals['now'] + 864000));

echo 'var base_url="'.$globals['base_url'].'";'."\n";
echo 'var base_static="'.$globals['base_static'].'";'."\n";
echo 'var mobile_version = true;'."\n";

include('mobile.js');
?>
