<?php
// The Meneame source code is Free Software, Copyright (C) 2005-2009 by
// Ricardo Galli <gallir at gmail dot com> and Menéame Comunicacions S.L.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.

// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// IMPORTANT: Do local modification in "hostname-local.php"
// and/or "local.php"
// They are automatically included
//

if (defined("config_done")) return TRUE; // If not "included_once"
define('config_done', 1);

// WARN WARNING ALERT: we use $_SERVER['SERVER_NAME'] which is the first
// server_name in NGInx and other servers
// $globals['server_name']	= $_SERVER['SERVER_NAME'];

// In case you have different domains and want to avoid Google penalization for duplicated content
// $globals['canonical_server_name'] = 'www.canonical.com';

// If you want to use the same cookie for subdomains, set de .domain.com
// $globals['cookies_domain'] = '.domain.com';

// If you want for force all html connection to pass throu ssl
// $globals['force_ssl'] = True;

// Specify the name of the ssl server, ensure you have also setup "cookies_domain
$globals['ssl_server'] = False; 

$globals['site_name'] = 'Menéame';
$globals['site_shortname'] = 'mnm'; //Used to differentiate in keys

// If you user version, be careful to rewrite the directory for img, css and js
// Example for nginx:
// rewrite /v_\d+/(.+)$ /$1 last;
$globals['v'] = 28; // internal version, to for reloads
$globals['lang'] = $dblang	= 'es';

$globals['help_url'] = 'http://meneame.wikispaces.com/Comenzando';

// Show only these meta categories, false for all
$globals['allowed_metas'] = false;
// Must be an array, example
// $globals['allowed_metas'] = array(100, 101, 102, 103);


$globals['page_size'] = 20;
$globals['anonnymous_vote'] = $anonnymous_vote = true;
$globals['ads'] = true;
$globals['external_ads'] = $external_ads = true;
$globals['behind_load_balancer'] = False; // LB as those in Amazon EC2 don't send the real remote address
//$globals['email_domain'] = 'my_email_domain.com'; // Used for sending emails bots and scrips, if not defined it uses server_name
//$globals['notify_email'] = 'my_email_domain.com'; // used for sending notifications, now only used for Amazon SNS/SES notifications

//Configuration values
//$globals['external_ads'] = false;
//$globals['external_user_ads'] = false;

$globals['db_server'] = 'localhost';
$globals['db_name'] = 'meneame';
$globals['db_user'] = 'meneame';
$globals['db_password'] = '';
$globals['db_use_transactions'] = true; // Disable it if you use MyISAM and have high loads

// Administrator email
//$globals['adm_email'] = 'admin@administrador'

//Specify the static web server, wiith port included, use same document root as the main server (i.e. base_url is used
// Don't forget to add a redirect to ooops.php in case of 404 error, for example in NGINX:
/*
    error_page  404 = /ooops.php;
    location = /ooops.php {
        include php_fastcgi;
    }
*/
$globals['static_server'] = '';
//$globals['static_server'] = 'http://static.meneame.net';

// Enables the click counter, it call to /go.php
// Make sure you have defined the table:
// CREATE TABLE  `meneame`.`link_clicks` ( `id` INT UNSIGNED NOT NULL , `counter` INT UNSIGNED NOT NULL DEFAULT  '0', PRIMARY KEY (  `id` )) ENGINE = INNODB;
$globals['click_counter'] = 1; // Put a value since which id should show in "link_summary", 0 to disable it

//URL shortener (used in menea.me)
//$globals['url_shortener'] = 'menea.me';


// Specify you base url, "/" if is the root document
// $globals['base_dir'] = '/meneame/';
$globals['base_url'] = '/';
$globals['top_logo'] = 'img/mnm/eli.png';
$globals['thumbnail_logo'] = 'img/mnm/eli_thumbnail.png';
$globals['legal'] = '/legal';

// Calculate affinity to the sender of the link
$globals['karma_user_affinity'] = false;
// Coefficient for links karma calculation
$globals['min_decay'] = 0.25;
$globals['karma_start_decay'] = 12; // In hours, when a link start to decrease its karma
$globals['karma_decay'] = 54; // In hours, when it reach its minimum
//$globals['news_meta'] = 102; // The code of the "last news" meta category, other has a longer no decreasing period
// Similar values for "news_meta", used if the previous is defined
$globals['karma_news_start_decay'] =  7;
$globals['karma_news_decay'] = 42;
$globals['karma_clicks_bonus'] = 0.05; // Bonus to give due to clicks, it reaches this value when clicks/votes = 10;


