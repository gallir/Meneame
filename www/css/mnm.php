<?php
include(__DIR__.'/../config.php');
header('Content-Type: text/css; charset=utf-8');
header('Cache-Control: public, max-age=864000');
header("Expires: " . gmdate("r", $globals['now'] + 864000));
header('Last-Modified: ' .  gmdate('D, d M Y H:i:s', max(filemtime('mnm.css'), filemtime('handheld.css')) ) . ' GMT');

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


