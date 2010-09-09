<?
include('../config.php');
header('Content-Type: application/x-javascript; charset=utf-8');
header('Cache-Control: max-age=864000');
header("Expires: " . gmdate("r", $globals['now'] + 864000));

echo 'var base_url="'.$globals['base_url'].'";'."\n";
echo 'var base_static="'.$globals['base_static'].'";'."\n";

include('jquery.simplemodal-1.2.3.pack.js');
echo "\n\n";
include('general.js');
echo "\n\n";

include('users.js');
?>
