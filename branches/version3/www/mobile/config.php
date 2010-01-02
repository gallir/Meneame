<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

define("mnmpath", dirname(__FILE__));
define("mnminclude", dirname(__FILE__).'/libs/');
ini_set("include_path", '.:'.mnminclude.':'.mnmpath);

// IMPORTANTE: Do local modification in "hostname-local.php"
// and/or "local.php"
// They are automatically included
//$server_name	= $_SERVER['HTTP_HOST'];
$dblang			= 'es';
$page_size		= 15;
$anonnymous_vote = true;
$external_ads = true;

//Configuration values
//$globals['external_ads'] = false;
//$globals['external_user_ads'] = false;

$globals['db_server'] = 'localhost';
$globals['db_name'] = 'meneame';
$globals['db_user'] = 'meneame';
$globals['db_password'] = '';

// Specify you base url, "/" if is the root document
// $globals['base_dir'] = '/meneame/';
$globals['base_url'] = '/';

//Specify the static web server, wiith port included, use same document root as the main server (i.e. base_url is used
$globals['static_server'] = '';
//$globals['static_server'] = 'http://static.meneame.net';

//$globals['legal'] = globals['base_url'].'libs/ads/legal-meneame.php';
// leave empty if you don't have the rewrite rule in apache

//RewriteRule ^/story/(.+)$ /story.php/$1  [L,NS]
$globals['base_story_url'] = 'story/';

//RewriteRule ^/search(/.*)$ /search.php$1 [L,NS,NE,PT]
//$globals['base_search_url'] = 'search/';

//RewriteRule ^/user/(.+)$ /user.php/$1  [L,NS]
$globals['base_user_url'] = 'user/';

//RewriteRule ^/notame(/.*)$ /sneakme/index.php$1 [L,NS]
$globals['base_sneakme_url'] = 'notame/';

// Memcache, set hostname if enabled
//$globals['memcache_host'] = 'localhost'; 
$globals['memcache_port'] = 11211; // optional

// Comment pages
$globals['comments_page_size'] = 20;
$globals['comments_page_threshold'] = 1.10;
$globals['max_comments'] = 2718;


$globals['mysql_persistent'] = true;
$globals['mysql_master_persistent'] = false;
// Enable or disable the detecttion of real IP behind transparents proxies
$globals['check_behind_proxy'] = false;
//$globals['lounge'] = 'lounge.html';
//$globals['redirect_feedburner'] = false;

// If > 0 it shows negatives votes and date(vote) > $globals['show_negatives']
//$globals['show_negatives'] = 0;
$globals['min_karma_for_negatives'] = 5.5;
$globals['min_user_votes'] = 0;  // For new users and also enable check of sent versus voted

$globals['cache_dir'] = 'cache';

// Recaptcha settings
// Put your keys en to enable recaptcha
//$globals['recaptcha_public_key'] = '';
//$globals['recaptcha_private_key'] = '';

// CSS files (main, color, notame)
$globals['css_main'] = 'css/es/mnm-mobile02.css';
//$globals['css_color'] = 'css/es/mnmcol-mobile01.css';


// This is for SMS messages
// Put your authorised addresses
//$globals['allowed_gsm_clients'] = 'localhost 192.168.0.1';


// Send logs to "log_user", is windows compatible
openlog(false, LOG_ODELAY, LOG_USER);

// Set an utf-8 locale if there is no utf-8 defined
if (!preg_match('/utf-8/i', setlocale(LC_CTYPE, 0)))  {
	setlocale(LC_CTYPE, "en_US.UTF-8");
}

@include('local.php');
@include($_SERVER['SERVER_NAME'].'-local.php');
@include($_SERVER['SERVER_ADDR'].'-local.php');

//ob_start();
$globals['base_static'] = $globals['static_server'] . $globals['base_url'];

include mnminclude.'db.php';
include mnminclude.'utils.php';
include mnminclude.'login.php';

// For production servers
$db->hide_errors();

?>
