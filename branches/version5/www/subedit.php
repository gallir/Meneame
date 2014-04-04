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

if (empty($routes)) die; // Don't allow to be called bypassing dispatcher

force_authentication();

if (!empty($_POST['id'])) {
	$id = intval($_POST['id']);
} else {
	if ($globals['submnm']) {
		$id = SitesMgr::my_id();
	} else {
		$id = intval($_GET['id']);
	}
}
if (! $id) $id = -1;

$errors = array();
$site = SitesMgr::get_info();

array_push($globals['cache-control'], 'no-cache');

if (! SitesMgr::can_edit($id)) {
	$errors[] = _("no puede editar o crear nuevos");
} else {
	if ($_POST['created_from']) {
		$id = save_sub($id, $errors);
		$sub = SitesMgr::get_info($id, true);
		if ($id && empty($errors)) {
			header("Location: ".$globals['base_url']."m/$sub->name/subedit");
			die;
		}
		if (! $id) {
			$sub = (object) $_POST; // Copy the data for the form, in case it failed to store
		}
	}
}

if ($id > 0) {
	$globals['submnm_info'] = $sub = SitesMgr::get_info($id);
}

if ($current_user->admin) {
	$candidates_to = $db->get_results("select id, name from subs where sub = 0 and id not in (select dst from subs_copy where src = $id)");
	$copy_to = $db->get_results("select id, name from subs, subs_copy where src = $id and id = dst");
} else {
	$copy_to = $candidates_to = false;
}

do_header(_("editar sub"));
echo '<div id="singlewrap">'."\n";
Haanga::Load('sub_edit.html', compact('sub', 'errors', 'site', 'candidates_to', 'copy_to'));
echo "</div>"."\n";

do_footer();

function save_sub($id, &$errors) {
	global $current_user, $db;

	// Double check
	$owner = intval($_POST['owner']);
	if (! SitesMgr::can_edit($id)) {
		array_push($errors, _('usuario no autorizado a editar'));
		return false;
	}
	$site = SitesMgr::get_info();
	if ($_POST['created_from'] != $site->id) {
		array_push($errors, _('sitio erróneo'));
	}

	if($owner != $current_user->user_id && ! $current_user->admin) {
		array_push($errors, _('propietario erróneo'));
	}

	$name = mb_substr(clean_input_string($_POST['name']), 0, 12);
	if (mb_strlen($name) < 3 || ! preg_match('/^\p{L}[\p{L}\d_]+$/u', $name)) {
		array_push($errors, _('nombre erróneo'). ' ' . $_POST['name']);
	}
	
	$name_long = mb_substr(clean_text($_POST['name_long']), 0, 40);
	if (mb_strlen($name_long) < 6) {
		array_push($errors, _('título erróneo'));
	}

	$name = $db->escape($name);
	$name_long= $db->escape($name_long);
	if ($db->get_var("select count(*) from subs where name = '$name' and id != $id") > 0) {
		array_push($errors, _('nombre duplicado'));
	}

	if ($current_user->admin) {
		$enabled = intval($_POST['enabled']);
		$allow_main_link = intval($_POST['allow_main_link']);
	} else {
		// Keep the values
		$enabled = 'enabled';
		$allow_main_link = 'allow_main_link';
	}

	$nsfw = intval($_POST['nsfw']);
	$private = intval($_POST['private']);
	


	if (empty($errors)) {
		$db->transaction();
		if ($id > 0) {
			$r = $db->query("update subs set owner = $owner, enabled = $enabled, allow_main_link = $allow_main_link, nsfw = $nsfw, name = '$name', name_long = '$name_long', private = $private where id = $id");
		} else {
			$r = $db->query("insert into subs (created_from, owner, nsfw, name, name_long, sub, private) values ($site->id, $owner, $nsfw, '$name', '$name_long', 1, $private)");
			$id = $db->insert_id;
		}
		if ($r && $id > 0) {
			// Copy values from first site
			$r = $db->query("update subs as a join subs as b on a.id = $id and b.id=$site->id set a.server_name = b.server_name, a.base_url = b.base_url");
			// Update copy_to
			if ($current_user->admin) {
				sub_copy_to($id, $_POST['copy_to']);
			}

			// Update colors
			$color_regex = '/^#[a-f0-9]{6}/i';
			if (preg_match($color_regex, $_POST['color1'])) $color1 = $db->escape($_POST['color1']);
			else $color1 = '';
			if (preg_match($color_regex, $_POST['color2'])) $color2 = $db->escape($_POST['color2']);
			else $color2 = '';
		
			$db->query("update subs set color1 = '$color1', color2 = '$color2' where id = $id");
		}
		if ($r && $id > 0) {
			$db->commit();
			return $id;
		} else {
			array_push($errors, _('error actualizando la base de datos'));
			$db->rollback();
		}

	}
	return false;
}

function sub_copy_to($src, $dests) {
	global $db;
	$r = $db->query("delete from subs_copy where src = $src");
	if (empty($dests) || ! is_array($dests)) return;
	foreach ($dests as $dst) {
		$dst = intval($dst);
		if ($dst > 0) {
			$db->query("insert into subs_copy (src, dst) values ($src, $dst)");
		}
	}
}


