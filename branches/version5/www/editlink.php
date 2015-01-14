<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'tags.php');

force_authentication();

array_push($globals['cache-control'], 'no-cache');
do_header(_("editar noticia"), "post");

echo '<div id="singlewrap">'."\n";

if (!empty($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
	$link=new Link;
	$link->id=$link_id = intval($_REQUEST['id']);
	$link->read();
	if (!$link->is_editable() || intval($_GET['user'] != $current_user->user_id)) {
		echo '<div class="form-error-submit">&nbsp;&nbsp;'._("noticia no modificable").'</div>'."\n";
		return;
	}
	if ($_POST['phase'] == "1") {
		do_save($link);
		fork("backend/send_pingbacks.php?id=$link->id");
	} else {
		do_edit($link);
	}
} else {
	echo '<div class="form-error-submit">&nbsp;&nbsp;'._("¿duh?").'</div>';
}


echo "</div>"."\n";

do_footer();

function do_edit($link) {
	global $dblang, $db, $current_user, $globals;

	$link->status = $link->sub_status;
	$link->discarded = $link->is_discarded();
	$link->status_text = $link->get_status_text();
	$link->key = md5($globals['now'].$link->randkey);
	$link->chars_left = 550 - mb_strlen(html_entity_decode($link->content, ENT_COMPAT, 'UTF-8'), 'UTF-8');
	$link->has_thumb();
	$link->is_new = false;
	$link->is_sub_owner = SitesMgr::is_owner();
	$link->site_properties = SitesMgr::get_extended_properties();
	Haanga::Load('link/edit.html', compact('link'));
}

function do_save($link) {
	global $dblang, $globals, $current_user, $db;

	$link->status = $link->sub_status;
	$site_properties = SitesMgr::get_extended_properties();

	// Store previous value for the log
	$link_old = new stdClass;
	$link_old->url = $link->url;
	$link_old->title = $link->title;
	$link_old->content = $link->content;
	$link_old->tags = $link->tags;
	$link_old->status = $link->status;
	$link_old->sub_id = $link->sub_id;

	$link->read_content_type_buttons($_POST['type']);

	$link->sub_id=intval($_POST['sub_id']);
	if ($link->sub_id != $link_old->sub_id) {
		$link->sub_changed = true; // To force to delete old statuses with another origin
	}

	if ($current_user->admin || $current_user->user_level == 'blogger' || SitesMgr::is_owner()) {
		if (!empty($_POST['url'])) {
			$link->url = clean_input_url($_POST['url']);
		}
		if ($_POST['thumb_delete']) {
			$link->delete_thumb();
		}
		if ($_POST['uri_update']) {
			$link->get_uri();
		}
		if ($_POST['thumb_get']) {
			$link->get_thumb();
		} elseif (!empty($_POST['thumb_url'])) {
			$url = clean_input_url($_POST['thumb_url']);
			$link->get_thumb(false, $url);
		}
	}
	$link->title = $_POST['title'];
	$link->content = $_POST['bodytext'];
	$link->tags = tags_normalize_string($_POST['tags']);
	$errors = link_edit_errors($link);

	// change the status
	if ($_POST['status'] != $link->status
		&& ($_POST['status'] == 'autodiscard' || $current_user->admin || SitesMgr::is_owner())
		&& preg_match('/^[a-z]{4,}$/', $_POST['status'])
		&& ( ! $link->is_discarded() || $current_user->admin || SitesMgr::is_owner())) {
		if (preg_match('/discard|abuse|duplicated|autodiscard/', $_POST['status'])) {
			// Insert a log entry if the link has been manually discarded
			$insert_discard_log = true;
		}
		$link->status = $_POST['status'];
	}

	if (! $errors) {
		if (empty($link->uri)) $link->get_uri();
		// Check the blog_id
		$blog_id = Blog::find_blog($link->url, $link->id);
		if ($blog_id > 0 && $blog_id != $link->blog) {
			$link->blog = $blog_id;
		}

		$db->transaction();
		$link->store();
		// Disabled table tags
		// tags_insert_string($link->id, $dblang, $link->tags, $link->date);

		// Insert edit log/event if the link it's newer than 15 days
		if ($globals['now'] - $link->date < 86400*15) {
			if ($insert_discard_log) {
				// Insert always a link and discard event if the status has been changed to discard
				Log::insert('link_discard', $link->id, $current_user->user_id);
				if ($link->author == $current_user->user_id) { // Don't save edit log if it's discarded by an admin
					Log::insert('link_edit', $link->id, $current_user->user_id);
				}
			} elseif ($link->votes > 0) {
				Log::conditional_insert('link_edit', $link->id, $current_user->user_id, 60, serialize($link_old));
			}
		}

		// Check this one is a draft, allows the user to save and send it to the queue
		if($link->votes == 0 && $link->status != 'queued' && $link->author == $current_user->user_id) {
			$link->enqueue();
		}
		$db->commit();

		// Check image upload
		if ($link->store_image_from_form('image')) {
			$link->thumb_status = 'local';
			$link->store_thumb_status();
		}
	}
	$link->read();
	$link->permalink = $link->get_permalink();

	Haanga::Load('link/edit_result.html', compact('link', 'errors'));
}

function link_edit_errors($link) {
	global $current_user, $globals;

	$errors = array();


	$site_info = SitesMgr::get_info($link->sub_id);
	$site_properties = SitesMgr::get_extended_properties($link->sub_id);
	if (! $site_info->enabled || (! SitesMgr::is_owner() && ! empty($site_properties['new_disabled'] ) )) {
		$errors[] = _('no se puede enviar a ese sub');
		$error = true;
	}


	// only checks if the user is not special or god
	if(! empty($link->url) && ! $link->check_url($link->url, false) && ! $current_user->admin) {
		$errors[] = _('url incorrecto');
	}

	if($_POST['key'] !== md5($_POST['timestamp'].$link->randkey)) {
		$errors[] = _('clave incorrecta');
		$error = true;
	}

	if(time() - $_POST['timestamp'] > 900) {
		$errors[] =  _('tiempo excedido');
	}

	$errors = array_merge($errors, $link->check_field_errors());

	return $errors;
}

?>
