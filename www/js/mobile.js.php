<?php
include('../config.php');
header('Content-Type: application/x-javascript; charset=utf-8');
header('Cache-Control: max-age=864000');
header("Expires: " . gmdate("r", $globals['now'] + 864000));

Haanga::Load('js/mobile.js');

?>
