<?php
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// David Martín :: Suki_ :: <david at sukiweb dot net>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

$globals['skip_check_ip_noaccess'] = true;
include('../config.php');
include(mnminclude . 'html1.php');
include('libs/admin.php');

do_header(_('Admin logs'));

$page_size = 40;
$offset = (get_current_page() - 1) * $page_size;

$operation = $_REQUEST["op"] ? $_REQUEST["op"] : 'list';
$search = $_REQUEST["s"];
$orderby = $_REQUEST["order_by"];

$selected_tab = "admin_logs";
if ($_REQUEST["tab"]) {
	$selected_tab = clean_input_string($_REQUEST["tab"]);
}

$log_type = false;
if (!empty($_REQUEST["log_type"])) {
	$log_type = clean_input_string($_REQUEST["log_type"]);
}

do_admin_tabs($selected_tab);

$key = get_security_key();

switch ($operation) {
	case 'list':
		do_log_list($selected_tab, $search, $log_type, $orderby, $key);
		break;
}

do_footer();

function do_log_list($selected_tab, $search, $log_type, $orderby, $key) {
	global $db, $offset, $page_size;

	if (empty($orderby)) {
		$orderby = 'log_date';
		$order = "DESC";
	} else {
		$orderby = preg_replace('/[^a-z_]/i', '', $orderby);
		if ($orderby == 'log_date') {
			$order = "DESC";
		} else {
			$order = "ASC";
		}
	}
	$where = 'WHERE 1=1';
	if ($log_type) {
		$where .= " AND log_type='" . $log_type. "'";
	}

	if ($search) {
		$search_text = $db->escape($search);
		$where .= " AND (admin.user_login LIKE '%$search_text%' OR u.user_login LIKE '%$search_text%')";
	}

	$rows = $db->get_var("SELECT count(*) FROM admin_logs " . $where);
	$sql = "SELECT admin.user_login as admin_user_login, admin_logs.*, u.user_id as user_id, u.user_login as user_login, u.user_karma as user_karma, u.user_level as user_level FROM admin_logs 
			LEFT JOIN users as admin on (admin_logs.log_user_id=admin.user_id)
			LEFT JOIN users as u on (admin_logs.log_ref_id=u.user_id) " . $where . " ORDER BY $orderby $order LIMIT $offset,$page_size";

	$logs = $db->get_results($sql);

	Haanga::Load('admin/logs/list.html', compact('logs', 'selected_tab', 'key', 'search', 'log_type'));

	do_pages($rows, $page_size, false);

}