<?php
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require __DIR__."/common.php";

if (!empty($_POST['process'])) {
	$valid = false;
	if (!empty($_POST['form_time']) && !empty($_POST['form_hash'])) {
		$valid = sha1($site_key . $_POST['form_time'] . $current_user->user_id) == $_POST['form_hash'];
	}

	if (empty($_POST['vote_starts'])) {
		// if vote_starts is empty by default 7 days before
		$_POST['vote_starts'] = 7*24;
	}

	if (is_numeric($_POST['vote_starts'])) {
		// if the input is a number we assume they are hours
		$_POST['vote_starts'] = date("Y-m-d H:i:s", strtotime($_POST['date']) - intval($_POST['vote_starts']) * 3600);
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
		$league->vote_starts	= $_POST['vote_starts'];
		if (is_numeric($_POST['score_local']) && is_numeric($_POST['score_visitor'])) {
			$league->score_local   = $_POST['score_local'];
			$league->score_visitor = $_POST['score_visitor'];
		}
		$league->store();
	} else if (!$valid) {
		die(_("El token del formulario no es correcto"));
	}
	$_GET = array('action' => 'list');
	$_SERVER['QUERY_STRING'] = http_build_query($_GET);
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
	$total    = $db->get_row("SELECT count(*) as total from " . Match::TABLE)->total;
	$per_page = 30;
	$pages    = ceil($total/$per_page);
	$current  = $pages;
	if (!empty($_GET['page']) && is_numeric($_GET['page'])) {
   	 	$current = intval($_GET['page']);
	}
	$offset  = ($pages-$current) * $per_page;

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
	ORDER BY id DESC
	LIMIT $offset, $per_page
	";
	$data['cols'] = array('liga' => _('Liga'), 'local' => _('Local'), 'visitante' => _('Visitante'), 'date' => _('Fecha'));
	$data['rows'] = $db->get_results($sql);
	$data['page'] = $globals['league_base_url'] . 'matches.php';
	Haanga::Load("league/abm-list.tpl", $data);
	do_pages_reverse($total, $per_page);
}

do_footer();

/* vim:set noet ci pi sts=0 sw=4 ts=4: */
