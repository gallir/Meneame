<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
$globals['ads'] = false;

array_push($globals['cache-control'], 'no-cache');

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
$extended = array();


$can_edit = SitesMgr::can_edit($id);

if (! $can_edit) {
	$errors[] = _("no puede editar o crear nuevos");
} else {
	if ($_POST['created_from']) {
		$id = save_sub($id, $errors);
		$sub = SitesMgr::get_info($id, true);
		if ($id && empty($errors)) {
			header("Location: ".$globals['base_url_general']."m/$sub->name/subedit");
			die;
		}
		if (! $id) {
			$sub = (object) $_POST; // Copy the data for the form, in case it failed to store
		}
	}
}

if ($id > 0 && $can_edit) {
	$globals['submnm_info'] = $sub = SitesMgr::get_info($id);
	$extended = SitesMgr::get_extended_properties($id);
}

if ($current_user->admin) {
	$candidates_from = $db->get_results("select id, name from subs where owner = 0 and id not in (select src from subs_copy where dst = $id)");
	$copy_from = $db->get_results("select id, name from subs, subs_copy where dst = $id and id = src");
} else {
	$copy_from = $candidates_from = false;
}

do_header(_("editar sub"));
echo '<div id="singlewrap">'."\n";
Haanga::Load('sub_edit.html', compact('sub', 'extended', 'errors', 'site', 'candidates_from', 'copy_from'));
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
	
	// Check the extended info
	foreach (array('no_link', 'no_anti_spam', 'allow_local_links', 'intro_max_len', 'intro_min_len') as $k) {
		if (isset($_POST[$k]) && $_POST[$k] !== '') {
			$_POST[$k] = intval($_POST[$k]);
		}
	}
	
	if ($_POST['intro_max_len'] > 5000) $_POST['intro_max_len'] = 5000;

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
			// Update copy_from
			if ($current_user->admin) {
				sub_copy_from($id, $_POST['copy_from']);
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
			SitesMgr::store_extended_properties($id, $_POST);
			$db->commit();
			store_image($id);
			return $id;
		} else {
			array_push($errors, _('error actualizando la base de datos'));
			$db->rollback();
		}

	}
	return false;
}

function sub_copy_from($id, $from) {
	global $db;
	$r = $db->query("delete from subs_copy where dst = $id");
	if (empty($from) || ! is_array($from)) return;
	foreach ($from as $src) {
		$src = intval($src);
		if ($src > 0) {
			$db->query("insert into subs_copy (src, dst) values ($src, $id)");
		}
	}
}

function store_image($id) {
	$media = new Upload('sub_logo', $id, 0);
	$media->media_size = 0;
	$media->media_mime = '';
		
	if(!empty($_FILES['logo_image']['tmp_name'])) {
		$media->access = 'public';
		if ($media->from_temporal($_FILES['logo_image'], 'image')) {
			$site->media_size = $media->size;
			$site->media_mime = $media->mime;
			$site->media_dim1 = $media->dim1;
			$site->media_dimd = $media->dim2;
		}
	} elseif ($_POST['logo_image_delete']) {
		$media->delete();
	}
}
