<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005-2011 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class LogAdmin
{
    public static function insert($type, $ref_id, $user_id = 0, $old_value, $new_value)
    {
        global $db, $globals;

        return $db->query('
            INSERT INTO `admin_logs`
            SET
                `log_date` = NOW(),
                `log_type` = "'.$type.'",
                `log_ref_id` = "'.(int)$ref_id.'",
                `log_user_id` = "'.(int)$user_id.'",
                `log_old_value` = "'.$old_value.'",
                `log_new_value` = "'.$new_value.'",
                `log_ip` = "'.$globals['user_ip'].'";
        ');
    }
}