// Memcache, set hostname if enabled
//$globals['memcache_host'] = 'localhost';
$globals['memcache_port'] = 11211; // optional


// Enable it if you to log every access to the scripts (progname will be "meneame_accesslog")
//$globals['access_log'] = false;

// Uncomment if you don't want to control the banned IPs
//$globals['check_ip_noaccess'] = true;
// Uncomment and asssign the seconds you want to keep noaccess cache
//$globals['check_ip_noaccess_cache'] = 3;

$globals['fancybox_enabled'] = true;

// Comment pages
$globals['comments_page_size'] = 100;
$globals['comments_page_threshold'] = 1.10;

// Min karma to highlights comments
// The negative is used to hide comments
$globals['comment_highlight_karma'] = 100;
$globals['comment_hidden_karma'] = -100;


// Upload images and files
$globals['media_public'] = true;  // If true, anyone can read
$globals['media_max_size'] = 1024*1024; // 1 MB;
$globals['media_min_karma'] = 6;
$globals['media_max_bytes_per_day'] = 2 * 1024 * 1024; // 2 MB/day
$globals['media_max_upload_per_day'] = 10;
$globals['media_thumb_size'] = 80;
$globals['media_sublogo_height'] = 30;
$globals['media_sublogo_height_mobile'] = 20;



// Give 4 minutes to edit a comment
$globals['comment_edit_time'] = 240;

// How many *global* links for last 3 minutes
// If user->karma > 10 then limit = limit*1.5
$globals['limit_3_minutes'] = 10;
$globals['limit_3_minutes_karma'] = 10;

$globals['limit_user_24_hours'] = 12;
$globals['limit_same_site_24_hours'] = 4;

$globals['karma_propaganda'] = 12; // min user karma to avoid extra spam/propaganda checks in the submits


$globals['karma_base']=6;
$globals['karma_base_max']=9; // If not penalised, older users can get up to this value as base for the calculus
$globals['min_karma']=1; //min user karma
$globals['max_karma']=20; //max user karma
$globals['special_karma_gain']=17; //karma to gain 'special' status (max * 0.85)
$globals['special_karma_loss']=12; //karma to loss 'special' status (max * 0.6)

$globals['comment_votes_multiplier'] = 5; //'importance' in karma calculations of comment votes
$globals['post_votes_multiplier'] = 1; //'importance' in karma calculations of post votes

$globals['karma_points_by_votes'] = 5; // Max karma given to votes to other's links
$globals['instant_karma_per_published'] = 1; //karma added when published
$globals['instant_karma_per_depublished'] = 1; //karma substracted when depublished
$globals['instant_karma_per_discard'] = 0.2; //karma substracted when discarded
$globals['karma_points_per_published'] = 2; //karma added by each published when recalculating new karma (max 4)
$globals['karma_points_per_published_max'] = 4;
$globals['depublish_karma_divisor'] = 20; //karma is divided by this when depublish


$globals['depublish_negative_karma'] = 6.0; //minimun karma of the vote to get it counted by discard.php
$globals['depublish_positive_karma'] = 7.4; //minimun karma of the vote to get it counted by discard.php




//$globals['tags'] = 'tecnología, internet, cultura, software libre, linux, open source, bitácoras, blogs, ciencia';
$globals['max_sneakers'] = 250;
$globals['max_comments'] = 2718;
$globals['time_enabled_comments'] = 604800; // 7 days
$globals['time_enabled_comments_status']['queued'] = 259200; // 3 days
$globals['time_enabled_comments_status']['discard'] = 86400; // 1 day
$globals['time_enabled_comments_status']['autodiscard'] = 86400; // 1 day
$globals['time_enabled_comments_status']['abuse'] = 43200; // 1/2 day
$globals['time_enabled_votes'] = 345600; // 4 days
$globals['time_enabled_negative_votes'] = 3600; // 1 hour
$globals['mysql_persistent'] = true;
$globals['mysql_master_persistent'] = false;
// Enable or disable the detecttion of real IP behind transparents proxies
$globals['check_behind_proxy'] = false;
//$globals['lounge'] = 'lounge.html';

