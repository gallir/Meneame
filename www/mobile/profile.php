<?php
// The source code packaged with this file is Free Software, Copyright (C) 2008 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//				http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include(__DIR__.'/config.php');
include(mnminclude.'html1-mobile.php');
include(mnminclude.'avatars.php');

$globals['ads'] = false;

// We need it because we modify headers
ob_start();

$user_levels = array ('disabled', 'normal', 'special', 'admin', 'god');

// User recovering her password
if (!empty($_GET['login']) && !empty($_GET['t']) && !empty($_GET['k'])) {
	$time = intval($_GET['t']);
	$key = $_GET['k'];

	$user=new User();
	$user->username=clean_input_string($_GET['login']);
	if($user->read()) {
		$now = time();
		$key2 = md5($user->id.$user->pass.$time.$site_key.get_server_name());
		//echo "$now, $time; $key == $key2\n";
		if ($time > $now - 7200 && $time < $now && $key == $key2) {
			$db->query("update users set user_validated_date = now() where user_id = $user->id and user_validated_date is null");
			$current_user->Authenticate($user->username, false);
			header('Location: '.get_user_uri($user->username));
			die;
		}
	}
}
//// End recovery

if ($current_user->user_id > 0 && $current_user->authenticated && empty($_REQUEST['login'])) {
		$login=$current_user->user_login;
} else {
	header("Location: ./login.php");
	die;
}

$user=new User();
$user->username = $login;
if(!$user->read()) {
	not_found();
}


if (isset($_POST['process'])) {
	$globals['secure_page'] = True;
	$save_messages = save_profile();
} else {
	$globals['secure_page'] = False;
}

do_header(_('edición del perfil del usuario'). ': ' . $user->username);
echo '<div id="singlewrap">'."\n";
echo $save_messages; // We do it later because teh profile could change header's info
show_profile();
echo "</div>\n";
do_footer();


function show_profile() {
	global $user, $user_levels, $globals, $site_key, $current_user;

	echo '<div>';
	echo '<form  enctype="multipart/form-data" action="'.get_auth_link().'profile.php" method="post" id="thisform" AUTOCOMPLETE="off">';
	echo '<fieldset><legend>';
	echo '<span class="sign">'._('opciones de usuario') . " <a href='".get_user_uri($user->username)."'>$user->username</a>: $user->level</span></legend>";

	echo '<img class="thumbnail" src="'.$globals['base_url'] . 'backend/get_avatar.php?id='.$user->id.'&amp;size=80&amp;t='.time().'" width="80" height="80" alt="'.$user->username.'" />';
	echo '<input type="hidden" name="process" value="1" />';
	echo '<input type="hidden" name="user_id" value="'.$user->id.'" />';
	echo '<input type="hidden" name="form_hash" value="'. md5($site_key.$user->id.mnminclude) .'" />';

	echo '<p><label>'._('usuario').':</label><br/>';
	echo '<input type="text" autocomplete="off" name="username" id="username" value="'.$user->username.'"/>';
	echo '</p>';

	echo '<p><label>'._('nombre real').':</label><br/>';
	echo '<input type="text" autocomplete="off" name="names" id="names" value="'.$user->names.'"/>';
	echo '</p>';

	echo '<p><label>'._('correo electrónico').':</label><br/>';
	echo '<input type="text" autocomplete="off" name="email" id="email" value="'.$user->email.'"/>';
	echo '</p>';

	echo '<p><label>'._('página web').':</label><br/>';
	echo '<input type="text" autocomplete="off" name="url" id="url" value="'.$user->url.'" />';
	echo '</p>';


	if (is_avatars_enabled()) {
		echo '<input type="hidden" name="MAX_FILE_SIZE" value="300000" />';
		echo '<p><label>'._('avatar').':</label><br/>';
		echo '<input type="file" autocomplete="off" name="image" />';
		echo '</p>';
	}



	echo '<p><label for="password">' . _("nueva clave") . ':</label><br />' . "\n";
	echo '<input type="password" autocomplete="off" id="password" name="password" size="25" onkeyup="return securePasswordCheck(this.form.password);"/></p>' . "\n";

	echo '<p><label for="verify">' . _("repite la clave") . ': </label><br />' . "\n";
	echo '<input type="password" autocomplete="off" id="verify" name="password2" size="25" onkeyup="checkEqualFields(this.form.password2, this.form.password)"/></p>' . "\n";

	echo '<p><input type="submit" name="save_profile" value="'._('actualizar').'"/></p>';
	echo '</fieldset>';
	echo "</form></div>\n";

}


