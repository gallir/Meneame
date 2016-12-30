SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `polls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `polls` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `question` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `votes` smallint(7) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_at` datetime NOT NULL,
  `link_id` int(20) NULL,
  `post_id` int(11) UNSIGNED NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_polls_link_id` FOREIGN KEY (`link_id`) REFERENCES `links`(`link_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_polls_post_id` FOREIGN KEY (`post_id`) REFERENCES `posts`(`post_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

DROP TABLE IF EXISTS `polls_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `polls_options` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `option` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `votes` smallint(7) UNSIGNED NOT NULL DEFAULT '0',
  `karma` smallint(7) UNSIGNED NOT NULL DEFAULT '0',
  `poll_id` int(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_polls_options_poll_id` FOREIGN KEY (`poll_id`) REFERENCES `polls`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

SET FOREIGN_KEY_CHECKS=1;