//$globals['rss_redirect_user_agent'] = 'feedburner'; // Identify the user agent of the agency
//$globals['rss_redirect_published'] = false; // http://url.of.the.new.rss
//$globals['rss_redirect_queued'] = false; // also valid for other links' status


// If > 0 it shows negatives votes and date(vote) > $globals['show_negatives']
//$globals['show_negatives'] = 0;
$globals['min_karma_for_negatives'] = 5.5;
$globals['min_user_votes'] = 0;  // For new users and also enable check of sent versus voted
$globals['new_user_time'] = 259200; // 3 days. Time from registry date the user is considered "new user"
$globals['new_user_karma'] = 6.1; // min karma to check new users
//$globals['min_karma_for_links'] = 4.9;
//$globals['min_karma_for_comments'] = 4.9;
$globals['min_time_for_comments'] = 3600; // Time to wait until first comment (from user_validated_date)
//$globals['min_karma_for_posts'] = 6;
//$globals['min_karma_for_sneaker'] = 5.2;
$globals['min_karma_for_comment_votes'] = 5.5;



$globals['new_user_links_limit'] = 1; //links allowed to submit in interval for new users
$globals['new_user_links_interval'] = 3600;
$globals['user_links_limit'] = 5;
$globals['user_links_interval'] = 7200;
$globals['user_links_clon_interval'] = 12; // hours forbidden to send with a clone, 0 to allow it

$globals['user_comments_clon_interval'] = 0; // hours forbidden to comment with a clone


//sneakme
$globals['posts_len'] = 500;
$globals['posts_period'] = 60;
$globals['posts_edit_time'] = 3600;
$globals['posts_edit_time_admin'] = 864000;
$globals['post_highlight_karma'] = 100;
$globals['post_hide_karma'] = -50;

$globals['draft_time'] = 1200; // Time unsent drafts will be kept (20 minutes)
$globals['draft_limit'] = 5; // Max unset drafts at the same time


// Don't forget to add a redirect to ooops.php in case of 404 error,
// if you want thumbnails to be automatically created by ooops.php
// for example in NGINX:
/*
    error_page  404 = /ooops.php;
    location = /ooops.php {
        include php_fastcgi;
    }
*/
// If you did it, change this to true
$globals['cache_redirector'] = false;

// Check it's writeable by the web server
$globals['cache_dir'] = 'cache';


// Haanga templates configuration
$globals['haanga_cache'] = '/var/tmp';
$globals['haanga_templates'] = 'templates';

//$globals['lucene_dir'] = mnmpath.'/'.$globals['cache_dir'].'/lucene_links';
$globals['sphinx_server'] = 'localhost';
$globals['sphinx_port'] = 9312;

$globals['avatars_check_always'] = true;
$globals['avatars_max_size'] = 1024*1024;
$globals['avatars_allowed_sizes'] = Array (80, 40, 25, 20);

// If you use nginx define X-Accel-Redirect, or X-Sendfile for Apache module
// See: https://tn123.org/mod_xsendfile/ http://wiki.nginx.org/XSendfile

//$globals['xsendfile'] = 'X-Accel-Redirect';
//$globals['xsendfile'] = 'X-Sendfile';


$globals['show_popular_queued'] = true;
$globals['show_popular_published'] = true;

// Recaptcha settings
$globals['captcha_first_login'] = false;
// Put your keys en to enable recaptcha
//$globals['recaptcha_public_key'] = '';
//$globals['recaptcha_private_key'] = '';

// Twitter settings, with oauth
// Ensure you have pecl/oauth installed
// For authentication
// $globals['oauth']['twitter']['consumer_key'] = 'xxxxxxxxxx';
// $globals['oauth']['twitter']['consumer_secret'] = 'xxxxxxxxxx';

// For posting
//$globals['twitter_user'] = 'xxx';
//$globals['twitter_consumer_key'] = 'xxxxxx';
//$globals['twitter_consumer_secret'] = 'xxxxxx';
//$globals['twitter_token'] = 'xxxxxx-xxxxxx';
//$globals['twitter_token_secret'] = 'xxxxxx';
// Show the link in the horizontal bar
// $globals['twitter_page'] = 'http://twitter.com/meneame_net';


