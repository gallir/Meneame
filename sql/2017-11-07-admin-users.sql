SET FOREIGN_KEY_CHECKS=0;

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;

DROP TABLE IF EXISTS `admin_sections`;

CREATE TABLE `admin_sections` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DROP TABLE IF EXISTS `admin_users`;

CREATE TABLE `admin_users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_id` int(11) NOT NULL,
  `section_id` int(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_admin_users_admin_id` FOREIGN KEY (`admin_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_admin_users_section_id` FOREIGN KEY (`section_id`) REFERENCES `admin_sections`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

SET FOREIGN_KEY_CHECKS=1;

DROP PROCEDURE IF EXISTS insertUsersAdmin;

DELIMITER ;;
CREATE PROCEDURE insertUsersAdmin()
BEGIN
  DECLARE sections varchar(150) DEFAULT 'admin_users,admin_logs,comment_reports,strikes,hostname,punished_hostname,email,ip,words,noaccess,preguntame,sponsors,mafia';
  DECLARE section_id int(11);

  WHILE sections != '' DO
    INSERT INTO `admin_sections` SET `name` = SUBSTRING_INDEX(sections, ',', 1);

    INSERT INTO `admin_users` (`section_id`, `admin_id`) (
      SELECT LAST_INSERT_ID(), `user_id`
      FROM `users`
      WHERE `user_level` IN ('admin', 'god')
    );

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

DROP PROCEDURE insertUsersAdmin;

DELETE FROM `admin_users` WHERE `section_id` IN (
  SELECT `id` FROM `admin_sections` WHERE `name` IN ('admin_users', 'preguntame', 'sponsors')
);
