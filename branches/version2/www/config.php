<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

define("mnmpath", dirname(__FILE__));
define("mnminclude", dirname(__FILE__).'/libs/');

// IMPORTANTE: Do local modification in "hostname-local.php"
// and/or "local.php"
// They are automatically included
//$server_name	= $_SERVER['SERVER_NAME'];
$dblang			= 'es';
$page_size		= 30;
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
// leave empty if you don't have the rewrite rule in apache

//RewriteRule ^/story/(.+)$ /story.php/$1  [L,NS]
$globals['base_story_url'] = 'story/';

//RewriteRule ^/search(/.*)$ /index.php$1 [L,NS,NE,PT]
$globals['base_search_url'] = 'search/';

//RewriteRule ^/user/(.+)$ /user.php/$1  [L,NS]
$globals['base_user_url'] = 'user/';

//RewriteRule ^/notame(/.*)$ /sneakme/index.php$1 [L,NS]
$globals['base_sneakme_url'] = 'notame/';

// Memcache, set hostname if enabled
//$globals['memcache_host'] = 'localhost'; 
$globals['memcache_port'] = 11211; // optional

// Comment pages
$globals['comments_page_size'] = 100;
$globals['comments_page_threshold'] = 1.10;


// Give 4 minutes to edit a comment
$globals['comment_edit_time'] = 240;

//$globals['tags'] = 'tecnología, internet, cultura, software libre, linux, open source, bitácoras, blogs, ciencia';
$globals['time_enabled_comments'] = 604800; // 7 days
$globals['time_enabled_votes'] = 345600; // 4 days
$globals['mysql_persistent'] = true;
// Enable or disable the detecttion of real IP behind transparents proxies
$globals['check_behind_proxy'] = false;
//$globals['lounge'] = 'lounge.html';
//$globals['redirect_feedburner'] = false;

// If > 0 it shows negatives votes and date(vote) > $globals['show_negatives']
//$globals['show_negatives'] = 0;
$globals['min_karma_for_negatives'] = 5.5;
//$globals['min_karma_for_links'] = 4.9;
//$globals['min_karma_for_comments'] = 4.9;
//$globals['min_karma_for_posts'] = 6;
//$globals['min_karma_for_sneaker'] = 5.2;
$globals['min_karma_for_comment_votes'] = 5.5;
// Ensure you have a avar dir writeable by the web server
//$globals['avatars_dir'] = 'avatars-local';
// Changed to a global cache directory
$globals['cache_dir'] = 'cache';
$globals['avatars_max_size'] = 200000;
$globals['avatars_files_per_dir'] = 1000;
$globals['avatars_allowed_sizes'] = Array (80, 40, 25, 20);

// Recaptcha settings
// Put your keys en to enable recaptcha
//$globals['recaptcha_public_key'] = '';
//$globals['recaptcha_private_key'] = '';

// Twitter settings
//$globals['twitter_user'] = '';
//$globals['twitter_password'] = '';
// Jaiku settings
//$globals['jaiku_user'] = '';
//$globals['jaiku_key'] = '';


// Websnapr.com previews
$globals['do_websnapr'] = true;

// Forbidden email domains to avoid "clones" and  "too easy" impersoning
// http://en.wikipedia.org/wiki/Disposable_e-mail_address
// See http://c2.com/cgi/wiki?ThrowawayEmailAndRidYourselfOfSpam
// Comment it out if you don't care
$globals['forbidden_email_domains'] = 'foo.domain.foo another.foo.domain';

// Anti spams
// check http://meneame.net/story/aviso-spam-programado-contra-meneame
$globals['forbiden_domains'] = 'foo.domain.foo another.foo.domain';

// Put here the pathname of the file where you store your karma.log file
$globals['karma_log'] = mnmpath . '/../../karma.log';


// CSS files (main, color, notame)
$globals['css_main'] = 'css/es/mnm3.css';
$globals['css_color'] = 'css/es/mnmcol4.css';

// This is for SMS messages
// Put your authorised addresses
#$globals['allowed_gsm_clients'] = 'localhost 192.168.0.1';

// The maximun amount of annonymous votes vs user votes in 1/2 hour
// 3 means 3 times annonymous votes as user votes in that period
$anon_to_user_votes = 0.5;
$site_key = 12345679;
// Check this
$anon_karma	= 4;

// Don't touch behind this
@include('local.php');
@include($_SERVER['SERVER_NAME'].'-local.php');
@include($_SERVER['SERVER_ADDR'].'-local.php');

//ob_start();
include mnminclude.'db.php';
include mnminclude.'utils.php';
include mnminclude.'login.php';

// For production servers
$db->hide_errors();

?>
