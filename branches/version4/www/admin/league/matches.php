<?php
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require "common.php";

if (!empty($_POST['process'])) {
	$valid = false;
	if (!empty($_POST['form_time']) && !empty($_POST['form_hash'])) {
		$valid = sha1($site_key . $_POST['form_time'] . $current_user->user_id) == $_POST['form_hash'];
	}
	if ($valid && empty($_POST['match_id'])) {
		Match::create($_POST);
	} else if ($valid) {
		$league = new Match($_POST['match_id']);
		if (!$league->read()) {
			die(_("No se puede encontrar el equipo"));
		}
		$league->league_id 	= $_POST['league'];
		$league->local		= $_POST['local'];
		$league->visitor	= $_POST['visitor'];
		$league->date		= $_POST['date'];
		$league->vote_until	= $_POST['vote_until'];
		$league->store();
	} else if (!$valid) {
		die(_("El token del formulario no es correcto"));
	}
	$_GET['action'] = 'list';
}


do_header(_('Administración de Ligas'));
do_league_tabs();

$data = array(
	'leagues' => $db->get_results("SELECT id,name FROM league"),
	'teams' => $db->get_results("SELECT id,name FROM league_teams"),
);
switch ($_GET['action']) {
case 'create':
	create_form('match', _('Agregar un partido'), $data);
	break;

case 'update':
	$league = new Match($_GET['id']);
	if (!$league->read()) {
		die(_("No se puede encontrar el PArtido"));
	}
	create_form('match', _('Editar un partido'), array_merge($data, get_object_vars($league)));
	break;

case 'list':
default:
	$sql = "SELECT
		m.*,
		t1.name as local,
		t1.shortname as local_short,
		t2.name as visitante,
		t2.shortname as visitante_short,
		l.name as liga
	FROM 
		" . Match::TABLE ." m
	INNER JOIN " . Team::TABLE ." t1 ON (t1.id = m.local)
	INNER JOIN " . Team::TABLE ." t2 ON (t2.id = m.visitor)
	INNER JOIN " . League::TABLE ." l ON (l.id = m.league_id)
	";
	$data['cols'] = array('liga' => _('Liga'), 'local' => _('Local'), 'visitante' => _('Visitante'), 'date' => _('Fecha'));
	$data['rows'] = $db->get_results($sql);
	$data['page'] = $globals['league_base_url'] . 'matches.php';
	Haanga::Load("league/abm-list.tpl", $data);
}

do_footer();

/* vim:set noet ci pi sts=0 sw=4 ts=4: */
