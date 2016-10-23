<?php
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// David Martín :: Suki_ :: <david at sukiweb dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

$globals['skip_check_ip_noaccess'] = true;
include(__DIR__.'/../config.php');
include(mnminclude . 'html1.php');
include(__DIR__.'/libs/admin.php');

$page_size = 40;
$offset = (get_current_page() - 1) * $page_size;

$operation = $_REQUEST["op"] ? $_REQUEST["op"] : 'list';
$search = $_REQUEST["s"];
$orderby = $_REQUEST["order_by"];

$selected_tab = "comment_reports";
if ($_REQUEST["tab"]) {
	$selected_tab = clean_input_string($_REQUEST["tab"]);
}

$report_status = array('pending', 'debate');
if (!empty($_REQUEST["report_status"])) {
	$report_status = $_REQUEST["report_status"];
}

$report_date = 'all';
if (!empty($_REQUEST["report_date"])) {
	$report_date = clean_input_string($_REQUEST["report_date"]);
}

$statistics = calculate_statistics();

$key = get_security_key();

switch ($operation) {
	case 'list':
		do_header(_('Comment reports'));
		do_admin_tabs($selected_tab);
		do_report_list($selected_tab, $search, $report_status, $report_date, $orderby, $key, $statistics);
		break;
	case 'change_status':
		if (!check_security_key($_REQUEST['key'])) die;
		$report = Report::from_db($_REQUEST['report_id']);
		$status = $_REQUEST['new_report_status'];
		update_status($report, $status);
		header("Location: " . $_SERVER['REQUEST_URI']);
		break;
}

do_footer();

function do_report_list($selected_tab, $search, $report_status, $report_date, $orderby, $key, $statistics)
{
	global $db, $offset, $page_size, $globals;

	if (empty($orderby)) {
		$orderby = 'report_num';
		$order = "DESC";
	} else {
		$orderby = preg_replace('/[^a-z_]/i', '', $orderby);
		if ($orderby == 'report_num') {
			$order = "DESC";
		} else {
			$order = "ASC";
		}
	}

	$where = "WHERE report_type='" . Report::REPORT_TYPE_LINK_COMMENT . "'";
	if ($report_status) {
		$where .= " AND report_status IN ('" . join("','", $report_status) . "')";
	}

	if ($report_date) {

		switch ($report_date) {
			case 'two_hours':
				$ts = $globals['now'] - 7200;
				break;
			case 'six_hours':
				$ts = $globals['now'] - 6 * 3600;
				break;
			case 'twelve_hours':
				$ts = $globals['now'] - 12 * 3600;
				break;
			case 'one_day':
				$ts = $globals['now'] - 86400;
				break;
			case 'one_week':
				$ts = $globals['now'] - 7 * 86400;
				break;
		}

		if ($report_date != 'all') {
			$where .= " AND report_date > FROM_UNIXTIME($ts)";
		}
	}

	if ($search) {
		$search_text = $db->escape($search);
		$where .= " AND (report.author_user_login LIKE '%$search_text%')";
	}

	$rows = $db->get_var("SELECT count(*) FROM reports " . $where);

	$sql = "SELECT" . Report::SQL_COMMENT_GROUPED . " $where GROUP BY ref_id, reason ORDER BY $orderby $order LIMIT $offset,$page_size";

	$reports = group_by_comment( $db->get_results($sql));

	Haanga::Load('admin/reports/list.html', compact('reports', 'selected_tab', 'key', 'search', 'report_status', 'report_date', 'statistics'));

	do_pages($rows, $page_size, false);

}

function update_status($report, $status)
{
	global $db, $current_user, $globals;

	$report_modified = $globals['now'];

	return $db->query("UPDATE reports SET report_status='$status', report_revised_by={$current_user->user_id}, report_modified=FROM_UNIXTIME($report_modified) WHERE report_ref_id={$report->ref_id} AND report_reason='{$report->reason}' AND report_type='link_comment'");
}

function calculate_statistics()
{

	$statistics[Report::REPORT_STATUS_PENDING] = Report::get_total_in_status(Report::REPORT_STATUS_PENDING);
	$statistics[Report::REPORT_STATUS_DEBATE] = Report::get_total_in_status(Report::REPORT_STATUS_DEBATE);
	$statistics[Report::REPORT_STATUS_PENALIZED] = Report::get_total_in_status(Report::REPORT_STATUS_PENALIZED);
	$statistics[Report::REPORT_STATUS_DISMISSED] = Report::get_total_in_status(Report::REPORT_STATUS_DISMISSED);

	return $statistics;

}

function group_by_comment($reports) {

	$grouped_reports = array();
	$parsed = array();

	foreach ($reports as $report) {
		$group = array();

		foreach ($reports as $r) {
			if ($r->ref_id == $report->ref_id && !in_array($r->ref_id, $parsed)) {
				$group[] = $r;
			}
		}

		$parsed[] = $report->ref_id;

		$grouped_reports[] = array(
			'num_lines' => count($group),
			'lines' => $group
		);

	}

	return $grouped_reports;

}