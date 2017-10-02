SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `sponsors`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `sponsors` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `external` varchar(255) NOT NULL DEFAULT '',
  `banner` varchar(255) NOT NULL DEFAULT '',
  `banner_mobile` varchar(255) NOT NULL DEFAULT '',
  `css` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `enabled` boolean NOT NULL DEFAULT 0,
  `link` int(20) NULL,
  `admin_id` int(11) NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_sponsors_link` FOREIGN KEY (`link`) REFERENCES `links`(`link_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sponsors_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

SET FOREIGN_KEY_CHECKS=1;