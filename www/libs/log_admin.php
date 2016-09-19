<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005-2011 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class LogAdmin
{
	static function insert($type, $ref_id, $user_id = 0, $old_value, $new_value)
	{
		global $db, $globals;

		$ip = $globals['user_ip'];
		$res = $db->query("insert into admin_logs (log_date, log_type, log_ref_id, log_user_id, log_old_value, log_new_value, log_ip) values (now(), '$type', $ref_id, $user_id, '$old_value', '$new_value', '$ip')");
		return $res;
	}
}