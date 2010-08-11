<?
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
//$globals['server_name']	= $_SERVER['HTTP_HOST'];
$globals['lang'] = $dblang	= 'es';
$globals['page_size'] = $page_size	= 30;
$globals['anonnymous_vote'] = $anonnymous_vote = true;
$globals['external_ads'] = $external_ads = true;
$globals['behind_load_balancer'] = False; // LB as those in Amazon EC2 don't send the real remote address
$globals['ssl_server'] = False; //Secure server must have the same name and base
//$globals['email_domain'] = 'my_email_domain.com'; // Used for sending emails from web, if not defined it uses server_name

//Configuration values
//$globals['external_ads'] = false;
//$globals['external_user_ads'] = false;

$globals['db_server'] = 'localhost';
$globals['db_name'] = 'meneame';
$globals['db_user'] = 'meneame';
$globals['db_password'] = '';
$globals['db_use_transactions'] = true; // Disable it if you use MyISAM and have high loads

//Specify the static web server, wiith port included, use same document root as the main server (i.e. base_url is used
$globals['static_server'] = '';
//$globals['static_server'] = 'http://static.meneame.net';

//URL shortener (used in menea.me)
//$globals['url_shortener'] = 'menea.me';
//$globals['url_shortener_to'] = 'meneame.net';
//$globals['url_shortener_mobile_to'] = 'm.meneame.net'; // Automatic redirection to mobile version


// Specify you base url, "/" if is the root document
// $globals['base_dir'] = '/meneame/';
$globals['base_url'] = '/';
$globals['top_logo'] = 'img/mnm/eli.png';
$globals['thumbnail_logo'] = 'img/mnm/eli_thumbnail.png';
$globals['legal'] = $globals['base_url'].'legal.php';

$globals['min_decay'] = 0.25; 
$globals['karma_start_decay'] = 10; // In hours, when a link start to decrease its karma
$globals['karma_decay'] = 54; // In hours, when it reach its minimum
//$globals['news_meta'] = 102; // The code of the "last news" meta category, other has a longer no decreasing period
// Similar values for "news_meta", used if the previous is defined
$globals['karma_news_start_decay'] =  7;
$globals['karma_news_decay'] = 42;



// leave empty if you don't have the rewrite rule in apache

//RewriteRule ^/story/(.+)$ /story.php/$1  [L,NS]
$globals['base_story_url'] = 'story/';

//RewriteRule ^/c/(.+)$ /comment.php/$1  [L,NS]
$globals['base_comment_url'] = 'c/';

//RewriteRule ^/search(/.*)$ /search.php$1 [L,NS,NE,PT]
//$globals['base_search_url'] = 'search/';

//RewriteRule ^/user/(.+)$ /user.php/$1  [L,NS]
$globals['base_user_url'] = 'user/';

//RewriteRule ^/notame(/.*)$ /sneakme/index.php$1 [L,NS]
$globals['base_sneakme_url'] = 'notame/';

// Memcache, set hostname if enabled
//$globals['memcache_host'] = 'localhost'; 
$globals['memcache_port'] = 11211; // optional
$globals['xcache_enabled'] = false; // enable it if want to use xcache vars

// Comment pages
$globals['comments_page_size'] = 100;
$globals['comments_page_threshold'] = 1.10;

// Min karma to highlights comments
// The negative is used to hide comments
$globals['comment_highlight_karma'] = 100;
$globals['comment_hidden_karma'] = -100;


// Give 4 minutes to edit a comment
$globals['comment_edit_time'] = 240;

// How many *global* links for last 3 minutes
// If user->karma > 10 then limit = limit*1.5
$globals['limit_3_minutes'] = 10;
$globals['limit_3_minutes_karma'] = 10;

$globals['limit_user_24_hours'] = 12;

$globals['karma_propaganda'] = 12; // min user karma to avoid extra spam/propaganda checks in the submits


$globals['karma_base']=6;
$globals['karma_base_max']=9; // If not penalised, older users can get up to this value as base for the calculus
$globals['min_karma']=1; //min user karma
$globals['max_karma']=20; //max user karma
$globals['special_karma_gain']=17; //karma to gain 'special' status (max * 0.85)
$globals['special_karma_loss']=12; //karma to loss 'special' status (max * 0.6)

$globals['comment_votes_multiplier'] = 5; //'importance' in karma calculations of comment votes
$globals['post_votes_multiplier'] = 1; //'importance' in karma calculations of post votes

$globals['instant_karma_per_published'] = 1; //karma added when published
$globals['instant_karma_per_depublished'] = 1.2; //karma substracted when depublished
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
//$globals['redirect_feedburner'] = false;

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
$globals['draft_limit'] = 3; // Max unset drafts at the same time


// Check it's writeable by the web server
$globals['cache_dir'] = 'cache';


// Haanga templates configuration
$globals['haanga_cache'] = '/var/tmp';
$globals['haanga_templates'] = 'templates';

//$globals['lucene_dir'] = mnmpath.'/'.$globals['cache_dir'].'/lucene_links';
$globals['sphinx_server'] = 'localhost';
$globals['sphinx_port'] = 9312;

$globals['avatars_check_always'] = true;
$globals['avatars_max_size'] = 400000;
$globals['avatars_allowed_sizes'] = Array (80, 40, 25, 20);


$globals['show_popular_queued'] = true;
$globals['show_popular_published'] = true;

// Recaptcha settings
$globals['captcha_first_login'] = false;
// Put your keys en to enable recaptcha
//$globals['recaptcha_public_key'] = '';
//$globals['recaptcha_private_key'] = '';

// Twitter settings
//$globals['twitter_user'] = '';
//$globals['twitter_password'] = '';
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
$globals['thumb_size'] = 70;

