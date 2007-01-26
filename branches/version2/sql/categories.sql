-- MySQL dump 10.10
--
-- Host: localhost    Database: meneame
-- ------------------------------------------------------
-- Server version	5.0.30-Debian_1-log

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
  PRIMARY KEY  (`category__auto_id`),
  UNIQUE KEY `category_lang` (`category_lang`,`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'es',1,100,'software libre',NULL),(4,'es',4,100,'internet','internet'),(6,'es',6,101,'blogs',NULL),(38,'es',38,102,'sociedad','sociedad'),(14,'es',8,100,'hardware',NULL),(16,'es',22,100,'ciencia',NULL),(19,'es',13,100,'diseño',NULL),(22,'es',11,100,'software',NULL),(27,'es',19,100,'cacharros',NULL),(29,'es',23,100,'juegos',NULL),(33,'es',32,101,'friqui',NULL),(34,'es',33,100,'negocios',NULL),(35,'es',35,101,'curiosidades',NULL),(36,'es',36,101,'derechos',NULL),(37,'es',37,100,'seguridad',NULL),(39,'es',5,101,'cine/TV',NULL),(40,'es',100,0,'tecnología','tecnologia'),(41,'es',101,0,'cultura','cultura'),(42,'es',102,0,'actualidad','actualidad'),(43,'es',7,100,'empresas',NULL),(44,'es',9,101,'música',NULL),(45,'es',10,101,'vídeos','videos'),(46,'es',12,101,'espectáculos','espectaculos'),(47,'es',15,101,'historia','historia'),(48,'es',16,101,'literatura','literatura'),(49,'es',17,102,'américas','americas'),(50,'es',18,102,'europa','europa'),(51,'es',20,102,'internacional','internacional'),(53,'es',24,102,'política','politica'),(54,'es',25,102,'economía','economía'),(55,'es',26,102,'urgente','urgente'),(56,'es',27,102,'deportes','deportes');
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

-- Dump completed on 2007-01-26 11:46:05
