<?php

// autoloaded clasess
// Should be defined after mnminclude
// and before de database
function __autoload($class) {
	static $classfiles = array(
				'db' => 'mysqli.php',
				'RGDB' => 'rgdb.php',
				'Link' => 'link.php',
				'LinkMobile' => 'linkmobile.php',
				'Comment' => 'comment.php',
				'CommentMobile' => 'blog.php',
				'Vote' => 'votes.php',
				'Annotation' => 'annotation.php',
				'Blog' => 'blog.php',
				'Post' => 'post.php',
				'UserAuth' => 'login.php',
				'User' => 'user.php',
				'BasicThumb' => 'webimages.php',
				'WebThumb' => 'webimages.php',
				'HtmlImages' => 'webimages.php',
				'Trackback' => 'trackback.php',
				'Media' => 'media.php',
				'S3' => 'S3.php',
	);

	if (isset($classfiles[$class]) && file_exists(mnminclude.$classfiles[$class])) {
		require_once(mnminclude.$classfiles[$class]);
	} else {
		// Build the include for "standards" frameworks wich uses path1_path2_classnameclassName
		$filePath = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
		$includePaths = explode(PATH_SEPARATOR, get_include_path());
		foreach($includePaths as $includePath){
			if(file_exists($includePath . DIRECTORY_SEPARATOR . $filePath)){
				require_once($filePath);
				return;
			}
		}
		/* "try"  to include $class.php file if exists */
		@include_once($class.".php");
	}
}

function haanga_bootstrap()
{
	/* bootstrap function, load our custom tags/filter */
	require mnminclude.'haanga_mnm.php';
}

/* Load template engine here */
$config = array(
	'template_dir' => dirname(__FILE__).'/../templates/',
	'cache_dir'	=> mnmpath.'/'.$globals['cache_dir'].'/Haanga/'.$_SERVER['HTTP_HOST'],
	'autoload'	 => FALSE, /* Don't use Haanga's autoloader */
	'bootstrap'	=> 'haanga_bootstrap',
	'compiler' => array( /* opts for the tpl compiler */
		/* Avoid use if empty($var) */
		'if_empty' => FALSE,
		/* we're smart enought to know when escape :-) */
		'autoescape' => FALSE, 
		/* let's save bandwidth */
		'strip_whitespace' => TRUE, 
		/* call php functions from the template */
		'allow_exec'  => TRUE, 
		/* global $global, $current_user for all templates */
		'global' => array('globals', 'current_user'), 
	),
	'use_hash_filename' => FALSE, /* don't use hash filename for generated php */
);

if (is_callable('xcache_isset')) {
	/* don't check for changes in the template for the next 5 min */
	//$config['check_ttl'] = 300;
	//$config['check_get'] = 'xcache_get';
	//$config['check_set'] = 'xcache_set';
}

require mnminclude.'Haanga.php';

Haanga::configure($config);


global $globals;
$db = new RGDB($globals['db_user'], $globals['db_password'], $globals['db_name'], $globals['db_server']);
// we now do "lazy connection.
$db->persistent = $globals['mysql_persistent'];
//$db->master_persistent = $globals['mysql_master_persistent'];

?>
