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
require_once(mnminclude . 'ban.php');
include(__DIR__.'/libs/admin.php');

do_header(_('Admin logs'));

$page_size = 40;
$offset = (get_current_page() - 1) * $page_size;

$operation = $_REQUEST["op"] ? $_REQUEST["op"] : 'list';
$search = $_REQUEST["s"];
$orderby = $_REQUEST["order_by"];

$selected_tab = "hostname";
if ($_REQUEST["tab"]) {
	$selected_tab = clean_input_string($_REQUEST["tab"]);
}

do_admin_tabs($selected_tab);

$key = get_security_key();

if ($current_user->user_level=="god" && check_security_key($_REQUEST["key"])) {

	if (!empty($_REQUEST["new_ban"])) {
		insert_ban($selected_tab, $_POST["ban_text"], $_POST["ban_comment"], $_POST["ban_expire"]);
	} elseif (!empty($_REQUEST["edit_ban"])) {
		insert_ban($selected_tab, $_POST["ban_text"], $_POST["ban_comment"], $_POST["ban_expire"], $_POST["ban_id"]);
	} elseif (!empty($_REQUEST["new_bans"])) {
		$array = preg_split ("/\s+/", $_POST["ban_text"]);
		$size = count($array);
		for($i=0; $i < $size; $i++) {
			insert_ban($selected_tab, $array[$i], $_POST["ban_comment"], $_POST["ban_expire"]);
		}
	} elseif (!empty($_REQUEST["del_ban"])) {
		del_ban($_REQUEST["del_ban"]);
	}
}

switch ($operation) {
	case 'list':
		do_ban_list($selected_tab, $search, $orderby, $key);
		break;
	case 'new':
		do_ban_new($selected_tab, $search, $key);
		break;
	case 'edit':
		do_ban_edit($selected_tab, $search, $key);
		break;
	case 'news':
		do_ban_news($selected_tab, $search, $key);
		break;
}

do_footer();

function do_ban_list($selected_tab, $search, $orderby, $key) {
	global $db, $offset, $page_size;

	if (empty($orderby)) {
		$orderby = 'ban_date';
		$order = "DESC";
	} else {
		$orderby = preg_replace('/[^a-z_]/i', '', $orderby);
		if ($orderby == 'ban_date') {
			$order = "DESC";
		} else {
			$order = "ASC";
		}
	}
	$where = "WHERE ban_type='" . $selected_tab . "'";
	if ($search) {
		$search_text = $db->escape($search);
		$where .= " AND (ban_text LIKE '%$search_text%' OR ban_comment LIKE '%$search_text%')";
	}

	$rows = $db->get_var("SELECT count(*) FROM bans " . $where);
	$sql = "SELECT * FROM bans " . $where . " ORDER BY $orderby $order LIMIT $offset,$page_size";
	$bans = $db->get_results($sql);

	Haanga::Load('admin/bans/list.html', compact('bans', 'selected_tab', 'key', 'search'));

	do_pages($rows, $page_size, false);

}

function do_ban_new($selected_tab, $search, $key) {
	Haanga::Load('admin/bans/new.html', compact('selected_tab', 'search', 'key'));
}

function do_ban_edit($selected_tab, $search, $key) {
	$ban_id = intval($_REQUEST['id']);
	$ban = new Ban();
	$ban->ban_id = $ban_id;
	$ban->read();
	Haanga::Load('admin/bans/edit.html', compact('ban', 'selected_tab', 'search', 'key'));
}

function do_ban_news($selected_tab, $search, $key) {
	Haanga::Load('admin/bans/news.html', compact('selected_tab', 'search', 'key'));
}