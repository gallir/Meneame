<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Strike extends LCPBase
{

	const STRIKE_TYPE_1 = 'strike1';
	const STRIKE_TYPE_2 = 'strike2';
	const STRIKE_TYPE_BAN = 'ban';

	const STRIKE_REASON_VIOLATES_RULES = 'violate_rules';
	const STRIKE_REASON_INAPPROPRIATE_CONTENT = 'inappropriate_content';
	const STRIKE_REASON_SPAM = 'spam';
	const STRIKE_REASON_INSULT_THREAT = 'insult';
	const STRIKE_REASON_INCITES_HATRED = 'incites_hatred';
	const STRIKE_REASON_ADVERTISING = 'advertising';
	const STRIKE_REASON_VIOLENCE_OR_PORN = 'violence_porn';
	const STRIKE_REASON_REVEILS_PRIVATE_DATA = 'private_data';
	const STRIKE_REASON_BREACH_LEGALITY = 'legality';

	const SQL_SIMPLE = " strike_id as id, strike_type as type, strike_date as date, strike_reason as reason, strike_admin_id as admin_id, strike_user_id as user_id, strike_report_id as report_id, strike_old_karma as old_karma, strike_new_karma as new_karma, strike_comment as comment, strike_ip as ip FROM strikes";

	const SQL = " strike_id as id, strike_type as type, strike_date as date, strike_reason as reason, strike_comment as comment, admin.user_id as admin_id, admin.user_login as admin_login, users.user_id as user_id, users.user_login as user_login, users.user_karma as actual_karma, strike_old_karma as old_karma, strike_new_karma as new_karma FROM strikes
	LEFT JOIN users as admin on (admin.user_id = strike_admin_id)
	LEFT JOIN users on (users.user_id = strike_user_id)
	LEFT JOIN reports on (reports.report_id = strike_report_id)";

	// sql fields to build an object from mysql
	public $id;
	public $type;
	public $date;
	public $reason;
	public $admin_id;
	public $user_id;
	public $report_id;
	public $old_karma;
	public $new_karma;
	public $comment;
	public $ip;

	public function __construct($strike_type = Strike::STRIKE_TYPE_1)
	{
		$this->id = 0;
		$this->type = $strike_type;
		$this->report_id = 0;
	}

	static function from_db($id)
	{
		global $db;

		$selector = "strike_id = $id";

		$sql = "SELECT" . Strike::SQL_SIMPLE . "WHERE $selector";

		return $db->get_object($sql, 'Strike');
	}

	static function is_valid_reason($reason)
	{
		return in_array($reason, array(
			self::STRIKE_REASON_INAPPROPRIATE_CONTENT,
			self::STRIKE_REASON_SPAM,
			self::STRIKE_REASON_VIOLATES_RULES,
			self::STRIKE_REASON_INSULT_THREAT,
			self::STRIKE_REASON_INCITES_HATRED,
			self::STRIKE_REASON_ADVERTISING,
			self::STRIKE_REASON_VIOLENCE_OR_PORN,
			self::STRIKE_REASON_REVEILS_PRIVATE_DATA,
			self::STRIKE_REASON_BREACH_LEGALITY
		));
	}

	static function calculate_new_karma($old_karma, $strike_type)
	{

		global $globals;

		switch ($strike_type) {
			case Strike::STRIKE_TYPE_1:
				if (($old_karma - $globals['karma_max_descent_strike_1']) < $globals['karma_strike_1']) {
					$new_karma = $globals['karma_strike_1'];
				} else {
					$new_karma = $old_karma - $globals['karma_max_descent_strike_1'];
				}
				break;

			case Strike::STRIKE_TYPE_2:
				if (($old_karma - $globals['karma_max_descent_strike_2']) < $globals['karma_strike_2']) {
					$new_karma = $globals['karma_strike_2'];
				} else {
					$new_karma = $old_karma - $globals['karma_max_descent_strike_2'];
				}
				break;
			case Strike::STRIKE_TYPE_BAN:
				if (($old_karma - $globals['karma_max_descent_ban']) < $globals['karma_ban']) {
					$new_karma = $globals['karma_ban'];
				} else {
					$new_karma = $old_karma - $globals['karma_ban'];
				}
				break;
		}

		return $new_karma;
	}

	function store()
	{
		global $db, $globals;

		if (!$this->date) $this->date = $globals['now'];
		$strike_ip = $this->ip = $globals['user_ip'];
		$strike_comment = $this->comment;
		if ($this->id === 0) {
			$r = $db->query("INSERT INTO strikes (strike_date, strike_type, strike_reason, strike_user_id, strike_report_id, strike_admin_id, strike_old_karma, strike_new_karma, strike_comment, strike_ip) VALUES(FROM_UNIXTIME({$this->date}), '{$this->type}', '{$this->reason}',{$this->user_id}, {$this->report_id}, {$this->admin_id}, {$this->old_karma}, {$this->new_karma}, '$strike_comment', '$strike_ip')");
			$this->id = $db->insert_id;
		} else {
			$r = $db->query("UPDATE strikes set strike_date=FROM_UNIXTIME({$this->date}), strike_type='{$this->type}', strike_reason='{$this->reason}', strike_user_id={$this->user_id}, strike_report_id={$this->report_id}, strike_admin_id={$this->admin_id}, strike_old_karma={$this->old_karma}, strike_new_karma={$this->new_karma}, strike_comment='$strike_comment', strike_ip='$strike_ip'");
		}

		if (!$r) {
			$db->rollback();
			return false;
		}

		$db->commit();
		return true;
	}

	static function is_valid_strike_type_for_user($user_id, $strike_type)
	{

		global $db, $globals;

		$user_id = intval($user_id);

		if (!Strike::is_valid_strike_type($strike_type)) return false;

		$strikes = $db->get_results("SELECT strike_type, count(*) AS num_strikes FROM strikes where strike_user_id=$user_id GROUP BY strike_type ORDER BY strike_date DESC");

		if (!$strikes) {
			return true;
		}

		$used_strikes = array();
		foreach ($strikes as $strike) {
			$used_strikes[] = $strike->type;
		}

		if (in_array($strike_type, $used_strikes)) return false;

		return true;

	}

	static function is_valid_strike_type($strike_type)
	{
		return in_array($strike_type, array(
			self::STRIKE_TYPE_1,
			self::STRIKE_TYPE_2,
			self::STRIKE_TYPE_BAN
		));
	}

	static function get_applied_strikes_to_user($user_id) {
		global $db, $globals;

		$user_id = intval($user_id);

		$strikes = $db->get_results("SELECT strike_type as type, count(*) AS num_strikes FROM strikes where strike_user_id=$user_id GROUP BY strike_type ORDER BY strike_date DESC");

		$applied_strikes = array();
		foreach ($strikes as $strike) {
			array_push($applied_strikes, $strike->type);
		}

		return $applied_strikes;
	}

}