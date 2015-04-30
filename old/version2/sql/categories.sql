-- MySQL dump 10.11
--
-- Host: localhost    Database: meneame
-- ------------------------------------------------------
-- Server version	5.0.41-Debian_1~bpo.1-log

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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category__auto_id` int(11) NOT NULL auto_increment,
  `category_lang` char(4) collate utf8_spanish_ci NOT NULL default 'es',
  `category_id` int(11) NOT NULL default '0',
  `category_parent` int(11) NOT NULL default '0',
  `category_name` char(32) collate utf8_spanish_ci NOT NULL,
  `category_uri` char(32) collate utf8_spanish_ci default NULL,
  `category_calculated_coef` float NOT NULL default '0',
  PRIMARY KEY  (`category__auto_id`),
  UNIQUE KEY `category_lang` (`category_lang`,`category_id`),
  UNIQUE KEY `id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=66 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'es',1,100,'software libre',NULL,1.12088),(4,'es',4,100,'internet','internet',1.12088),(6,'es',6,101,'blogs',NULL,1.12318),(38,'es',38,102,'sociedad','sociedad',0.823064),(14,'es',8,100,'hardware',NULL,1.12088),(16,'es',22,100,'ciencia',NULL,1.12088),(19,'es',13,100,'diseño',NULL,1.12088),(22,'es',11,100,'software',NULL,1.12088),(27,'es',19,100,'cacharros',NULL,1.12088),(29,'es',23,100,'juegos',NULL,1.12088),(33,'es',32,101,'friqui',NULL,1.12318),(57,'es',28,101,'podcast','podcast',1.12318),(35,'es',35,101,'curiosidades',NULL,1.12318),(36,'es',36,101,'derechos',NULL,1.12318),(37,'es',37,100,'seguridad',NULL,1.12088),(39,'es',5,101,'cine/TV',NULL,1.12318),(40,'es',100,0,'tecnología','tecnologia',1.12088),(41,'es',101,0,'cultura','cultura',1.12318),(42,'es',102,0,'actualidad','actualidad',0.823064),(43,'es',7,100,'empresas',NULL,1.12088),(44,'es',9,101,'música',NULL,1.12318),(45,'es',10,101,'vídeos','videos',1.12318),(46,'es',12,101,'espectáculos','espectaculos',1.12318),(47,'es',15,101,'historia','historia',1.12318),(48,'es',16,101,'literatura','literatura',1.12318),(49,'es',17,102,'américas','americas',0.823064),(50,'es',18,102,'europa','europa',0.823064),(51,'es',20,102,'internacional','internacional',0.823064),(53,'es',24,102,'política','politica',0.823064),(54,'es',25,102,'economía','economía',0.823064),(55,'es',26,102,'última hora','ultima-hora',0.823064),(56,'es',27,102,'deportes','deportes',0.823064),(58,'es',29,101,'educación','educación',1.12318),(59,'es',39,100,'medicina','medicina',1.12088),(60,'es',40,100,'energía','energia',1.12088),(61,'es',41,101,'arte','arte',1.12318),(62,'es',42,100,'novedades','novedades-tec',1.12088),(63,'es',43,100,'medio ambiente','medio-ambiente',1.12088),(64,'es',44,102,'personalidades','personalidades',0.823064),(65,'es',45,102,'prensa','prensa',0.823064);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-08-31 16:56:19
