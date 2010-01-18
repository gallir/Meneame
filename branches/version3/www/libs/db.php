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
	);
	require_once(mnminclude.$classfiles[$class]);
}

global $globals;
$db = new RGDB($globals['db_user'], $globals['db_password'], $globals['db_name'], $globals['db_server']);
// we now do "lazy connection.
$db->persistent = $globals['mysql_persistent'];
//$db->master_persistent = $globals['mysql_master_persistent'];

?>
