SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `strikes`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `strikes` (
  `strike_id` int(11) NOT NULL AUTO_INCREMENT,
  `strike_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `strike_type` text,
  `strike_reason` text,
  `strike_user_id` int(11) NOT NULL,
  `strike_report_id` int(11) DEFAULT 0,
  `strike_admin_id` int(11) NOT NULL,
  `strike_old_karma` decimal(10,2) NOT NULL,
  `strike_new_karma` decimal(10,2) NOT NULL,
  `strike_comment` text,
  `strike_ip` char(42) DEFAULT NULL,
  PRIMARY KEY (`strike_id`),
  KEY `strike_date` (`strike_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

SET FOREIGN_KEY_CHECKS=1;