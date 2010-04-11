-- MySQL dump 10.13  Distrib 5.1.37, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: meneame
-- ------------------------------------------------------
-- Server version	5.1.37-1ubuntu5.1-log

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
-- Table structure for table `annotations`
--

DROP TABLE IF EXISTS `annotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `annotations` (
  `annotation_key` char(64) NOT NULL,
  `annotation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `annotation_expire` timestamp NULL DEFAULT NULL,
  `annotation_text` text,
  PRIMARY KEY (`annotation_key`),
  KEY `annotation_expire` (`annotation_expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auths`
--

DROP TABLE IF EXISTS `auths`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auths` (
  `user_id` int(10) unsigned NOT NULL,
  `service` char(32) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `username` char(32) NOT NULL DEFAULT '''''',
  `token` char(64) NOT NULL DEFAULT '''''',
  `secret` char(64) NOT NULL DEFAULT '''''',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `service` (`service`,`uid`),
  KEY `user_id` (`user_id`),
  KEY `service_2` (`service`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `avatars`
--

DROP TABLE IF EXISTS `avatars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avatars` (
  `avatar_id` int(11) NOT NULL,
  `avatar_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `avatar_image` blob NOT NULL,
  PRIMARY KEY (`avatar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bans`
--

DROP TABLE IF EXISTS `bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bans` (
  `ban_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ban_type` enum('email','hostname','punished_hostname','ip','words','proxy') NOT NULL,
  `ban_text` char(64) NOT NULL,
  `ban_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ban_expire` timestamp NULL DEFAULT NULL,
  `ban_comment` char(100) DEFAULT NULL,
  PRIMARY KEY (`ban_id`),
  UNIQUE KEY `ban_type` (`ban_type`,`ban_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blogs` (
  `blog_id` int(20) NOT NULL AUTO_INCREMENT,
  `blog_key` char(35) COLLATE utf8_spanish_ci DEFAULT NULL,
  `blog_type` enum('normal','blog') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'normal',
  `blog_rss` varchar(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `blog_rss2` varchar(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `blog_atom` varchar(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `blog_url` varchar(64) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`blog_id`),
  UNIQUE KEY `key` (`blog_key`),
  KEY `blog_url` (`blog_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `category__auto_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_lang` char(4) COLLATE utf8_spanish_ci NOT NULL DEFAULT 'es',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `category_parent` int(11) NOT NULL DEFAULT '0',
  `category_name` char(32) COLLATE utf8_spanish_ci NOT NULL,
  `category_uri` char(32) COLLATE utf8_spanish_ci DEFAULT NULL,
  `category_calculated_coef` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`category__auto_id`),
  UNIQUE KEY `category_lang` (`category_lang`,`category_id`),
  UNIQUE KEY `id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `chats`
--

DROP TABLE IF EXISTS `chats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chats` (
  `chat_time` decimal(12,2) unsigned NOT NULL DEFAULT '0.00',
  `chat_uid` int(10) unsigned NOT NULL DEFAULT '0',
  `chat_room` enum('all','friends','admin') NOT NULL DEFAULT 'all',
  `chat_user` char(32) NOT NULL,
  `chat_text` char(255) NOT NULL,
  KEY `chat_time` (`chat_time`) USING BTREE
) ENGINE=MEMORY DEFAULT CHARSET=utf8 MAX_ROWS=1000;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clones`
--

DROP TABLE IF EXISTS `clones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clones` (
  `clon_from` int(10) unsigned NOT NULL,
  `clon_to` int(10) unsigned NOT NULL,
  `clon_ip` char(24) NOT NULL,
  `clon_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`clon_from`,`clon_to`,`clon_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `comment_id` int(20) NOT NULL AUTO_INCREMENT,
  `comment_type` enum('normal','admin','private') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'normal',
  `comment_randkey` int(11) NOT NULL DEFAULT '0',
  `comment_parent` int(20) DEFAULT '0',
  `comment_link_id` int(20) NOT NULL DEFAULT '0',
  `comment_user_id` int(20) NOT NULL DEFAULT '0',
  `comment_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_ip` char(24) COLLATE utf8_spanish_ci DEFAULT NULL,
  `comment_order` smallint(6) NOT NULL DEFAULT '0',
  `comment_votes` smallint(4) NOT NULL DEFAULT '0',
  `comment_karma` smallint(6) NOT NULL DEFAULT '0',
  `comment_content` text COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `comment_link_id_2` (`comment_link_id`,`comment_date`),
  KEY `comment_date` (`comment_date`),
  KEY `comment_user_id` (`comment_user_id`,`comment_date`),
  KEY `comment_link_id` (`comment_link_id`,`comment_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conversations` (
  `conversation_user_to` int(10) unsigned NOT NULL,
  `conversation_type` enum('comment','post','link') NOT NULL,
  `conversation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `conversation_from` int(10) unsigned NOT NULL,
  `conversation_to` int(10) unsigned NOT NULL,
  KEY `conversation_type` (`conversation_type`,`conversation_from`),
  KEY `conversation_time` (`conversation_time`),
  KEY `conversation_type_2` (`conversation_type`,`conversation_to`),
  KEY `conversation_user_to` (`conversation_user_to`,`conversation_type`,`conversation_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `counts`
--

DROP TABLE IF EXISTS `counts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `counts` (
  `key` char(24) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favorites` (
  `favorite_user_id` int(10) unsigned NOT NULL,
  `favorite_type` enum('link','post','comment') NOT NULL DEFAULT 'link',
  `favorite_link_id` int(10) unsigned NOT NULL,
  `favorite_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `favorite_user_id_2` (`favorite_user_id`,`favorite_type`,`favorite_link_id`),
  KEY `favorite_type` (`favorite_type`,`favorite_link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friends` (
  `friend_type` enum('affiliate','manual','hide','affinity') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'affiliate',
  `friend_from` int(10) NOT NULL DEFAULT '0',
  `friend_to` int(10) NOT NULL DEFAULT '0',
  `friend_value` smallint(3) NOT NULL DEFAULT '0',
  UNIQUE KEY `friend_type` (`friend_type`,`friend_from`,`friend_to`),
  KEY `friend_type_2` (`friend_type`,`friend_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_links`
--

DROP TABLE IF EXISTS `geo_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geo_links` (
  `geo_id` int(11) NOT NULL,
  `geo_text` char(80) DEFAULT NULL,
  `geo_pt` point NOT NULL,
  UNIQUE KEY `geo_id` (`geo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_users`
--

DROP TABLE IF EXISTS `geo_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `geo_users` (
  `geo_id` int(11) NOT NULL,
  `geo_text` char(80) DEFAULT NULL,
  `geo_pt` point NOT NULL,
  UNIQUE KEY `geo_id` (`geo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `language_id` int(11) NOT NULL AUTO_INCREMENT,
  `language_name` varchar(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`language_id`),
  UNIQUE KEY `language_name` (`language_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `links` (
  `link_id` int(20) NOT NULL AUTO_INCREMENT,
  `link_author` int(20) NOT NULL DEFAULT '0',
  `link_blog` int(20) DEFAULT '0',
  `link_status` enum('discard','queued','published','abuse','duplicated','autodiscard','metapublished') CHARACTER SET utf8 NOT NULL DEFAULT 'discard',
  `link_randkey` int(20) NOT NULL DEFAULT '0',
  `link_votes` int(20) NOT NULL DEFAULT '0',
  `link_negatives` int(11) NOT NULL DEFAULT '0',
  `link_anonymous` int(10) unsigned NOT NULL DEFAULT '0',
  `link_votes_avg` float NOT NULL DEFAULT '0',
  `link_comments` int(11) unsigned NOT NULL DEFAULT '0',
  `link_karma` decimal(10,2) NOT NULL DEFAULT '0.00',
  `link_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `link_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_sent_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_published_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_category` int(11) NOT NULL DEFAULT '0',
  `link_lang` char(2) CHARACTER SET utf8 NOT NULL DEFAULT 'es',
  `link_ip` char(24) COLLATE utf8_spanish_ci DEFAULT NULL,
  `link_content_type` char(12) COLLATE utf8_spanish_ci DEFAULT NULL,
  `link_uri` char(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `link_url` varchar(250) COLLATE utf8_spanish_ci NOT NULL,
  `link_thumb_status` enum('unknown','checked','error','local','remote','deleted') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'unknown',
  `link_thumb_x` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `link_thumb_y` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `link_thumb` tinytext COLLATE utf8_spanish_ci,
  `link_url_title` text COLLATE utf8_spanish_ci,
  `link_title` text COLLATE utf8_spanish_ci NOT NULL,
  `link_content` text COLLATE utf8_spanish_ci NOT NULL,
  `link_tags` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`link_id`),
  KEY `link_url` (`link_url`),
  KEY `link_uri` (`link_uri`),
  KEY `link_blog` (`link_blog`),
  KEY `link_author` (`link_author`,`link_date`),
  KEY `link_date` (`link_date`),
  KEY `link_status` (`link_status`,`link_date`,`link_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_pos`
--

DROP TABLE IF EXISTS `log_pos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_pos` (
  `host` varchar(60) NOT NULL,
  `time_stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `log_file` varchar(32) DEFAULT NULL,
  `log_pos` int(11) DEFAULT NULL,
  `master_host` varchar(60) DEFAULT NULL,
  `master_log_file` varchar(32) DEFAULT NULL,
  `master_log_pos` int(11) DEFAULT NULL,
  PRIMARY KEY (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_type` enum('link_new','comment_new','link_publish','link_discard','comment_edit','link_edit','post_new','post_edit','login_failed','spam_warn','link_geo_edit','user_new','user_delete','link_depublished') NOT NULL,
  `log_ref_id` int(11) unsigned NOT NULL,
  `log_user_id` int(11) NOT NULL,
  `log_ip` char(24) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_date` (`log_date`),
  KEY `log_type` (`log_type`,`log_ref_id`),
  KEY `log_type_2` (`log_type`,`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pageloads`
--

DROP TABLE IF EXISTS `pageloads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pageloads` (
  `date` date NOT NULL,
  `type` enum('html','ajax','other','rss','image','api','sneaker','bot','geo') NOT NULL DEFAULT 'html',
  `counter` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`date`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `post_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `post_randkey` int(11) NOT NULL DEFAULT '0',
  `post_src` enum('web','api','im','mobile','phone') CHARACTER SET utf8 NOT NULL DEFAULT 'web',
  `post_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `post_user_id` int(11) unsigned NOT NULL,
  `post_visible` enum('all','friends') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'all',
  `post_ip_int` int(11) unsigned NOT NULL,
  `post_votes` smallint(4) NOT NULL DEFAULT '0',
  `post_karma` smallint(6) NOT NULL DEFAULT '0',
  `post_content` text COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`post_id`),
  KEY `post_date` (`post_date`),
  KEY `post_user_id` (`post_user_id`,`post_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prefs`
--

DROP TABLE IF EXISTS `prefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prefs` (
  `pref_user_id` int(11) NOT NULL,
  `pref_key` char(16) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `pref_value` char(6) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  KEY `pref_user_id` (`pref_user_id`,`pref_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sneakers`
--

DROP TABLE IF EXISTS `sneakers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sneakers` (
  `sneaker_id` char(24) NOT NULL,
  `sneaker_time` int(10) unsigned NOT NULL DEFAULT '0',
  `sneaker_user` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `sneaker_id` (`sneaker_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 MAX_ROWS=1000;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sph_counter`
--

DROP TABLE IF EXISTS `sph_counter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sph_counter` (
  `counter_id` int(11) NOT NULL,
  `max_doc_id` int(11) NOT NULL,
  PRIMARY KEY (`counter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `tag_link_id` int(11) NOT NULL DEFAULT '0',
  `tag_lang` char(4) COLLATE utf8_spanish_ci NOT NULL DEFAULT 'es',
  `tag_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tag_words` char(40) COLLATE utf8_spanish_ci NOT NULL,
  UNIQUE KEY `tag_link_id` (`tag_link_id`,`tag_lang`,`tag_words`),
  KEY `tag_lang` (`tag_lang`,`tag_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `trackbacks`
--

DROP TABLE IF EXISTS `trackbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trackbacks` (
  `trackback_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trackback_link_id` int(11) NOT NULL DEFAULT '0',
  `trackback_user_id` int(11) NOT NULL DEFAULT '0',
  `trackback_type` enum('in','out') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'in',
  `trackback_status` enum('ok','pendent','error') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'pendent',
  `trackback_date` timestamp NULL DEFAULT NULL,
  `trackback_ip_int` int(10) unsigned NOT NULL DEFAULT '0',
  `trackback_link` varchar(250) COLLATE utf8_spanish_ci NOT NULL,
  `trackback_url` varchar(250) COLLATE utf8_spanish_ci DEFAULT NULL,
  `trackback_title` text COLLATE utf8_spanish_ci,
  `trackback_content` text COLLATE utf8_spanish_ci,
  PRIMARY KEY (`trackback_id`),
  UNIQUE KEY `trackback_link_id_2` (`trackback_link_id`,`trackback_type`,`trackback_link`),
  KEY `trackback_link_id` (`trackback_link_id`),
  KEY `trackback_url` (`trackback_url`),
  KEY `trackback_date` (`trackback_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int(20) NOT NULL AUTO_INCREMENT,
  `user_login` char(32) COLLATE utf8_spanish_ci NOT NULL,
  `user_level` enum('autodisabled','disabled','normal','special','blogger','admin','god') CHARACTER SET utf8 NOT NULL DEFAULT 'normal',
  `user_avatar` int(10) unsigned NOT NULL DEFAULT '0',
  `user_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_validated_date` timestamp NULL DEFAULT NULL,
  `user_ip` char(32) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_pass` char(64) COLLATE utf8_spanish_ci NOT NULL,
  `user_email` char(64) COLLATE utf8_spanish_ci NOT NULL,
  `user_names` char(60) COLLATE utf8_spanish_ci NOT NULL,
  `user_login_register` char(32) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_email_register` char(64) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_lang` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `user_comment_pref` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `user_karma` decimal(10,2) DEFAULT '6.00',
  `user_public_info` char(64) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_url` char(128) COLLATE utf8_spanish_ci NOT NULL,
  `user_adcode` char(24) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_adchannel` char(12) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_phone` char(16) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_login` (`user_login`),
  KEY `user_email` (`user_email`),
  KEY `user_karma` (`user_karma`),
  KEY `user_public_info` (`user_public_info`),
  KEY `user_phone` (`user_phone`),
  KEY `user_date` (`user_date`),
  KEY `user_modification` (`user_modification`),
  KEY `user_email_register` (`user_email_register`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `votes` (
  `vote_id` int(20) NOT NULL AUTO_INCREMENT,
  `vote_type` enum('links','comments','posts') CHARACTER SET utf8 NOT NULL DEFAULT 'links',
  `vote_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vote_link_id` int(20) NOT NULL DEFAULT '0',
  `vote_user_id` int(20) NOT NULL DEFAULT '0',
  `vote_value` smallint(11) NOT NULL DEFAULT '1',
  `vote_ip_int` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`vote_id`),
  UNIQUE KEY `vote_type` (`vote_type`,`vote_link_id`,`vote_user_id`,`vote_ip_int`),
  KEY `vote_type_2` (`vote_type`,`vote_user_id`),
  KEY `vote_type_4` (`vote_type`,`vote_date`,`vote_user_id`),
  KEY `vote_ip_int` (`vote_ip_int`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci PACK_KEYS=0;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `votes_summary`
--

DROP TABLE IF EXISTS `votes_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `votes_summary` (
  `votes_year` smallint(4) NOT NULL,
  `votes_month` tinyint(2) NOT NULL,
  `votes_type` char(10) NOT NULL,
  `votes_maxid` int(11) NOT NULL,
  `votes_count` int(11) NOT NULL,
  UNIQUE KEY `votes_year` (`votes_year`,`votes_month`,`votes_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-04-11 23:35:28
