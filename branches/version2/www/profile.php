<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'user.php');
include(mnminclude.'avatars.php');

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
			$current_user->Authenticate($user->username, $user->pass);
			header('Location: '.get_user_uri($user->username));
			die;
		}
	}
}
//// End recovery

if ($current_user->user_id > 0 && $current_user->authenticated && empty($_REQUEST['login'])) {
		$login=$current_user->user_login;
} elseif (!empty($_REQUEST['login']) && $current_user->user_level == 'god') {
	$login=$db->escape($_REQUEST['login']);
	$admin_mode = true;
} else {
	header("Location: ./login.php");
	die;
}

$user=new User();
$user->username = $login;
if(!$user->read()) {
	not_found();
}

$globals['ads'] = true;
// Enable user AdSense
// do_user_ad: 0 = noad, > 0: probability n/100
// 100 if the user is the current one
if($current_user->user_id == $user->id && $globals['external_user_ads'] && !empty($user->adcode)) {
	$globals['user_adcode'] = $user->adcode;
	$globals['user_adchannel'] = $user->adchannel;
	$globals['do_user_ad']  = 100;
}


do_header(_('edición del perfil del usuario'). ': ' . $user->username);
do_banner_top();
echo '<div id="container-wide">' . "\n";
show_profile();

do_footer();


