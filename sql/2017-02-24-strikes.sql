SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `strikes`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `strikes` (
  `strike_id` int(11) NOT NULL AUTO_INCREMENT,
  `strike_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `strike_type` varchar(50),
  `strike_reason` varchar(50),
  `strike_user_id` int(11) NOT NULL,
  `strike_report_id` int(11) DEFAULT 0,
  `strike_admin_id` int(11) NOT NULL,
  `strike_karma_old` decimal(4,2) UNSIGNED NOT NULL,
  `strike_karma_new` decimal(4,2) UNSIGNED NOT NULL,
  `strike_karma_restore` decimal(4,2) UNSIGNED NOT NULL,
  `strike_hours` tinyint(3) NOT NULL,
  `strike_expires_at` datetime NOT NULL,
  `strike_comment` text,
  `strike_ip` char(42) DEFAULT NULL,
  `strike_restored` boolean NOT NULL DEFAULT 0,
  PRIMARY KEY (`strike_id`),
  KEY `strike_date` (`strike_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

SET FOREIGN_KEY_CHECKS=1;
