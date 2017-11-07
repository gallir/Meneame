SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `users_admin`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `users_admin` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `section` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_users_admin_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

SET FOREIGN_KEY_CHECKS=1;

DROP PROCEDURE IF EXISTS insertUsersAdmin;

DELIMITER ;;
CREATE PROCEDURE insertUsersAdmin()
BEGIN
  DECLARE sections varchar(150) DEFAULT 'admin_logs,comment_reports,strikes,hostname,punished_hostname,email,ip,words,noaccess,preguntame,sponsors,mafia';
  DECLARE section varchar(150);

  WHILE sections != '' DO
    SET section = SUBSTRING_INDEX(sections, ',', 1);

    INSERT INTO `users_admin` (`section`, `admin_id`) (SELECT section, `user_id` FROM `users` WHERE `user_level` IN ('admin', 'god'));

    IF LOCATE(',', sections) > 0 THEN
      SET sections = SUBSTRING(sections, LOCATE(',', sections) + 1);
    ELSE
      SET sections = '';
    END IF;
  END WHILE;
END;
;;

DELIMITER ;

CALL insertUsersAdmin();