// Amazon setup
//$globals['Amazon_access_key'] = '';
//$globals['Amazon_secret_key'] = '';
//$globals['Amazon_S3_media_bucket'] = '';
//$globals['Amazon_S3_media_url'] = '';
$globals['Amazon_S3_local_cache'] = true;


// Main javascript file
$globals['js_main'] = 'general02.js.php';
// CSS files (main, color, notame)
$globals['css_main'] = 'css/es/mnm65.css';
//$globals['css_color'] = 'css/es/mnmcol17.css';
$globals['html_main'] = 'html1.php';

// Store access stats
$globals['save_pageloads'] = false;

$globals['enabled_tags'] = 'b|i|em|strong|strike'; // HTML tags allowed for comments and posts

// Greeting in several languages
// Unfortunately, array constructor does not work properly with GNU _()
$globals['greetings'] = array('bienvenid@'=>'españolo y española ;-)','hola'=>'español','kaixo'=>'euskera',
		'apa'=>'catalán','com va'=>'catalán','va bé?'=>'catalán','cómo andás'=>'argentino','epa'=>'catalán',
		'aupa'=>'español','ieup'=>'vasco','gñap'=>'gñapés','aiya'=>'quenya','hello'=>'inglés',
		'uep com anam'=>'catalán','hey'=>'inglés','hi'=>'inglés','hunga hunga'=>'troglodita',
		'salut'=>'francés','bonjour'=>'francés','hallo'=>'alemán','guten tag'=>'alemán','moin moin'=>'frisón',
		'Dobrý deň'=>'eslovaco','helo'=>'SMTP','minjhani'=>'tsonga','kunjhani'=>'tsonga','ciao'=>'italiano',
		'hej'=>'danés','god dag'=>'noruego','have a nice day'=>'inglés','as-salaam-aleykum'=>'árabe',
		'marhabah'=>'árabe','sabbah-el-khair'=>'árabe','salaam or do-rood'=>'árabe','namaste'=>'hindi',
		'ahn nyeong ha se yo'=>'coreano','ahn nyeong'=>'coreano','goedendag'=>'neerlandés','priviet'=>'ruso',
		'zdravstvuyte'=>'ruso','ni hao'=>'mandarín','nei ho'=>'cantonés','shalom'=>'hebreo','hei'=>'finés',
		'oi'=>'portugués','olá'=>'portugués','hej'=>'sueco','god dag'=>'sueco','mingalarbar'=>'birmano',
		'merhaba'=>'turco','ciao'=>'italiano','kumusta ka'=>'tagalo','saluton'=>'esperanto','vanakkam'=>'tamil',
		'jambo'=>'swahili','mbote'=>'lingala','namaskar'=>'malayalam','dzień dobry'=>'polaco','cześć'=>'polaco',
		'aloha'=>'hawaiano','jo napot'=>'húngaro','szervusz'=>'húngaro','dobriy ranok'=>'ucraniano',
		'labdien'=>'letón','sveiki'=>'letón','chau'=>'letón','hyvää päivää'=>'finés','moi'=>'finés',
		'hei'=>'finés','yia sou'=>'griego','yia sas'=>'griego','góðan dag'=>'islandés','hæ'=>'islandés',
		'ellohay'=>'pig latin','namaskkaram'=>'telugú','adaab'=>'urdu','baagunnara'=>'telugú','niltze'=>'náhuatl',
		'hao'=>'náhuatl','bok'=>'croata','ya\'at\'eeh'=>'navajo','merħba'=>'maltés','mambo'=>'congo',
		'salam aleikum'=>'senegalés','grüzi'=>'alemán suizo','haj'=>'escandinavo','hallå'=>'escandinavo',
		'moïen'=>'luxemburgués','talofa'=>'samoano','malo'=>'samoano','malo e lelei'=>'tongano',
		'la orana'=>'tahitiano','kia ora'=>'maorí','buna ziua'=>'rumano','kem che'=>'guyaratí',
		'namaskar'=>'canarés','kwe kwe'=>'tailandés','hola, oh'=>'asturiano','hâu'=>'nicolino',
		'vary'=>'nicolino','Привет'=>'ruso','konnichiwa'=>'japonés','hello world'=>'holamundo',
		'klaatu barada nikto'=>'el idioma de Klatu y Gort',
		'ola'=>'gallego','boas'=>'gallego', 'bonos díes'=>'asturiano', 'nuqneH'=>'klingon',
		'Mba\'eichapa' => 'guaraní', 'Mba\'eteko' => 'guaraní'
	);

// This is for SMS messages
// Put your authorised addresses
//$globals['allowed_gsm_clients'] = 'localhost 192.168.0.1';

// Bonus applied to new links
$globals['bonus_coef'] = 1.5;
// Bonus applied to sources that are not frequent
$globals['new_source_bonus'] = 1.05;
$globals['new_source_max_hours'] = 240;
$globals['new_source_min_hours'] = 48;


// The maximun amount of annonymous votes vs user votes in 1/2 hour
// 3 means 3 times annonymous votes as user votes in that period
$anon_to_user_votes = 0.5;
$site_key = 12345679;
// Check this
$anon_karma	= 4;


$globals['user_agent'] = 'Meneamebot (http://meneame.net/)';

// Send logs to "log_user", is windows compatible
openlog(false, LOG_ODELAY, LOG_USER);

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
	@include($_SERVER['HTTP_HOST'].'-local.php');
	@include($_SERVER['SERVER_ADDR'].'-local.php');


	include mnminclude.'init.php';
	include mnminclude.'login.php';

	// For production servers
	$db->hide_errors();
}




?>
