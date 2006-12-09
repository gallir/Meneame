-- MySQL dump 10.10
--
-- Host: localhost    Database: meneame
-- ------------------------------------------------------
-- Server version	5.0.24a-Debian_9-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `avatars`
--

DROP TABLE IF EXISTS `avatars`;
CREATE TABLE `avatars` (
  `avatar_id` int(11) NOT NULL,
  `avatar_image` blob NOT NULL,
  PRIMARY KEY  (`avatar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
CREATE TABLE `blogs` (
  `blog_id` int(20) NOT NULL auto_increment,
  `blog_key` varchar(35) collate utf8_spanish_ci default NULL,
  `blog_type` enum('normal','blog') collate utf8_spanish_ci NOT NULL default 'normal',
  `blog_rss` varchar(64) collate utf8_spanish_ci NOT NULL default '',
  `blog_rss2` varchar(64) collate utf8_spanish_ci NOT NULL default '',
  `blog_atom` varchar(64) collate utf8_spanish_ci NOT NULL default '',
  `blog_url` varchar(64) collate utf8_spanish_ci default NULL,
  PRIMARY KEY  (`blog_id`),
  UNIQUE KEY `key` (`blog_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category__auto_id` int(11) NOT NULL auto_increment,
  `category_lang` varchar(4) collate utf8_spanish_ci NOT NULL default 'es',
  `category_id` int(11) NOT NULL default '0',
  `category_parent` int(11) NOT NULL default '0',
  `category_name` char(64) collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`category__auto_id`),
  UNIQUE KEY `category_lang` (`category_lang`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
CREATE TABLE `chats` (
  `chat_time` int(10) unsigned NOT NULL default '0',
  `chat_uid` int(10) unsigned NOT NULL default '0',
  `chat_user` char(32) NOT NULL,
  `chat_text` char(255) NOT NULL,
  KEY `chat_time` USING BTREE (`chat_time`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 MAX_ROWS=1000;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `comment_id` int(20) NOT NULL auto_increment,
  `comment_randkey` int(11) NOT NULL default '0',
  `comment_parent` int(20) default '0',
  `comment_link_id` int(20) NOT NULL default '0',
  `comment_user_id` int(20) NOT NULL default '0',
  `comment_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `comment_ip` char(24) collate utf8_spanish_ci default NULL,
  `comment_order` smallint(6) NOT NULL default '0',
  `comment_votes` smallint(4) NOT NULL default '0',
  `comment_karma` smallint(6) NOT NULL default '0',
  `comment_content` text collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`comment_id`),
  UNIQUE KEY `comments_randkey` (`comment_randkey`,`comment_link_id`,`comment_user_id`),
  KEY `comment_link_id_2` (`comment_link_id`,`comment_date`),
  KEY `comment_date` (`comment_date`),
  KEY `comment_user_id` (`comment_user_id`,`comment_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE `favorites` (
  `favorite_user_id` int(10) unsigned NOT NULL,
  `favorite_link_id` int(10) unsigned NOT NULL,
  `favorite_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `favorite_user_id` (`favorite_user_id`,`favorite_link_id`),
  KEY `favorite_link_id` (`favorite_link_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;
CREATE TABLE `friends` (
  `friend_type` enum('affiliate','manual','hide') collate utf8_spanish_ci NOT NULL default 'affiliate',
  `friend_from` int(10) NOT NULL default '0',
  `friend_to` int(10) NOT NULL default '0',
  `friend_value` decimal(10,6) NOT NULL default '0.000000',
  UNIQUE KEY `friend_type_2` (`friend_type`,`friend_from`,`friend_to`,`friend_value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `language_id` int(11) NOT NULL auto_increment,
  `language_name` varchar(64) collate utf8_spanish_ci NOT NULL default '',
  PRIMARY KEY  (`language_id`),
  UNIQUE KEY `language_name` (`language_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `link_id` int(20) NOT NULL auto_increment,
  `link_author` int(20) NOT NULL default '0',
  `link_blog` int(20) default '0',
  `link_status` enum('discard','queued','published','abuse','duplicated') collate utf8_spanish_ci NOT NULL default 'discard',
  `link_randkey` int(20) NOT NULL default '0',
  `link_votes` int(20) NOT NULL default '0',
  `link_negatives` int(11) NOT NULL default '0',
  `link_comments` int(11) unsigned NOT NULL default '0',
  `link_karma` decimal(10,2) NOT NULL default '0.00',
  `link_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `link_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `link_published_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `link_category` int(11) NOT NULL default '0',
  `link_lang` varchar(4) collate utf8_spanish_ci NOT NULL default 'es',
  `link_uri` char(100) collate utf8_spanish_ci default NULL,
  `link_url` varchar(250) collate utf8_spanish_ci NOT NULL,
  `link_url_title` text collate utf8_spanish_ci,
  `link_title` text collate utf8_spanish_ci NOT NULL,
  `link_content` text collate utf8_spanish_ci NOT NULL,
  `link_tags` text collate utf8_spanish_ci,
  PRIMARY KEY  (`link_id`),
  KEY `link_author` (`link_author`),
  KEY `link_url` (`link_url`),
  KEY `link_date` (`link_date`),
  KEY `link_published_date` (`link_published_date`),
  KEY `link_uri` (`link_uri`),
  KEY `status_i` (`link_status`),
  FULLTEXT KEY `link_url_2` (`link_url`,`link_url_title`,`link_title`,`link_content`,`link_tags`),
  FULLTEXT KEY `link_tags` (`link_tags`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL auto_increment,
  `log_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `log_type` enum('link_new','comment_new','link_publish','link_discard','comment_edit','link_edit') character set utf8 collate utf8_spanish_ci NOT NULL,
  `log_ref_id` int(11) NOT NULL,
  `log_user_id` int(11) NOT NULL,
  `log_ip` char(24) character set utf8 collate utf8_spanish_ci default NULL,
  PRIMARY KEY  (`log_id`),
  KEY `log_date` (`log_date`),
  KEY `log_type` (`log_type`,`log_ref_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table structure for table `sneakers`
--

DROP TABLE IF EXISTS `sneakers`;
CREATE TABLE `sneakers` (
  `sneaker_id` char(24) NOT NULL,
  `sneaker_time` int(10) unsigned NOT NULL default '0',
  `sneaker_user` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `sneaker_id` (`sneaker_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 MAX_ROWS=1000;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `tag_link_id` int(11) NOT NULL default '0',
  `tag_lang` varchar(4) collate utf8_spanish_ci NOT NULL default 'es',
  `tag_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `tag_words` varchar(64) collate utf8_spanish_ci NOT NULL default '',
  UNIQUE KEY `tag_link_id` (`tag_link_id`,`tag_lang`,`tag_words`),
  KEY `tag_lang` (`tag_lang`,`tag_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Table structure for table `trackbacks`
--

DROP TABLE IF EXISTS `trackbacks`;
CREATE TABLE `trackbacks` (
  `trackback_id` int(10) unsigned NOT NULL auto_increment,
  `trackback_link_id` int(11) NOT NULL default '0',
  `trackback_user_id` int(11) NOT NULL default '0',
  `trackback_type` enum('in','out') collate utf8_spanish_ci NOT NULL default 'in',
  `trackback_status` enum('ok','pendent','error') collate utf8_spanish_ci NOT NULL default 'pendent',
  `trackback_modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `trackback_date` timestamp NULL default NULL,
  `trackback_url` varchar(200) collate utf8_spanish_ci NOT NULL default '',
  `trackback_title` text collate utf8_spanish_ci,
  `trackback_content` text collate utf8_spanish_ci,
  PRIMARY KEY  (`trackback_id`),
  UNIQUE KEY `trackback_link_id_2` (`trackback_link_id`,`trackback_type`,`trackback_url`),
  KEY `trackback_link_id` (`trackback_link_id`),
  KEY `trackback_url` (`trackback_url`),
  KEY `trackback_date` (`trackback_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(20) NOT NULL auto_increment,
  `user_login` char(32) collate utf8_spanish_ci NOT NULL,
  `user_level` enum('disabled','normal','special','blogger','admin','god') collate utf8_spanish_ci NOT NULL default 'normal',
  `user_avatar` tinyint(1) NOT NULL default '0',
  `user_modification` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `user_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `user_validated_date` timestamp NULL default NULL,
  `user_ip` char(32) collate utf8_spanish_ci default NULL,
  `user_pass` char(64) collate utf8_spanish_ci NOT NULL,
  `user_email` char(64) collate utf8_spanish_ci NOT NULL,
  `user_names` char(60) collate utf8_spanish_ci NOT NULL,
  `user_login_register` char(32) collate utf8_spanish_ci default NULL,
  `user_email_register` char(64) collate utf8_spanish_ci default NULL,
  `user_lang` tinyint(2) unsigned NOT NULL default '1',
  `user_comment_pref` tinyint(2) unsigned NOT NULL default '0',
  `user_karma` decimal(10,2) default '6.00',
  `user_url` char(128) collate utf8_spanish_ci NOT NULL,
  `user_adcode` char(24) collate utf8_spanish_ci default NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_login` (`user_login`),
  KEY `user_email` (`user_email`),
  KEY `user_karma` (`user_karma`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
CREATE TABLE `votes` (
  `vote_id` int(20) NOT NULL auto_increment,
  `vote_type` enum('links','comments') collate utf8_spanish_ci NOT NULL default 'links',
  `vote_date` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `vote_link_id` int(20) NOT NULL default '0',
  `vote_user_id` int(20) NOT NULL default '0',
  `vote_value` smallint(11) NOT NULL default '1',
  `vote_ip_int` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`vote_id`),
  UNIQUE KEY `vote_type` (`vote_type`,`vote_link_id`,`vote_user_id`,`vote_ip_int`),
  KEY `vote_type_2` (`vote_type`,`vote_user_id`),
  KEY `vote_type_4` (`vote_type`,`vote_date`,`vote_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci PACK_KEYS=0;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

