<?php
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
/* vim:set noet ci pi sts=0 sw=4 ts=4: */

require __DIR__."/common.php";

if (!empty($_POST['process'])) {
	$valid = false;
	if (!empty($_POST['form_time']) && !empty($_POST['form_hash'])) {
		$valid = sha1($site_key . $_POST['form_time'] . $current_user->user_id) == $_POST['form_hash'];
	}
	if ($valid && empty($_POST['team_id'])) {
		Team::create($_POST);
	} else if ($valid) {
		$league = new Team($_POST['team_id']);
		if (!$league->read()) {
			die(_("No se puede encontrar el equipo"));
		}
		$league->name = $_POST['name'];
		$league->shortname = $_POST['shortname'];
		$league->store();
	} else if (!$valid) {
		die(_("El token del formulario no es correcto"));
	}
	$_GET['action'] = 'list';
}


do_header(_('Administración de Ligas'));
do_league_tabs();

switch ($_GET['action']) {
case 'create':
	create_form('team', _('Agregar un equipo'));
	break;

case 'update':
	$league = new Team($_GET['id']);
	if (!$league->read()) {
		die(_("No se puede encontrar el Equipo"));
	}
	create_form('team', _('Editar un Equipo'), $league);
	break;

case 'list':
default:
	$data['cols'] = array('name' => _('Nombre'), 'image' => _('Image'));
	$data['rows'] = $db->get_results("SELECT * FROM " . Team::TABLE);
	$data['page'] = $globals['league_base_url'] . 'teams.php';
	foreach ($data['rows'] as $row) {
		$row->image = "<img src='${globals['base_static']}img/liga_f_ES_2012/" . strtolower($row->shortname)  . ".png'>";
	}
	Haanga::Load("league/abm-list.tpl", $data);
}

do_footer();

/* vim:set noet ci pi sts=0 sw=4 ts=4: */
