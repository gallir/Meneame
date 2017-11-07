<?php
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class UserAdmin
{
    public static function exists($admin_id, $section)
    {
        global $db;

        return (bool)$db->get_var('
            SELECT COUNT(*)
            FROM `users_admin`
            WHERE (
                `admin_id` = "'.(int)$admin_id.'"
                AND `section` = "'.$db->escape($section).'"
            )
            LIMIT 1;
        ');
    }

    public static function sections($admin_id)
    {
        global $db;

        return $db->get_col('
            SELECT `section`
            FROM `users_admin`
            WHERE `admin_id` = "'.(int)$admin_id.'";
        ');
    }
}
