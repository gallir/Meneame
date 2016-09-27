<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Report extends LCPBase
{

	const REPORT_TYPE_LINK_COMMENT = 'link_comment';

	const REPORT_STATUS_PENDING = 'pending';
	const REPORT_STATUS_DEBATE = 'debate';
	const REPORT_STATUS_PENALIZED = 'penalized';
	const REPORT_STATUS_DISMISSED = 'dismissed';

	const REPORT_REASON_VIOLATES_RULES = 'violate_rules';
	const REPORT_REASON_INAPPROPRIATE_CONTENT = 'inappropriate_content';
	const REPORT_REASON_SPAM = 'spam';

	const SQL_COMMENT = " report_id as id, report_type as type, report_date as date, report_modified as modified, report_status as status, report_reason as reason, reporters.user_id as reporter_id, reporters.user_level as reporter_user_level, reporters.user_login as reporter_user_login, authors.user_id as author_id, authors.user_level as author_user_level, authors.user_login as author_user_login, revisors.user_id as revisor_id, revisors.user_level as revisor_user_level, revisors.user_login as revisor_user_login, report_ip as ip, comment_id as ref_id, comment_order, comment_link_id, link_uri as comment_link_uri FROM reports
	LEFT JOIN users as reporters on (reporters.user_id = report_user_id)
	LEFT JOIN comments on (comments.comment_id = reports.report_ref_id)
	LEFT JOIN links on (comments.comment_link_id = links.link_id)
	LEFT JOIN users as authors on (authors.user_id = comments.comment_user_id)
	LEFT JOIN users as revisors on (revisors.user_id = reports.report_revised_by) ";

	const SQL_COMMENT_GROUPED = " count(*) as report_num, report_id as id, report_type as type, report_date as date, report_modified as modified, report_status as status, report_reason as reason, reporters.user_id as reporter_id, reporters.user_level as reporter_user_level, reporters.user_login as reporter_user_login, authors.user_id as author_id, authors.user_level as author_user_level, authors.user_login as author_user_login, revisors.user_id as revisor_id, revisors.user_level as revisor_user_level, revisors.user_login as revisor_user_login, report_ip as ip, comment_id as ref_id, comment_order, comment_link_id, link_uri as comment_link_uri FROM reports
	LEFT JOIN users as reporters on (reporters.user_id = report_user_id)
	LEFT JOIN comments on (comments.comment_id = reports.report_ref_id)
	LEFT JOIN links on (comments.comment_link_id = links.link_id)
	LEFT JOIN users as authors on (authors.user_id = comments.comment_user_id)
	LEFT JOIN users as revisors on (revisors.user_id = reports.report_revised_by) ";

	// sql fields to build an object from mysql
	public $id = 0;
	public $type = Report::REPORT_TYPE_LINK_COMMENT;
	public $date;
	public $status = Report::REPORT_STATUS_PENDING;
	public $reason = '';
	public $reporter_id;
	public $author_id;
	public $ref_id;
	public $revised_by;
	public $modified = null;
	public $ip;

	static function from_db($id, $report_type = Report::REPORT_TYPE_LINK_COMMENT)
	{
		global $db;

		$selector = "report_id = $id and report_type = '$report_type'";

		if ($report_type == Report::REPORT_TYPE_LINK_COMMENT) {
			$sql = "SELECT" . Report::SQL_COMMENT . "WHERE $selector";
		}

		return $db->get_object($sql, 'Report');
	}

	static function is_valid_reason($reason)
	{
		return in_array($reason, array(
			self::REPORT_REASON_INAPPROPRIATE_CONTENT,
			self::REPORT_REASON_SPAM,
			self::REPORT_REASON_VIOLATES_RULES
		));
	}

	static function check_min_karma()
	{
		global $current_user, $globals;

		return ($globals['min_karma_for_report_comments'] > $current_user->karma);

	}

	static function already_reported($report_ref_id)
	{
		global $db, $current_user;

		$sql = "select count(*) from reports where report_ref_id=$report_ref_id and report_user_id={$current_user->user_id}";
		$already_reported = boolval($db->get_var($sql));

		return $already_reported;
	}

	static function check_report_user_limit()
	{
		global $db, $current_user, $globals;

		$sql = "select count(*) from reports where report_user_id={$current_user->user_id} and (NOW() - report_date) < 86400";
		$number_reports_24h = $db->get_var($sql);

		return $number_reports_24h < $globals['max_reports_for_comments'];
	}

	static function get_total_in_status($status, $type = self::REPORT_TYPE_LINK_COMMENT)
	{
		global $db;

		$sql = "select count(*) from reports where report_status='$status' and report_type='$type'";
		return $db->get_var($sql);
	}

	public function store()
	{
		global $db, $globals;

		if (!$this->date) $this->date = $globals['now'];
		$report_date = $this->date;
		$report_type = $this->type;
		$report_reason = $this->reason;
		$report_user_id = $this->reporter_id;
		$report_ref_id = $this->ref_id;
		$report_status = $this->status;
		$report_modified = $this->modified;
		$report_revised_by = $this->revised_by;
		$report_ip = $this->ip = $globals['user_ip'];

		if ($this->id === 0) {
			$r = $db->query("INSERT INTO reports (report_date, report_type, report_reason, report_user_id, report_ref_id, report_status, report_modified, report_revised_by, report_ip) VALUES(FROM_UNIXTIME($report_date), '$report_type', '$report_reason', $report_user_id, $report_ref_id, '$report_status', null, null, '$report_ip')");
			$this->id = $db->insert_id;
		} else {
			$r = $db->query("UPDATE reports set report_date=FROM_UNIXTIME($report_date), report_type='$report_type', report_reason='$report_reason', report_user_id=$report_user_id, report_ref_id=$report_ref_id, report_status='$report_status', report_modified=FROM_UNIXTIME($report_modified), report_revised_by=$report_revised_by, report_ip='$report_ip'");
		}

		if (!$r) {
			$db->rollback();
			return false;
		}

		$db->commit();
		return true;
	}
}