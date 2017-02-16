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

global $site_key;

$page_size = 40;
$offset = (get_current_page() - 1) * $page_size;

$operation = $_REQUEST["op"] ? $_REQUEST["op"] : 'list';
$search = $_REQUEST["s"];
$orderby = $_REQUEST["order_by"];

$selected_tab = "strikes";
if ($_REQUEST["tab"]) {
	$selected_tab = clean_input_string($_REQUEST["tab"]);
}

$strike_type = 'all';
if (!empty($_REQUEST["strike_type"])) {
	$strike_type = $_REQUEST["strike_type"];
}

$strike_date = 'all';
if (!empty($_REQUEST["strike_date"])) {
	$strike_date = clean_input_string($_REQUEST["strike_date"]);
}

$user_login = (empty($_REQUEST['strike_user'])) ? NULL : clean_input_url($_REQUEST['strike_user']);

$key = md5($randkey . $site_key);

switch ($operation) {
	case 'list':
		do_header(_('listado de strikes'));
		do_admin_tabs($selected_tab);
		do_strike_list($selected_tab, $search, $strike_type, $strike_date, $orderby);
		break;
	case 'new':
		do_header(_('nuevo strike'));
		do_admin_tabs($selected_tab);
		do_new_strike($selected_tab, $orderby, $randkey, $key, $user_login);
		break;
	case 'save';
		do_save_strike();
		break;
}

do_footer();

function do_strike_list($selected_tab, $search, $strike_type, $strike_date, $orderby, $randkey)
{
	global $db, $offset, $page_size, $globals;

	if (empty($orderby)) {
		$orderby = 'strike_date';
		$order = "DESC";
	} else {
		$orderby = preg_replace('/[^a-z_]/i', '', $orderby);
		if ($orderby == 'strike_date') {
			$order = "DESC";
		} else {
			$order = "ASC";
		}
	}

	$rows = $db->get_var("SELECT count(*) FROM strikes");

	$sql = "SELECT" . Strike::SQL . " ORDER BY $orderby $order LIMIT $offset,$page_size";

	$strikes = $db->get_results($sql);

	Haanga::Load('admin/strikes/list.html', compact('selected_tab', 'strikes'));
	do_pages($rows, $page_size, false);


}


function do_new_strike($selected_tab, $orderby, $randkey, $key, $user_login)
{
	global $db, $offset, $page_size, $globals;

	$user = null;
	$strikes = null;
	$new_karma = null;

	if (!is_null($user_login)) {
		$user = new User();
		$user->username = $user_login;
		if ($user->read()) {
			$new_karma_strike_1 = Strike::calculate_new_karma($user, Strike::STRIKE_TYPE_1);
			$new_karma_strike_2 = Strike::calculate_new_karma($user, Strike::STRIKE_TYPE_2);
			$new_karma_ban = Strike::calculate_new_karma($user, Strike::STRIKE_TYPE_BAN);
			$applied_strikes = Strike::get_applied_strikes_to_user($user->id);
			$strikes = $db->get_results("SELECT " . Strike::SQL . " WHERE strike_user_id = {$user->id} ORDER BY strike_date DESC");
		}
	}

	Haanga::Load('admin/strikes/new.html', compact('selected_tab', 'user', 'strikes', 'strike_type', 'new_karma_strike_1', 'new_karma_strike_2', 'new_karma_ban', 'applied_strikes', 'key', 'randkey'));
}

function do_save_strike()
{

	global $db, $globals, $current_user, $site_key;

	// Check key
	if ($_POST['key'] != md5($_POST['randkey'] . $site_key)) {
		echo "Clave incorrecta";
		die;
	}

	// Check user
	$user = new User();
	$user->id = intval($_POST['user_id']);

	if (!$user->read()) {
		echo "Usuario inexistente";
		die;
	}

	// Check strike type
	$strike_type = $_POST['strike_type'];

	if (!Strike::is_valid_strike_type($strike_type)) {
		echo "Tipo de strike no válido";
		die;
	}

	// Check strike reason
	if (in_array($strike_type, Strike::get_applied_strikes_to_user($user->id))) {
		echo "Strike $strike_type ya aplicado";
		die;
	}

	// Save strike
	$strike = new Strike($strike_type);
	$strike->admin_id = $current_user->user_id;
	$strike->user_id = $user->id;
	$strike->comment = clean_input_string($_POST['strike_comment']);
	$strike->reason = $_POST['strike_reason'];
	$strike->old_karma = $user->karma;
	$strike->new_karma = Strike::calculate_new_karma($user->karma, $strike_type);

	$strike->store();

	// TODO: Do ban to user and save in admin_log with type strike.


	header("Location: " . $_SERVER['REQUEST_URI']);

}

