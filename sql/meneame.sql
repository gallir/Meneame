-- MySQL dump 10.13  Distrib 5.6.19, for debian-linux-gnu (x86_64)
--
-- Host: db.meneame.net    Database: meneame
-- ------------------------------------------------------
-- Server version	5.6.19-log

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
  `uid` decimal(24,0) unsigned NOT NULL,
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
  `ban_type` enum('email','hostname','punished_hostname','ip','words','proxy','noaccess') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `ban_text` char(64) NOT NULL,
  `ban_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ban_expire` timestamp NULL DEFAULT NULL,
  `ban_comment` char(100) DEFAULT NULL,
  PRIMARY KEY (`ban_id`),
  UNIQUE KEY `ban_type` (`ban_type`,`ban_text`),
  KEY `expire` (`ban_expire`)
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
  `blog_type` enum('normal','blog','noiframe','redirector','aggregator') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'normal',
  `blog_rss` varchar(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `blog_rss2` varchar(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `blog_atom` varchar(64) COLLATE utf8_spanish_ci NOT NULL DEFAULT '',
  `blog_url` varchar(64) COLLATE utf8_spanish_ci DEFAULT NULL,
  `blog_feed` char(128) COLLATE utf8_spanish_ci DEFAULT NULL,
  `blog_feed_checked` timestamp NULL DEFAULT NULL,
  `blog_feed_read` timestamp NULL DEFAULT NULL,
  `blog_title` char(128) COLLATE utf8_spanish_ci DEFAULT NULL,
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
  `clon_ip` char(48) NOT NULL DEFAULT '',
  `clon_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`clon_from`,`clon_to`,`clon_ip`),
  KEY `to_date` (`clon_to`,`clon_date`),
  KEY `from_date` (`clon_from`,`clon_date`),
  KEY `clon_date` (`clon_date`),
  KEY `clon_ip` (`clon_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `comment_ip_int` decimal(39,0) NOT NULL,
  `comment_ip` varbinary(42) DEFAULT NULL,
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
  KEY `conversation_user_to` (`conversation_user_to`,`conversation_type`,`conversation_time`),
  KEY `conversation_type_3` (`conversation_type`,`conversation_user_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `counts`
--

DROP TABLE IF EXISTS `counts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `counts` (
  `key` char(64) NOT NULL,
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
  `favorite_link_readed` int(1) unsigned NOT NULL DEFAULT 0,
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
  `friend_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `friend_type` (`friend_type`,`friend_from`,`friend_to`),
  KEY `friend_type_3` (`friend_type`,`friend_to`,`friend_date`)
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
-- Table structure for table `html_images_seen`
--

DROP TABLE IF EXISTS `html_images_seen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `html_images_seen` (
  `hash` char(40) NOT NULL,
  PRIMARY KEY (`hash`)
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
-- Table structure for table `league`
--

DROP TABLE IF EXISTS `league`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `league` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `league_matches`
--

DROP TABLE IF EXISTS `league_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `league_matches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `league_id` int(10) unsigned NOT NULL,
  `local` int(10) unsigned NOT NULL,
  `visitor` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `vote_starts` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `votes_local` int(20) DEFAULT '0',
  `votes_visitor` int(20) DEFAULT '0',
  `votes_tied` int(20) DEFAULT '0',
  `score_local` int(2) DEFAULT NULL,
  `score_visitor` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `league_id` (`league_id`),
  KEY `league_id_2` (`league_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `league_teams`
--

DROP TABLE IF EXISTS `league_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `league_teams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `shortname` char(5) DEFAULT NULL,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `league_terms`
--

DROP TABLE IF EXISTS `league_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `league_terms` (
  `user_id` int(20) NOT NULL,
  `vendor` enum('nivea') NOT NULL DEFAULT 'nivea',
  PRIMARY KEY (`user_id`,`vendor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `league_votes`
--

DROP TABLE IF EXISTS `league_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `league_votes` (
  `match_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `value` int(10) unsigned NOT NULL,
  `ip` decimal(39,0) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `match_id` (`match_id`,`user_id`),
  KEY `sort_index` (`match_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `link_clicks`
--

DROP TABLE IF EXISTS `link_clicks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `link_clicks` (
  `id` int(10) unsigned NOT NULL,
  `counter` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `link_commons`
--

DROP TABLE IF EXISTS `link_commons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `link_commons` (
  `link` int(10) unsigned NOT NULL,
  `value` float NOT NULL,
  `n` int(11) NOT NULL DEFAULT '0',
  `date` timestamp NULL DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `link` (`link`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `link_ip_int` decimal(39,0) NOT NULL,
  `link_ip` varbinary(42) DEFAULT NULL,
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
  KEY `link_date` (`link_date`)
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
  `log_sub` int(11) DEFAULT '1',
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_type` enum('link_new','comment_new','link_publish','link_discard','comment_edit','link_edit','post_new','post_edit','login_failed','spam_warn','link_geo_edit','user_new','user_delete','link_depublished','user_depublished_vote') NOT NULL,
  `log_ref_id` int(11) unsigned NOT NULL,
  `log_user_id` int(11) NOT NULL,
  `log_ip_int` decimal(39,0) NOT NULL,
  `log_ip` char(42) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_date` (`log_date`),
  KEY `log_type` (`log_type`,`log_ref_id`),
  KEY `log_type_2` (`log_type`,`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reports`
--

DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `report_type` text,
  `report_reason` text,
  `report_user_id` int(11) NOT NULL,
  `report_ref_id` int(11) NOT NULL,
  `report_status` text,
  `report_modified` timestamp NULL,
  `report_revised_by` int(11) NULL,
  `report_ip` char(42) DEFAULT NULL,
  PRIMARY KEY (`report_id`),
  KEY `report_date` (`report_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_logs`
--

DROP TABLE IF EXISTS `admin_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `log_type` text,
  `log_old_value` text,
  `log_new_value` text,
  `log_ref_id` int(11) UNSIGNED NOT NULL,
  `log_user_id` int(11) NOT NULL,
  `log_ip` char(42) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_date` (`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media` (
  `type` char(12) NOT NULL DEFAULT '',
  `id` int(10) unsigned NOT NULL,
  `version` tinyint(3) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `to` int(10) unsigned NOT NULL DEFAULT '0',
  `access` enum('restricted','public','friends','private') NOT NULL DEFAULT 'restricted',
  `mime` char(32) NOT NULL,
  `extension` char(6) NOT NULL DEFAULT 'jpg',
  `size` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dim1` smallint(5) unsigned NOT NULL,
  `dim2` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`type`,`id`,`version`),
  KEY `user` (`user`,`type`,`date`),
  KEY `type` (`type`,`version`,`date`),
  KEY `user_2` (`user`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `user` int(10) unsigned NOT NULL,
  `type` char(12) NOT NULL,
  `counter` int(10) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user`,`type`)
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
  `post_ip_int` decimal(39,0) DEFAULT NULL,
  `post_votes` smallint(4) NOT NULL DEFAULT '0',
  `post_karma` smallint(6) NOT NULL DEFAULT '0',
  `post_content` text COLLATE utf8_spanish_ci NOT NULL,
  `post_is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`post_id`),
  KEY `post_date` (`post_date`),
  KEY `post_user_id` (`post_user_id`,`post_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_posts`
--

DROP TABLE IF EXISTS `admin_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_posts` (
  `admin_post_id` int(11) unsigned NOT NULL,
  `admin_user_id` int(11) unsigned NOT NULL,
  `admin_user_login` char(32) COLLATE utf8_spanish_ci NOT NULL,
  KEY `admin_post` (`admin_post_id`,`admin_user_id`)
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
  `pref_value` int(8) unsigned NOT NULL DEFAULT '0',
  KEY `pref_user_id` (`pref_user_id`,`pref_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `privates`
--

DROP TABLE IF EXISTS `privates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `privates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `randkey` int(11) NOT NULL DEFAULT '0',
  `user` int(10) unsigned NOT NULL,
  `to` int(10) unsigned NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` char(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`,`date`),
  KEY `to_2` (`to`,`read`),
  KEY `to` (`to`,`date`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rss`
--

DROP TABLE IF EXISTS `rss`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rss` (
  `blog_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `link_id` int(10) unsigned DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_parsed` timestamp NULL DEFAULT NULL,
  `url` char(250) NOT NULL,
  `title` char(250) NOT NULL,
  UNIQUE KEY `url` (`url`),
  KEY `date` (`date`),
  KEY `blog_id` (`blog_id`,`date`),
  KEY `user_id` (`user_id`,`date`)
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
-- Table structure for table `sub_categories`
--

DROP TABLE IF EXISTS `sub_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sub_categories` (
  `id` smallint(5) unsigned NOT NULL,
  `category` smallint(5) unsigned NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `import` tinyint(1) NOT NULL DEFAULT '1',
  `export` tinyint(1) NOT NULL DEFAULT '0',
  `calculated_coef` float NOT NULL DEFAULT '0',
  UNIQUE KEY `category_id` (`category`,`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Store categories available for each sub site';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sub_statuses`
--

DROP TABLE IF EXISTS `sub_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sub_statuses` (
  `id` int(11) unsigned NOT NULL,
  `status` enum('discard','queued','published','abuse','duplicated','autodiscard','metapublished') NOT NULL DEFAULT 'discard',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `link` int(10) NOT NULL,
  `origen` int(11) NOT NULL,
  `karma` decimal(10,2) NOT NULL DEFAULT '0.00',
  UNIQUE KEY `link_id` (`link`,`id`),
  KEY `date_status_id` (`date`,`status`,`id`),
  KEY `id_status_date` (`id`,`status`,`date`),
  CONSTRAINT `sub_statuses_ibfk_1` FOREIGN KEY (`link`) REFERENCES `links` (`link_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Store the status for each link in every sub site';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subs`
--

DROP TABLE IF EXISTS `subs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(12) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `parent` smallint(5) unsigned NOT NULL DEFAULT '0',
  `server_name` varchar(32) DEFAULT NULL,
  `base_url` varchar(32) DEFAULT NULL,
  `name_long` char(40) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `sub` tinyint(1) DEFAULT '0',
  `meta` tinyint(1) DEFAULT '0',
  `owner` int(11) NOT NULL DEFAULT '0',
  `nsfw` tinyint(1) DEFAULT '0',
  `created_from` int(11) NOT NULL DEFAULT '0',
  `allow_main_link` tinyint(1) DEFAULT '1',
  `color1` char(7) DEFAULT NULL,
  `color2` char(7) DEFAULT NULL,
  `private` tinyint(1) DEFAULT '0',
  `page_mode` enum('best-comments','threads','interview','answered','standard') DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Basic data for every sub site';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subs_copy`
--

DROP TABLE IF EXISTS `subs_copy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subs_copy` (
  `src` int(11) NOT NULL,
  `dst` int(11) NOT NULL,
  UNIQUE KEY `uni` (`src`,`dst`),
  KEY `dst_i` (`dst`),
  CONSTRAINT `subs_copy_ibfk_1` FOREIGN KEY (`src`) REFERENCES `subs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subs_copy_ibfk_2` FOREIGN KEY (`dst`) REFERENCES `subs` (`id`) ON DELETE CASCADE
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
-- Table structure for table `texts`
--

DROP TABLE IF EXISTS `texts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `texts` (
  `key` char(32) NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`key`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
  `user_ip` char(42) COLLATE utf8_spanish_ci DEFAULT NULL,
  `user_pass` char(128) COLLATE utf8_spanish_ci NOT NULL,
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
  KEY `user_email_register` (`user_email_register`),
  KEY `user_url` (`user_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_similarities`
--

DROP TABLE IF EXISTS `users_similarities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_similarities` (
  `minor` int(10) unsigned NOT NULL,
  `major` int(10) unsigned NOT NULL,
  `value` float NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `minor` (`minor`,`major`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `votes` (
  `vote_id` int(20) NOT NULL AUTO_INCREMENT,
  `vote_type` enum('links','comments','posts','polls','users','sites','ads') COLLATE utf8_spanish_ci NOT NULL DEFAULT 'links',
  `vote_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `vote_link_id` int(20) NOT NULL DEFAULT '0',
  `vote_user_id` int(20) NOT NULL DEFAULT '0',
  `vote_value` smallint(11) NOT NULL DEFAULT '1',
  `vote_ip_int` decimal(39,0) NOT NULL,
  PRIMARY KEY (`vote_id`),
  UNIQUE KEY `vote_type` (`vote_type`,`vote_link_id`,`vote_user_id`,`vote_ip_int`),
  KEY `vote_type_4` (`vote_type`,`vote_date`,`vote_user_id`),
  KEY `vote_ip_int` (`vote_ip_int`),
  KEY `vote_type_2` (`vote_type`,`vote_user_id`,`vote_date`)
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

-- Dump completed on 2015-06-12 10:22:15
