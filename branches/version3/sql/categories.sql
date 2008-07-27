-- MySQL dump 10.11
--
-- Host: localhost    Database: meneame
-- ------------------------------------------------------
-- Server version	5.0.51a-3-log

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
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
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
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
SET character_set_client = @saved_cs_client;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'es',1,100,'software libre',NULL,1.04252),(4,'es',4,100,'internet','internet',1.04252),(6,'es',6,103,'blogs',NULL,0.787306),(38,'es',38,102,'sociedad','sociedad',1.2186),(14,'es',8,100,'hardware',NULL,1.04252),(16,'es',22,101,'ciencia',NULL,1.06563),(19,'es',13,100,'diseño',NULL,1.04252),(22,'es',11,100,'software',NULL,1.04252),(70,'es',64,102,'sucesos','sucesos',1.2186),(29,'es',23,100,'juegos',NULL,1.04252),(33,'es',32,103,'friqui',NULL,0.787306),(57,'es',28,103,'podcast','podcast',0.787306),(35,'es',35,103,'curiosidades',NULL,0.787306),(36,'es',36,101,'derechos',NULL,1.06563),(37,'es',37,100,'seguridad',NULL,1.04252),(39,'es',5,103,'TV','tv',0.787306),(40,'es',100,0,'tecnología','tecnologia',1.04252),(41,'es',101,0,'cultura','cultura',1.06563),(42,'es',102,0,'actualidad','actualidad',1.2186),(43,'es',7,102,'empresas',NULL,1.2186),(44,'es',9,101,'música',NULL,1.06563),(45,'es',10,103,'vídeos','videos',0.787306),(46,'es',12,103,'espectáculos','espectaculos',0.787306),(47,'es',15,101,'historia','historia',1.06563),(48,'es',16,101,'literatura','literatura',1.06563),(49,'es',17,102,'américas','americas',1.2186),(50,'es',18,102,'europa','europa',1.2186),(51,'es',20,102,'internacional','internacional',1.2186),(53,'es',24,102,'política','politica',1.2186),(54,'es',25,102,'economía','economía',1.2186),(56,'es',27,103,'deportes','deportes',0.787306),(58,'es',29,101,'educación','educación',1.06563),(59,'es',39,100,'medicina','medicina',1.04252),(60,'es',40,100,'energía','energia',1.04252),(61,'es',41,101,'arte','arte',1.06563),(62,'es',42,100,'novedades','novedades-tec',1.04252),(63,'es',43,100,'medioambiente','medioambiente',1.04252),(64,'es',44,102,'personalidades','personalidades',1.2186),(65,'es',45,101,'prensa','prensa',1.06563),(66,'es',103,0,'ocio','ocio',0.787306),(67,'es',60,101,'fotografía','fotografia',1.06563),(68,'es',61,101,'divulgación','divulgacion',1.06563),(69,'es',62,101,'cine','cine',1.06563);
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

-- Dump completed on 2008-07-27 18:21:13
