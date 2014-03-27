<?
$routes = array(
	''			=> 'index.php',
	'story'		=> 'story.php',
	'submit'	=> 'submit.php',
	'subedit'	=> 'subedit.php',
	'subs'		=> 'subs.php',
	'editlink'	=> 'editlink.php',
	'comment_edit'	=> 'backend/comment_edit.php',
	'login'		=> 'login.php',
	'register'	=> 'register.php',
	'cloud'		=> 'cloud.php',
	'sites_cloud'	=> 'sitescloud.php',
	'rsss'		=> 'rsss.php',
	'promote'	=> 'promote.php',
	'values'	=> 'values.php',
	'queue'		=> 'shakeit.php',
	'legal'		=> 'legal.php',
	'go'		=> 'go.php',
	'b'			=> 'bar.php',
	'c'			=> 'comment.php',
	'm'			=> 'submnm.php',
	'user'		=> 'user.php',
	'search'	=> 'search.php',
	'rss'		=> 'rss2.php',
	'comments_rss'	=> 'comments_rss2.php',
	'sneakme_rss'	=> 'sneakme_rss2.php',
	'sneak'		=> 'sneak.php',
	'popular'	=> 'topstories.php',
	'top_visited'	=> 'topclicked.php',
	'top_active'	=> 'topactive.php',
	'top_comments'	=> 'topcomments.php',
	'top_users'		=> 'topusers.php',
	'top_commented'	=> 'topcommented.php',
	'trends'	=> 'trends.php',
	'notame'	=> 'sneakme/dispatcher.php',
	'mobile'	=> 'mobile/dispatcher.php',
);

$globals['path'] = $path = preg_split('/\/+/', $_SERVER['PATH_INFO'], 10, PREG_SPLIT_NO_EMPTY);

if (empty($routes[$path[0]]) || (include './'.$routes[$path[0]]) === FALSE) {
	include_once 'config.php';
	not_found();
}

