DROP TABLE IF EXISTS `preguntame`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `preguntame` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(120),
  `subtitle` varchar(120),
  `link` varchar(250),
  `start_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enabled` boolean NOT NULL DEFAULT 0,
  `admin_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*!40101 SET character_set_client = @saved_cs_client */;
