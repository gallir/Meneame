<?
// The source code packaged with this file is Free Software, Copyright (C) 2005-2011 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Log {
	static function insert($type, $ref_id, $user_id=0, $annotation = false) {
		global $db, $globals;

		if ($globals['behind_load_balancer'] && $globals['form_user_ip']) {
			// If the page stored the "real IP" in a form
			$ip = $globals['form_user_ip'];
		} else {
			$ip = $globals['user_ip'];
		}
		$sub = SitesMgr::my_parent(); // Get this subsite's parent id (or itself if it's a parent)
		$res = $db->query("insert into logs (log_sub, log_date, log_type, log_ref_id, log_user_id, log_ip) values ($sub, now(), '$type', $ref_id, $user_id, '$ip')");
		if ($res && $annotation) {
			$a = new Annotation('log-'.$db->insert_id);
			$a->text = $annotation;
			$a->store(time()+86400*30); // Valid for one month
		}
		return $res;
	}

	static function conditional_insert($type, $ref_id, $user_id=0, $seconds=0, $annotation = false) {
		global $db, $globals;

		if (!Log::get_date($type, $ref_id, $user_id, $seconds)) {
			return Log::insert($type, $ref_id, $user_id, $annotation);
		}
		return false;
	}

	static function get_date($type, $ref_id, $user_id=0, $seconds=0) {
		global $db, $globals;

		if ($seconds > 0) {
			$interval = "and log_date > date_sub(now(), interval $seconds second)";
		} else $interval = '';
		return (int) $db->get_var("select count(*) from logs where log_type='$type' and log_ref_id = $ref_id $interval and log_user_id = $user_id order by log_date desc limit 1");
	}

	static function has_annotation($id) {
		global $db;
		return $db->get_var("select count(*) from annotations where annotation_key = 'log-$id'");
	}
}
?>
