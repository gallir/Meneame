<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
$globals['ads'] = false;

force_authentication();
$id = intval($_REQUEST['id']);
if (! $id) $id = -1;
if (! SitesMgr::can_edit($id)) die;

$errors = array();

array_push($globals['cache-control'], 'no-cache');
do_header(_("editar sub"));

echo '<div id="singlewrap">'."\n";

if ($_POST['u']) {
	$id = save_sub();
}

$sub = SitesMgr::get_info($id);
Haanga::Load('sub_edit.html', compact('sub', 'errors'));
echo "</div>"."\n";

do_footer();

function save_sub($errors) {
	global $current_user, $db;

	$errors = array();

	// Double check
	$id = intval($_POST['id']);
	$owner = intval($_POST['owner']);
	if (! $id) $id = -1;
	if (! SitesMgr::can_edit($id)) {
		array_push($errors, _('usuario no autorizado a editar'));
		return $errors;
	}

	if($owner != $current_user->user_id && ! $current_user->admin) {
		array_push($errors, _('propietario erróneo'));
	}
		

	$name = mb_substr(clean_input_string($_POST['name']), 0, 12);
	if (mb_strlen($name) < 3) {
		array_push($errors, _('nombre erróneo'). ' ' . $_POST['name']);
	}
	
	$name_long = mb_substr(clean_text($_POST['name_long']), 0, 32);
	if (mb_strlen($name_long) < 6) {
		array_push($errors, _('título erróneo'));
	}

	if (empty($errors)) {
		$name = $db->escape($name);
		$name_long= $db->escape($name_long);
		if ($id > 0) {
			$r = $db->query("update subs set owner = $owner, name = '$name', name_long = '$name_long' where id = $id");
		} else {
			$r = $db->query("insert into subs (owner, name, name_long, sub) values ($owner, '$name', '$name_long', 1)");
			if ($r) {
				$id = $db->insert_id;
				if ($id > 0) {
					// Copy values from first site
					$r = $db->query("update subs as a join subs as b on a.id = $id and b.id=1 set a.server_name = b.server_name, a.base_url = b.base_url");
				} else {
					array_push($errors, _('error insertando, quizás nombre duplicado'));
				}
			}
		}
		if (! $r) {
			array_push($errors, _('error actualizando la bbdd'));
		}
	}

	return $id;
}


