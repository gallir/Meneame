<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005-2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include mnminclude.'utils.php';

global $globals;
$globals['start_time'] = microtime(true);
$globals['now'] = intval($globals['start_time']);

register_shutdown_function('shutdown');

if (isset($globals['max_load']) && $globals['max_load'] > 0) {
	check_load($globals['max_load']);
}

// Basic initialization
//mb_internal_encoding('UTF-8');
/*
 * Use insteadi in your php.ini: 

default_charset = "UTF-8"
[mbstring]
mbstring.internal_encoding = UTF-8
mbstring.http_input = UTF-8
mbstring.http_output = UTF-8

*/

// we don't force https if the server name is not the same as de requested host from the client
if (!empty($globals['force_ssl']) && $_SERVER["SERVER_NAME"] != $_SERVER["HTTP_HOST"]) {
	$globals['force_ssl'] = false;
}

if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || $_SERVER['SERVER_PORT'] == 443 || $_SERVER['HTTPS'] == 'on') {
	$globals['https'] = true;
	$globals['scheme'] = 'https:';
} else {
	$globals['https'] = false;
	if (!empty($globals['force_ssl'])) {
		$globals['scheme'] = 'https:';
	} else {
		$globals['scheme'] = 'http:';
	}
}


// Use proxy and load balancer detection
if ($globals['check_behind_proxy']) {
	$globals['proxy_ip'] = $_SERVER["REMOTE_ADDR"];
	$globals['user_ip'] = check_ip_behind_proxy();
} elseif ($globals['behind_load_balancer']) {
	$globals['proxy_ip'] = $_SERVER["REMOTE_ADDR"];
	$globals['user_ip'] = check_ip_behind_load_balancer();
} else {
	$globals['user_ip'] = $_SERVER["REMOTE_ADDR"];
	$globals['proxy_ip'] = false;
}


$globals['user_ip_int'] = inet_ptod($globals['user_ip']);

$globals['cache-control'] = Array();
$globals['uri'] = preg_replace('/[<>\r\n]/', '', urldecode($_SERVER['REQUEST_URI'])); // clean  it for future use
//echo "<!-- " . $globals['uri'] . "-->\n";


