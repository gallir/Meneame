-- MySQL dump 10.13  Distrib 5.5.18, for Linux (x86_64)
--
-- Host: localhost    Database: meneame
-- ------------------------------------------------------
-- Server version	5.5.18-55-log

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
-- Table structure for table `subs`
--

DROP TABLE IF EXISTS `subs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subs` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `name` char(12) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `parent` smallint(5) unsigned NOT NULL DEFAULT '0',
  `server_name` varchar(32) DEFAULT NULL,
  `base_url` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Basic data for every sub site';
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
  `id` smallint(5) unsigned NOT NULL,
  `status` enum('discard','queued','published','abuse','duplicated','autodiscard','metapublished') NOT NULL DEFAULT 'discard',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `category` smallint(5) unsigned NOT NULL,
  `link` int(10) NOT NULL,
  `origen` smallint(5) unsigned NOT NULL,
  `karma` decimal(10,2) NOT NULL DEFAULT '0.00',
  UNIQUE KEY `link_id` (`link`,`id`),
  KEY `id_status_category_date` (`id`,`status`,`category`,`date`),
  KEY `id_status_date_category` (`id`,`status`,`date`,`category`),
  CONSTRAINT `sub_statuses_ibfk_1` FOREIGN KEY (`link`) REFERENCES `links` (`link_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Store the status for each link in every sub site';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2011-12-29  1:19:14
