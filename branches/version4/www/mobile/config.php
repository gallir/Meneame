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

$globals['basic_config'] = true; include('../config.php');

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

$globals['mobile_version'] = true; // Mark we are using mobile version

$globals['js_main'] = 'mobile.js.php?2';
$globals['css_main'] = 'css/es/mnm-mobile.css?5';
$globals['css_color'] = '';
$globals['html_main'] = 'html1-mobile.php';
$globals['comments_page_size'] = 50;

@include('local.php');
@include($_SERVER['SERVER_NAME'].'-local.php');
@include($_SERVER['SERVER_ADDR'].'-local.php');


include mnminclude.'init.php';
include mnminclude.'login.php';

// For production servers
$db->hide_errors();

?>