// For PHP < 5
if ( !function_exists('htmlspecialchars_decode') ) {
	function htmlspecialchars_decode($text) {
		return strtr($text, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
}

if($_SERVER['HTTP_HOST']) {
	// Check bots
	if (empty($_SERVER['HTTP_USER_AGENT'])
		|| preg_match('/(spider|httpclient|bot|slurp|wget|libwww|\Wphp|wordpress|joedog|facebookexternalhit|squider)[\W\s0-9]/i', $_SERVER['HTTP_USER_AGENT'])) {
		$globals['bot'] = true;
	} else {
		$globals['bot'] = false;
	}

	// Check mobile/TV versions
	if ( ! $globals['bot'] 
		&& (isset($_GET['mobile']) || preg_match('/SymbianOS|BlackBerry|iPhone|Nintendo|Mobile|Opera (Mini|Mobi)|\/MIDP|Portable|webOS|Kindle|Fennec/i', $_SERVER['HTTP_USER_AGENT']))
			&& ! preg_match('/ipad|tablet/i', $_SERVER['HTTP_USER_AGENT']) ) { // Don't treat iPad as mobiles
		$globals['mobile'] = 1;
		// Reduce page size for mobiles
		$globals['comments_page_size'] = intval($globals['comments_page_size']/2);
		$globals['page_size'] = intval($globals['page_size']/2);
	} else {
		$globals['mobile'] = 0;
	}

	// Fill server names
	// Alert, if does not work with port 443, in order to avoid standard HTTP connections to SSL port
	if(empty($globals['server_name'])) {
		if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
			$globals['server_name'] = strtolower($_SERVER['SERVER_NAME']) . ':' . $_SERVER['SERVER_PORT'];
		} else {
			$globals['server_name'] = strtolower($_SERVER['SERVER_NAME']);
		}
	}
} else {
	if (!$globals['server_name']) $globals['server_name'] = 'meneame.net'; // Warn: did you put the right server name?
}

$globals['base_url_general'] = $globals['base_url']; // Keep the original if it's modified in submnms

// Add always the scheme, it's necessary for headers and rss's
if (!empty($globals['static_server'])) {
	$globals['base_static_noversion'] = $globals['scheme'].'//'.$globals['static_server'].$globals['base_url'];
} else {
	$globals['base_static_noversion'] = $globals['scheme'].'//'.$globals['server_name'].$globals['base_url'];
}

$globals['base_static'] = $globals['base_static_noversion'].'v_'.$globals['v'].'/';

// Votes' tags
$globals['negative_votes_values'] = Array ( -1 => _('irrelevante'), -2 => _('antigua'), -3 => _('cansina'), -4 => _('sensacionalista'), -5 => _('spam'), -6 => _('duplicada'), -7 => _('microblogging'), -8 => _('errónea'),  -9 => _('copia/plagio'));


// autoloaded clasess
// Should be defined after mnminclude
// and before the database
function __autoload($class) {
	static $classfiles = array(
				'SitesMgr' => 'sites.php',
				'Annotation' => 'annotation.php',
				'Log' => 'log.php',
				'db' => 'mysqli.php',
				'RGDB' => 'rgdb.php',
				'LCPBase' => 'LCPBase.php',
				'Link' => 'link.php',
				'LinkMobile' => 'linkmobile.php',
				'Comment' => 'comment.php',
				'CommentMobile' => 'blog.php',
				'Vote' => 'votes.php',
				'Annotation' => 'annotation.php',
				'Blog' => 'blog.php',
				'Post' => 'post.php',
				'PrivateMessage' => 'private.php',
				'UserAuth' => 'login.php',
				'User' => 'user.php',
				'BasicThumb' => 'webimages.php',
				'WebThumb' => 'webimages.php',
				'HtmlImages' => 'webimages.php',
				'Trackback' => 'trackback.php',
				'Upload' => 'upload.php',
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

// Allows a script to define to use the alternate server
if (isset($globals['alternate_db_server']) && !empty($globals['alternate_db_servers'][$globals['alternate_db_server']])) {
	$db = new RGDB($globals['db_user'], $globals['db_password'], $globals['db_name'], $globals['alternate_db_servers'][$globals['alternate_db_server']], true);
} else {
	$db = new RGDB($globals['db_user'], $globals['db_password'], $globals['db_name'], $globals['db_server'], true);
	$db->persistent = $globals['mysql_persistent'];
}


function haanga_bootstrap()
{
	/* bootstrap function, load our custom tags/filter */
	require mnminclude.'haanga_mnm.php';
}

/* Load template engine here */
$config = array(
	'template_dir' => mnmpath.'/'.$globals['haanga_templates'],
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

// Allow full or relative pathname for the cache (i.e. /var/tmp or cache)
if ($globals['haanga_cache'][0] == '/') {
	$config['cache_dir'] =  $globals['haanga_cache'] .'/Haanga/'.$_SERVER['SERVER_NAME'];
} else {
	$config['cache_dir'] = mnmpath.'/'.$globals['haanga_cache'] .'/Haanga/'.$_SERVER['SERVER_NAME'];
}

require mnminclude.'Haanga.php';

Haanga::configure($config);

function __($text) {
    return htmlentities($text, ENT_QUOTES, 'UTF-8', false);
}

function _e($text) {
    echo htmlentities($text, ENT_QUOTES, 'UTF-8', false);
}

function shutdown() {
	global $globals, $current_user, $db;

	close_connection();

	if (is_object($db) && $db->connected) {
		Link::store_clicks(); // It will check cache and increment link clicks counter
		$db->close();
	}

	if ($globals['access_log'] && !empty($globals['user_ip'])) {
		if (! empty($globals['script'])) $script = $globals['script'];
		elseif (empty($_SERVER['SCRIPT_NAME'])) $script = 'null('.urlencode($_SERVER["DOCUMENT_URI"]).')';
		else $script = $_SERVER['SCRIPT_NAME'];

		if (!empty($globals['ip_blocked'])) $user = 'B'; // IP is banned
		elseif ($current_user->user_id > 0) $user = $current_user->user_login;
		else $user = '-';

		if ($globals['start_time'] > 0) {
			$time = sprintf("%5.3f", microtime(true) - $globals['start_time']);
		} else {
			$time = 0;
		}

		@syslog(LOG_DEBUG, $globals['user_ip'].' '.$user.' '.$time.' '.get_server_name().' '.$script);
		exit(0);
	}
}
?>
