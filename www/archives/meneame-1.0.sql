-- MySQL dump 10.9
--
-- Host: localhost    Database: meneame
-- ------------------------------------------------------
-- Server version	4.1.11-Debian_4sarge2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

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
  `category_name` varchar(64) collate utf8_spanish_ci NOT NULL default '',
  PRIMARY KEY  (`category__auto_id`),
  UNIQUE KEY `category_lang` (`category_lang`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

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
  `comment_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `comment_karma` smallint(6) NOT NULL default '0',
  `comment_nick` varchar(32) collate utf8_spanish_ci default NULL,
  `comment_content` text collate utf8_spanish_ci NOT NULL,
  PRIMARY KEY  (`comment_id`),
  UNIQUE KEY `comments_randkey` (`comment_randkey`,`comment_link_id`,`comment_user_id`),
  KEY `comment_link_id_2` (`comment_link_id`,`comment_date`),
  KEY `comment_date` (`comment_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

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
  `link_karma` decimal(10,2) NOT NULL default '0.00',
  `link_modified` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `link_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `link_published_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `link_category` int(11) NOT NULL default '0',
  `link_lang` varchar(4) collate utf8_spanish_ci NOT NULL default 'es',
  `link_url` varchar(200) collate utf8_spanish_ci NOT NULL default '',
  `link_url_title` text collate utf8_spanish_ci,
  `link_title` text collate utf8_spanish_ci NOT NULL,
  `link_content` text collate utf8_spanish_ci NOT NULL,
  `link_tags` text collate utf8_spanish_ci,
  PRIMARY KEY  (`link_id`),
  KEY `link_author` (`link_author`),
  KEY `link_url` (`link_url`),
  KEY `link_date` (`link_date`),
  KEY `link_published_date` (`link_published_date`),
  FULLTEXT KEY `link_url_2` (`link_url`,`link_url_title`,`link_title`,`link_content`,`link_tags`),
  FULLTEXT KEY `link_tags` (`link_tags`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

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
  `user_login` varchar(32) collate utf8_spanish_ci NOT NULL default '',
  `user_level` enum('normal','special','blogger','admin','god') collate utf8_spanish_ci NOT NULL default 'normal',
  `user_modification` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `user_date` timestamp NOT NULL default '0000-00-00 00:00:00',
  `user_validated_date` timestamp NULL default NULL,
  `user_ip` varchar(32) collate utf8_spanish_ci default NULL,
  `user_pass` varchar(64) collate utf8_spanish_ci NOT NULL default '',
  `user_email` varchar(128) collate utf8_spanish_ci NOT NULL default '',
  `user_names` varchar(128) collate utf8_spanish_ci NOT NULL default '',
  `user_lang` int(11) NOT NULL default '1',
  `user_karma` decimal(10,2) default '6.00',
  `user_url` varchar(128) collate utf8_spanish_ci NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_login` (`user_login`),
  KEY `user_email` (`user_email`)
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
  `vote_ip` char(24) collate utf8_spanish_ci default NULL,
  PRIMARY KEY  (`vote_id`),
  KEY `user_id` (`vote_user_id`),
  KEY `vote_type` (`vote_type`,`vote_link_id`,`vote_user_id`,`vote_ip`),
  KEY `vote_type_2` (`vote_type`,`vote_user_id`),
  KEY `vote_user_id` (`vote_user_id`,`vote_date`),
  KEY `vote_date` (`vote_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci PACK_KEYS=0;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

