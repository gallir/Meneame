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
	if ($classfiles[$class] && file_exists(mnminclude.$classfiles[$class])) {
		require_once(mnminclude.$classfiles[$class]);
	} else {
		// Build the include for "standards" frameworks wich uses path1_path2_classnameclassName
		$filePath = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
		$includePaths = explode(PATH_SEPARATOR, get_include_path());
		foreach($includePaths as $includePath){
			if(file_exists($includePath . DIRECTORY_SEPARATOR . $filePath)){
				require_once $filePath;
				return;
			}
		}
		@require_once($class.".php");
	}
}

global $globals;
$db = new RGDB($globals['db_user'], $globals['db_password'], $globals['db_name'], $globals['db_server']);
// we now do "lazy connection.
$db->persistent = $globals['mysql_persistent'];
//$db->master_persistent = $globals['mysql_master_persistent'];

?>
