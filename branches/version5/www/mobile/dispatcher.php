<?php
$routes = array(
	''			=> 'index.php',
	'story'		=> 'story.php',
	'queue'		=> 'shakeit.php',
	'user'		=> 'user.php',
	'search'	=> 'search.php',
	'popular'	=> 'topstories.php',
	'login'		=> 'login.php',
);

chdir(dirname(__FILE__));
array_shift($globals['path']);

@include($routes[$globals['path'][0]]);


