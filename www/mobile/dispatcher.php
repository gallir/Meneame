<?php
$routes = array(
    ''          => 'index.php',
    'story'     => 'story.php',
    'queue'     => 'shakeit.php',
    'user'      => 'user.php',
    'search'    => 'search.php',
    'popular'   => 'topstories.php',
    'login'     => 'login.php',
);

chdir(__DIR__);

array_shift($globals['path']);

$script = $routes[$globals['path'][0]];

if ($script && is_file(__DIR__.'/'.$script)) {
    include($script);
}
