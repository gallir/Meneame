<?php
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class AdminUser
{
    public static function allowed($admin_id, $section)
    {
        global $db;

        return (bool)$db->get_var('
            SELECT COUNT(*)
            FROM `admin_users`, `admin_sections`
            WHERE (
                `admin_users`.`admin_id` = "'.(int)$admin_id.'"
                AND `admin_sections`.`name` = "'.$db->escape($section).'"
                AND `admin_users`.`section_id` = `admin_sections`.`id`
            )
            LIMIT 1;
        ');
    }

    public static function sectionsByAdminId($admin_id)
    {
        global $db;

        return $db->get_col('
            SELECT `admin_sections`.`name`
            FROM `admin_sections`, `admin_users`
            WHERE (
                `admin_users`.`admin_id` = "'.(int)$admin_id.'"
                AND `admin_sections`.`id` = `admin_users`.`section_id`
            );
        ');
    }

    public static function sections()
    {
        global $db;

        return $db->get_col('
            SELECT `name`
            FROM `admin_sections`
            ORDER BY `name` ASC;
        ');
    }

    public static function listing()
    {
        global $db;

        $list = $db->get_results('
            SELECT `users`.`user_id`, `users`.`user_login`, `users`.`user_level`,
                GROUP_CONCAT(`admin_sections`.`name` ORDER BY `admin_sections`.`name`) AS `sections`
            FROM `users`, `admin_users`
            JOIN `admin_sections` ON (`admin_sections`.`id` = `admin_users`.`section_id`)
            WHERE `users`.`user_id` = `admin_users`.`admin_id`
            GROUP BY `users`.`user_login`
            ORDER BY `users`.`user_login` ASC;
        ');

        foreach ($list as $row) {
            $row->sections = explode(',', $row->sections);
        }

        return $list;
    }
}
