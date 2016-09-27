<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (!defined('mnmpath')) {
	include(dirname(__FILE__) . '/../config.php');
	include(mnminclude . 'html1.php');
}

array_push($globals['cache-control'], 'no-cache');
http_cache();

if (!empty($_REQUEST['id']) && ($id = intval($_REQUEST['id'])) > 0 && $current_user->user_id > 0) {
	$comment = Comment::from_db($id);
	if (!$comment) die;
	$link_id = $comment->link;
} else {
	die;
}

if ($_POST['process'] == 'newreport') {
	save_report($comment, $link_id);
} elseif ($_POST['process'] == 'check_can_report') {

	if (!check_security_key($_POST['key'])) die;
	$res = check_report($comment, $link_id);
	if (true === $res) {
		$data['html'] = '';
		$data['error'] = '';
	} else {
		$data['html'] = '';
		$data['error'] = $res;
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
} else {
	print_edit_form($comment, $link_id);
}

function check_report($comment, $link_id) {

	global $current_user, $globals;

	// Check if current user can report
	if (!Report::check_report_user_limit()) {
		return _('has superado el límite de reportes de comentarios<br>(máximo ' . $globals['max_reports_for_comments'] . ' reportes / 24 horas)');
	}

	// Check for min karma
	if (!Report::check_min_karma()) {
		return _('no dispones de karma suficiente para reportar comentarios');
	}

	// Check if user votes his own comment! :p
	if ($current_user->user_id == $comment->user_id) {
		return _('no puedes reportar tu propio comentario');
	}

	// Check if user has already reported
	if (Report::already_reported($comment->id)) {
		return _('Ya has reportado este comentario.');
	}

	// Check comments closed
	if ($comment->date < $globals['now'] - $globals['time_enabled_comments']) {
		return _('comentarios cerrados');
	}

	return true;
}

function print_edit_form($comment, $link_id)
{
	global $current_user, $site_key;
	$randkey = rand(1000000, 100000000);
	$key = md5($randkey . $site_key);
	echo Haanga::Load("report_new.html", compact('comment', 'link_id', 'current_user', 'site_key', 'randkey', 'key'), true);
}


function check_save_report($comment, $link_id)
{
	global $site_key, $current_user, $globals;

	// Check key
	if (!$_POST['key'] || ($_POST['key'] != md5($_POST['randkey'] . $site_key))) {
		return _('petición incorrecta');
	}

	// Check user equals current user
	if ($current_user->user_id != $_POST['user_id']) {
		return _('petición incorrecta');
	}

	// Check that at least one valid option is selected (report reason)
	if (!$_POST['report_reason'] || !Report::is_valid_reason($_POST['report_reason'])) {
		return _('debes seleccionar una opción');
	}

	// Check if current user can report
	if (!Report::check_report_user_limit()) {
		return _('has superado el límite de reportes de comentarios<br>(máximo ' . $globals['max_reports_for_comments'] . ' comentarios / 24 horas)');
	}

	// Check for min karma
	if (!Report::check_min_karma()) {
		return _('no dispones de karma suficiente para reportar comentarios');
	}

	// Check if user votes his own comment! :p
	if ($current_user->user_id == $comment->user_id) {
		return _('no puedes reportar tu propio comentario');
	}

	// Check if user has already reported
	if (Report::already_reported($comment->id)) {
		return _('Ya has reportado este comentario.');
	}

	// Check comments closed
	if ($comment->date < $globals['now'] - $globals['time_enabled_comments']) {
		return _('comentarios cerrados');
	}

	// save report
	$report = new Report();
	$report->reason = $_POST['report_reason'];
	$report->reporter_id = $current_user->user_id;
	$report->ref_id = $comment->id;

	return $report->store();
}

function save_report($comment, $link_id)
{
	$res = check_save_report($comment, $link_id);

	if (true === $res) {
		$data['html'] = '';
		$data['error'] = '';
	} else {
		$data['html'] = '';
		$data['error'] = $res;
	}

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
}
