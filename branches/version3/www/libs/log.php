<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function log_insert($type, $ref_id, $user_id=0) {
	global $db, $globals;

	if ($globals['behind_load_balancer'] && $globals['form_user_ip']) {
		// If the page stored the "real IP" in a form
		$ip = $globals['form_user_ip']; 
	} else {
		$ip = $globals['user_ip'];
	}
	return $db->query("insert into logs (log_date, log_type, log_ref_id, log_user_id, log_ip) values (now(), '$type', $ref_id, $user_id, '$ip')");
}

function log_conditional_insert($type, $ref_id, $user_id=0, $seconds=0) {
	global $db, $globals;

	if (!log_get_date($type, $ref_id, $user_id, $seconds)) {
		return log_insert($type, $ref_id, $user_id);
	}
	return false;
}

function log_get_date($type, $ref_id, $user_id=0, $seconds=0) {
	global $db, $globals;

	if ($seconds > 0) {
		$interval = "and log_date > date_sub(now(), interval $seconds second)";
	}
	return (int) $db->get_var("select count(*) from logs where log_type='$type' and log_ref_id = $ref_id $interval and log_user_id = $user_id order by log_date desc limit 1");
}
?>