// For Facebook authentication
//$globals['facebook_key'] = 'xxxxxxxx';
//$globals['facebook_secret'] = 'xxxxxxxx';
// For Facebook page
//$globals['facebook_token'] = '';
// Show the link in the horizontal bar
// $globals['facebook_page'] = 'http://facebook.com/meneameoficial';


// Jaiku settings
//$globals['jaiku_user'] = '';
//$globals['jaiku_key'] = '';

// posthubsubbub
//$globals['pubsub'] = 'http://pubsubhubbub.appspot.com/';


// Websnapr.com previews
$globals['do_websnapr'] = false;

// Kalooga related images
// Example
//$globals['kalooga_categories'] = array(27, 28);

// Automatic thumbnails size
$globals['thumb_size'] = 60;
$globals['medium_thumb_size'] = 420;

// Amazon setup
//$globals['Amazon_access_key'] = '';
//$globals['Amazon_secret_key'] = '';
//$globals['Amazon_S3_media_bucket'] = '';
//$globals['Amazon_S3_media_url'] = '';
$globals['Amazon_S3_local_cache'] = true;
$globals['Amazon_S3_upload'] = true;
$globals['Amazon_S3_delete_allowed'] = false;


// Main javascript file
$globals['jquery'] = '//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js';
$globals['jquery2'] = '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js';
$globals['js_main'] = 'main.js.php';
// CSS files (main, color, notame)
$globals['css_main'] = 'mnm.php';
// Load webfonts from the specified url
// $globals['css_webfonts'] = '//fonts.googleapis.com/css?family=Open+Sans|Open+Sans+Condensed:300';
$globals['css_webfonts'] = "//fonts.googleapis.com/css?family=Roboto:400";
//$globals['css_color'] = 'css/es/mnmcol17.css';
$globals['html_main'] = 'html1.php';
$globals['thread_padding_percent'] = 3; // Padding for threaded comments
$globals['thread_padding_max_percent'] = 20; // Don't pad more than this %

// Store access stats
$globals['save_pageloads'] = false;

$globals['enabled_tags'] = 'b|i|em|strong|del|sup|sub'; // HTML tags allowed for comments and posts

// This is for SMS messages
// Put your authorised addresses
//$globals['allowed_gsm_clients'] = 'localhost 192.168.0.1';

// Disable checking of IP networks during registration
//$globals['skip_ip_register'] = false;

// Bonus applied to new links
$globals['bonus_coef'] = 1.5;
// The min karma coeffcient in promote10.php
$globals['min_karma_coef'] = 0.85;
// Bonus applied to sources that are not frequent
$globals['new_source_bonus'] = 1.05;
$globals['new_source_max_hours'] = 240;
$globals['new_source_min_hours'] = 48;


// The maximun amount of annonymous votes vs user votes in 1/2 hour
// 3 means 3 times annonymous votes as user votes in that period
$globals['anon_to_user_votes'] = 0.2;
$site_key = 12345679;
// Check this
$globals['anon_karma']	= 4;


$globals['user_agent'] = 'Meneamebot (http://meneame.net/)';

// Send logs to "log_user", it's windows compatible
openlog('meneame', LOG_ODELAY, LOG_USER);

//////////////////////////////////////
// Don't touch behind this
//////////////////////////////////////
// Set an utf-8 locale if there is no utf-8 defined
if(stripos(setlocale(LC_CTYPE, 0), "utf-8") === false) {
	setlocale(LC_CTYPE, "en_US.UTF-8");
}

// There is another config file, this is called for defaults (used by mobile)
if (!isset($globals['basic_config']) || !$globals['basic_config']) {
	define("mnmpath", dirname(__FILE__));
	define("mnminclude", dirname(__FILE__).'/libs/');
	ini_set("include_path", '.:'.mnminclude.':'.mnmpath);

	@include('local.php');
	if (php_sapi_name() == 'cli') {
		$globals['cli'] = True;
		/* Definition only for scripts executed "off-line" */
		@include('cli-local.php');
	} else {
		$globals['cli'] = False;
		@include($_SERVER['SERVER_NAME'].'-local.php');
	}
		
	// @include($_SERVER['SERVER_ADDR'].'-local.php');


	include mnminclude.'init.php';
	include mnminclude.'login.php';

	// For production servers
	$db->hide_errors();
}



