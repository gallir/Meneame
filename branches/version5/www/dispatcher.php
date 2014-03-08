<?
#phpinfo();

$routes = array(
	''			=> 'index.php',
	'story'		=> 'story.php',
	'submit'	=> 'submit.php',
	'login'		=> 'login.php',
	'register'	=> 'register.php',
	'cloud'		=> 'cloud.php',
	'sites_cloud'	=> 'sitescloud.php',
	'rsss'		=> 'rsss.php',
	'promote'	=> 'promote.php',
	'values'	=> 'values.php',
	'queue'		=> 'shakeit.php',
	'legal'		=> 'legal.php',
	'b'			=> 'bar.php',
	'c'			=> 'comment.php',
	'user'		=> 'user.php',
	'search'	=> 'search.php',
	'sneak'		=> 'sneak.php',
	'popular'	=> 'topstories.php',
	'top_visited'	=> 'topclicked.php',
	'top_active'	=> 'topactive.php',
	'top_comments'	=> 'topcomments.php',
	'top_users'		=> 'topusers.php',
	'top_commented'	=> 'topcommented.php',
	'trends'	=> 'trends/index.php',
	'notame'	=> 'sneakme/dispatcher.php',
	'mobile'	=> 'mobile/dispatcher.php',
);

$globals['path'] = $path = preg_split('/\/+/', $_SERVER['PATH_INFO'], 4, PREG_SPLIT_NO_EMPTY);

//$path = explode('/', $_SERVER["PATH_INFO"]);
//var_dump($path); die;

@include($routes[$path[0]]);

/*
switch ($path[1]) {
	case '':
		@include("index.php");
		break;
	case 'story':
		@include("story.php");
		break;
	case 'queue':
		@include('shakeit.php');
		break;
	case 'b':
		@include('bar.php');
		break;
	case 'c':
		@include('comment.php');
		break;
	case 'user':
		@include('user.php');
		break;
	case 'search':
		@include('search.php');
		break;
}
*/