function save_profile() {
	global $db, $user, $current_user, $globals, $site_key;
	$errors = 0; // benjami: control added (2005-12-22)
	$new_pass=false;
	$messages = '';

	$form_hash = md5($site_key.$user->id.mnminclude);

	if(!isset($_POST['save_profile']) || !isset($_POST['process']) ||
		($_POST['user_id'] != $current_user->user_id) ) return;

	if ( empty($_POST['form_hash']) || $_POST['form_hash'] != $form_hash ) {
		$messages .= '<p class="form-error">'._('Falta la clave de control').'</p>';
		$errors++;
	}

	if(!empty($_POST['username']) && trim($_POST['username']) != $user->username) {
		if (strlen(trim($_POST['username']))<3) {
			$messages .= '<p class="form-error">'._('nombre demasiado corto').'</p>';
			$errors++;
		}

		if(!check_username($_POST['username'])) {
			$messages .= '<p class="form-error">'._('nombre de usuario erróneo, caracteres no admitidos').'</p>';
			$errors++;
		} elseif (user_exists(trim($_POST['username'])) ) {
			$messages .= '<p class="form-error">'._('el usuario ya existe').'</p>';
			$errors++;
		} else {
			$user->username=trim($_POST['username']);
		}
	}

	if($user->email != trim($_POST['email']) && !check_email(trim($_POST['email']))) {
		$messages .= '<p class="form-error">'._('el correo electrónico no es correcto').'</p>';
		$errors++;
	} elseif (trim($_POST['email']) != $current_user->user_email && email_exists(trim($_POST['email']))) {
		$messages .= '<p class="form-error">'. _('ya existe otro usuario con esa dirección de correo'). '</p>';
		$errors++;
	}
	$user->url=htmlspecialchars(clean_input_url($_POST['url']));


	$user->names=clean_text($_POST['names']);
	if(!empty($_POST['password']) || !empty($_POST['password2'])) {
		if(! check_password($_POST["password"]) ) {
			$messages .= '<p class="form-error">'._('Clave demasiado corta, debe ser de 6 o más caracteres e incluir mayúsculas, minúsculas y números').'</p>';
			$errors=1;
		} else if(trim($_POST['password']) !== trim($_POST['password2'])) {
			$messages .= '<p class="form-error">'._('las claves no son iguales, no se ha modificado').'</p>';
			$errors = 1;
		} else {
			$new_pass = trim($_POST['password']);
			$user->pass = UserAuth::hash($new_pass);
			$messages .= '<p  class="form-error">'._('La clave se ha cambiado').'</p>';
			$new_pass = true;
		}
	}

	$user->comment_pref=intval($_POST['comment_pref']) + (intval($_POST['show_friends']) & 1) * 2 + (intval($_POST['show_2cols']) & 1) * 4;

	// Manage avatars upload
	if (!empty($_FILES['image']['tmp_name']) ) {
		if(avatars_check_upload_size('image')) {
			$avatar_mtime = avatars_manage_upload($user->id, 'image');
			if (!$avatar_mtime) {
				$messages .= '<p class="form-error">'._('error guardando la imagen').'</p>';
				$errors = 1;
				$user->avatar = 0;
			} else {
				$user->avatar = $avatar_mtime;
			}
		} else {
			$messages .= '<p class="form-error">'._('el tamaño de la imagen excede el límite').'</p>';
			$errors = 1;
			$user->avatar = 0;
		}
	}

	if (!$errors) {
		if (empty($user->ip)) {
			$user->ip=$globals['user_ip'];
		}
		$user->store();
		$user->read();
		if ($current_user->user_login != $user->username ||
					$current_user->user_email != $user->email || $new_pass) {
			$current_user->Authenticate($user->username, $new_pass);
		}
		$messages .= '<p class="form-error">'._('datos actualizados').'</p>';
	}
	return $messages;
}

?>