function show_profile() {
	global $user, $admin_mode, $user_levels, $globals, $site_key, $current_user;


	save_profile();
	
	echo '<div id="genericform-contents"><div id="genericform">';
	echo '<form  enctype="multipart/form-data" action="profile.php" method="post" id="thisform" AUTOCOMPLETE="off">';
	echo '<fieldset><legend>';
	echo '<span class="sign">'._('opciones de usuario') . " <a href='".get_user_uri($user->username)."'>$user->username</a>: $user->level</span></legend>";

	echo '<img class="thumbnail" src="'.$globals['base_url'] . 'backend/get_avatar.php?id='.$user->id.'&amp;size=80&amp;t='.time().'" width="80" height="80" alt="'.$user->username.'" />';
	echo '<input type="hidden" name="process" value="1" />';
	echo '<input type="hidden" name="user_id" value="'.$user->id.'" />';
	echo '<input type="hidden" name="form_hash" value="'. md5($site_key.$user->id.$globals['user_ip']) .'" />';
	if ($admin_mode)
		echo '<input type="hidden" name="login" value="'.$user->username.'" />';

	echo '<p class="l-top"><label>'._('usuario').':</label><br/>';
	echo '<input type="text" autocomplete="off" name="username" id="username" value="'.$user->username.'" onkeyup="enablebutton(this.form.checkbutton1, null, this)" />';
	echo '&nbsp;&nbsp;<span id="checkit"><input type="button" id="checkbutton1" disabled="disabled" value="'._('verificar').'" onclick="checkfield(\'username\', this.form, this.form.username)"/></span>';
	echo '<br/><span id="usernamecheckitvalue"></span>' . "\n";
	echo '</p>';

	echo '<p class="l-top"><label>'._('nombre real').':</label><br/>';
	echo '<input type="text" autocomplete="off" name="names" id="names" value="'.$user->names.'" />';
	echo '</p>';

	echo '<p><label>'._('correo electrónico').':</label><br/>';
	echo '<input type="text" autocomplete="off" name="email" id="email" value="'.$user->email.'" onkeyup="enablebutton(this.form.checkbutton2, null, this)"/>';
	echo '&nbsp;&nbsp;<input type="button"  id="checkbutton2" disabled="disabled" value="'._('verificar').'" onclick="checkfield(\'email\', this.form, this.form.email)"/>';
	echo '<br/><span id="emailcheckitvalue"></span>';
	echo '</p>';

	echo '<p><label>'._('página web').':</label><br/>';
	echo '<input type="text" autocomplete="off" name="url" id="url" value="'.$user->url.'" />';
	echo '</p>';

	echo '<p><label>'._('mensajero instantáneo público, invisible para los demás').':</label><br/>';
	echo '<span class="genericformnote">' . _('necesario si te conectarás vía Jabber/Google Talk') . '</span><br/>';
	echo '<input type="text" autocomplete="off" name="public_info" id="public_info" value="'.$user->public_info.'" />';
	echo '</p>';

	if ($user->id  == $current_user->user_id) {
		echo '<p><label>'._('teléfono móvil').':</label><br/>';
		echo '<span class="genericformnote">' . _('sólo necesario si enviarás notas al nótame vía SMS') . '</span><br/>';
		echo '<span class="genericformnote">' . _('pon el número completo, con código de país: +34123456789') . '</span><br/>';
		echo '<input type="text" autocomplete="off" name="phone" id="phone" value="'.$user->phone.'" />';
		echo '</p>';
	}



	if ($globals['external_user_ads']) {
		echo '<p><label for="adcode">'._('codigo AdSense').':</label><br/>';
		echo '<span class="genericformnote">' . _('tu código de usuario de AdSense, del tipo pub-123456789') . '</span><br/>';
		echo '<input type="text" autocomplete="off" name="adcode" id="adcode" maxlength="20" value="'.$user->adcode.'" /><br />';
		echo '<span class="genericformnote">' . _('canal AdSense (opcional), del tipo 1234567890') . '</span><br/>';
		echo '<input type="text" autocomplete="off" name="adchannel" id="adchannel" maxlength="12" value="'.$user->adchannel.'" />';
		echo '</p>';
	}


	if (is_avatars_enabled()) {
		echo '<input type="hidden" name="MAX_FILE_SIZE" value="300000" />';
		echo '<p><label>'._('avatar').':</label><br/>';
		echo '<span class="genericformnote">' . _('el avatar debe ser una imagen cuadrada en jpeg, gif o png de no más de 100 KB, sin transparencias') . '</span><br/>';
		echo '<input type="file" autocomplete="off" name="image" />';
		echo '</p>';
	}

	echo '<fieldset><legend>'._('opciones de visualización') . '</legend>';
	echo '<p>'._('mostrar todos los comentarios').':&nbsp;';
	print_checkbox('comment_pref', $user->comment_pref & 1);
	echo '</p>';


	echo '<p>'._('mostrar sólo noticias amigos por defecto').':&nbsp;';
	print_checkbox('show_friends', $user->comment_pref & 2);
	echo '</p>';

	/*
	echo '<p>'._('mostrar sólo 2 columnas').':&nbsp;';
	print_checkbox('show_2cols', $user->comment_pref & 4);
	echo '</p>';
	*/

	echo '</fieldset>';


	
	echo '<p>'._('introduce la nueva clave para cambiarla -no se cambiará si la dejas en blanco-:').'</p>';

	echo '<p><label for="password">' . _("clave") . ':</label><br />' . "\n";
	echo '<input type="password" autocomplete="off" id="password" name="password" size="25" /></p>' . "\n";

	echo '<p><label for="verify">' . _("repite la clave") . ': </label><br />' . "\n";
	echo '<input type="password" autocomplete="off" id="verify" name="password2" size="25" /></p>' . "\n";

	if ($admin_mode) {
		echo '<p><label for="verify">' . _("estado") . ': </label><br />' . "\n";
		echo '<select name="user_level">';
		foreach ($user_levels as $level) {
			echo '<option value="'.$level.'"';
			if ($user->level == $level) echo ' selected="selected"';
			echo '>'.$level.'</option>';
		}
		echo '</select>';

		echo '<p><label for="karma">'._('karma').':</label><br/>';
		echo '<input type="text" autocomplete="off" name="karma" id="karma" value="'.$user->karma.'" />';
		echo '</p>';

	}
	
	echo '<p class="l-bottom"><input type="submit" name="save_profile" value="'._('actualizar').'" class="genericsubmit" /></p>';
	echo '</fieldset>';

	// Disable the account
	if ($user->id  == $current_user->user_id) {
		echo '<br/><fieldset><legend>'._('deshabilitar la cuenta') . '</legend>';
		echo '<p>'._('atención! la cuenta será deshabilitada.').'</p>';
		echo '<p class="genericformnote">'._('se eliminarán automáticamente los datos personales.').'<br/>';
		echo _('las notas serán eliminadas, los envíos y comentarios NO se borrarán.').'</p>';
		echo '<p>'._('sí, quiero deshabilitarla').': <input  name="disable" type="checkbox" value="1"/>';
		echo '</p>';
		echo '<p class="l-bottom"><input type="submit" name="disabledme" value="'._('deshabilitar cuenta').'" class="genericsubmit" /></p>';
		echo '</fieldset>';
	}


	echo "</form></div></div>\n";
	
}


