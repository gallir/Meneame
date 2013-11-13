<?
include('../config.php');
header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: public, max-age=864000');
header("Expires: " . gmdate("r", $globals['now'] + 864000));

Haanga::Load('css/colorbox.css');
Haanga::Load('css/mnm.css');

/* Include handheld classes for mobile/tablets */

if (! $globals['mobile']) { /* If not mobile, it's a @media rule */
	echo "@media (max-width: 800px) {";
}

Haanga::Load('css/handheld.css');

if (! $globals['mobile']) { /* Close @media bracket */
	echo "}";
}