function save_profile() {
	global $db, $user, $current_user, $globals, $admin_mode, $site_key;
	$errors = 0; // benjami: control added (2005-12-22)
	$pass_changed=false;
	
	$form_hash = md5($site_key.$user->id.$globals['user_ip']);
	if(isset($_POST['disabledme']) && intval($_POST['disable']) == 1 && $_POST['form_hash'] == $form_hash && $_POST['user_id'] == $current_user->user_id ) {
		$old_user_login = $user->username;
		$old_user_id = $user->id;
		$db->query("delete from posts where post_user_id = $old_user_id");
		$user->disable();
		syslog(LOG_NOTICE, "Meneame, disabling $old_user_id ($old_user_login) by $current_user->user_login -> $user->username ".time());
		$current_user->Logout(get_user_uri($user->username));
		die;
	}


	if(!isset($_POST['save_profile']) || !isset($_POST['process']) || 
		($_POST['user_id'] != $current_user->user_id && !$admin_mode) ) return;
		
	if ( empty($_POST['form_hash']) || $_POST['form_hash'] != $form_hash ) {
		echo '<p class="form-error">'._('Falta la clave de control').'</p>';
		$errors++;
	}

	if(!empty($_POST['username']) && trim($_POST['username']) != $user->username) {
		if (strlen(trim($_POST['username']))<3) {
			echo '<p class="form-error">'._('nombre demasiado corto').'</p>';
			$errors++;
		}

		if(!check_username($_POST['username'])) {
			echo '<p class="form-error">'._('nombre de usuario erróneo, caracteres no admitidos').'</p>';
			$errors++;
		} elseif (user_exists(trim($_POST['username'])) ) {
			echo '<p class="form-error">'._('el usuario ya existe').'</p>';
			$errors++;
		} else {
			$user->username=trim($_POST['username']);
		}
	}
	
	if($user->email != trim($_POST['email']) && !check_email(trim($_POST['email']))) {
		echo '<p class="form-error">'._('el correo electrónico no es correcto').'</p>';
		$errors++;
	} elseif (!$admin_mode && trim($_POST['email']) != $current_user->user_email && email_exists(trim($_POST['email']))) {
		echo '<p class="form-error">'. _('ya existe otro usuario con esa dirección de correo'). '</p>';
		$errors++;
	} else {
		$user->email=trim($_POST['email']);
	}
	$user->url=htmlspecialchars(clean_input_url($_POST['url']));


	// Check IM address
	if (!empty($_POST['public_info'])) {
		$_POST['public_info']  = htmlspecialchars(clean_input_url($_POST['public_info']));
		$public = $db->escape($_POST['public_info']);
		$im_count = intval($db->get_var("select count(*) from users where user_id != $user->id and user_level != 'disabled' and user_public_info='$public'"));
		if ($im_count > 0) {
			echo '<p class="form-error">'. _('ya hay otro usuario con la misma dirección de MI, no se ha grabado'). '</p>';
			$_POST['public_info'] = '';
			$errors++;
		}
	}
		$user->phone = $_POST['phone'];
		$user->public_info=htmlspecialchars(clean_input_url($_POST['public_info']));
	// End check IM address

	if ($user->id  == $current_user->user_id) {
		// Check phone number
		if (!empty($_POST['phone'])) {
			if ( !preg_match('/^\+[0-9]{9,16}$/', $_POST['phone'])) {
				echo '<p class="form-error">'. _('número telefónico erróneo, no se ha grabado'). '</p>';
				$_POST['phone'] = '';
				$errors++;
			} else {
				$phone = $db->escape($_POST['phone']);
				$phone_count = intval($db->get_var("select count(*) from users where user_id != $user->id and user_level != 'disabled' and user_phone='$phone'"));
				if ($phone_count > 0) {
					echo '<p class="form-error">'. _('ya hay otro usuario con el mismo número, no se ha grabado'). '</p>';
					$_POST['phone'] = '';
					$errors++;
				}
			}
		}
		$user->phone = $_POST['phone'];
		// End check phone number
	}

	// Verifies adsense code
	if ($globals['external_user_ads']) {
		$_POST['adcode'] = trim($_POST['adcode']);
		$_POST['adchannel'] = trim($_POST['adchannel']);
		if (!empty($_POST['adcode']) && $user->adcode != $_POST['adcode']) {
			if ( !preg_match('/^pub-[0-9]{16}$/', $_POST['adcode'])) {
				echo '<p class="form-error">'. _('código AdSense incorrecto, no se ha grabado'). '</p>';
				$_POST['adcode'] = '';
				$errors++;
			} else {
				$adcode_count = intval($db->get_var("select count(*) from users where user_id != $user->id and user_level != 'disabled' and user_adcode='".$_POST['adcode']."'"));
				if ($adcode_count > 0) {
					echo '<p class="form-error">'. _('ya hay otro usuario con la misma cuenta, no se ha grabado'). '</p>';
					$_POST['adcode'] = '';
					$errors++;
				}
			}
		}
		if (!empty($_POST['adcode']) && !empty($_POST['adchannel']) && $user->adchannel != $_POST['adchannel']) {
			if ( !preg_match('/^[0-9]{10,12}$/', $_POST['adchannel'])) {
				echo '<p class="form-error">'. _('canal AdSense incorrecto, no se ha grabado'). '</p>';
				$_POST['adchannel'] = '';
				$errors++;
			}
		}
		$user->adcode = $_POST['adcode'];
		$user->adchannel = $_POST['adchannel'];
	}

	$user->names=clean_text($_POST['names']);
	if(!empty($_POST['password']) || !empty($_POST['password2'])) {
		if(trim($_POST['password']) !== trim($_POST['password2'])) {
			echo '<p class="form-error">'._('las claves no son iguales, no se ha modificado').'</p>';
			$errors = 1;
		} else {
			$user->pass=md5(trim($_POST['password']));
			echo '<p>'._('La clave se ha cambiado').'</p>';
			$pass_changed = true;
		}
	}
	if ($admin_mode && !empty($_POST['user_level'])) {
		$user->level=$db->escape($_POST['user_level']);
	}
	if ($admin_mode && !empty($_POST['karma']) && is_numeric($_POST['karma']) && $_POST['karma'] > 4 && $_POST['karma'] <= 20) {
		$user->karma=$_POST['karma'];
	}

	$user->comment_pref=intval($_POST['comment_pref']) + (intval($_POST['show_friends']) & 1) * 2 + (intval($_POST['show_2cols']) & 1) * 4;

	// Manage avatars upload
	if (!empty($_FILES['image']['tmp_name']) ) {
		if(avatars_check_upload_size('image')) {
			if (!avatars_manage_upload($user->id, 'image')) {
				echo '<p class="form-error">'._('error guardando la imagen').'</p>';
				$errors = 1;
				$user->avatar = 0;
			} else {
				$user->avatar = 1;
			}
		} else {
			echo '<p class="form-error">'._('el tamaño de la imagen excede el límite').'</p>';
			$errors = 1;
			$user->avatar = 0;
		}
	}

	if (!$errors) { // benjami: "if" added (2005-12-22)
		if (empty($user->ip)) {
			$user->ip=$globals['user_ip'];
		}
		$user->store();
		$user->read();
		if (!$admin_mode && ($current_user->user_login != $user->username || 
					$current_user->user_email != $user->email || $pass_changed)) {
			$current_user->Authenticate($user->username, $user->pass);
		}
		echo '<p class="form-act">'._('datos actualizados').'</p>';
	}
}

function print_checkbox($name, $current_value) {
	echo '<input  name="'.$name.'" type="checkbox" value="1"'; 
	if ($current_value > 0) echo '  checked="true"';
	echo '/>';
}

?>
