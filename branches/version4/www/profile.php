<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//				http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'avatars.php');

// We need it because we modify headers
ob_start();

$user_levels = array ('autodisabled', 'disabled', 'normal', 'special', 'blogger', 'admin', 'god');

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

// Check user, admin and authenticated user
if ($current_user->user_id > 0 && (empty($_REQUEST['login']) || $_REQUEST['login'] == $current_user->user_login) ) {
		$login=$current_user->user_login;
} elseif (!empty($_REQUEST['login']) && $current_user->user_level == 'god') {
	$login=$db->escape($_REQUEST['login']);
	$admin_mode = true;
} else {
	if ($current_user->user_id > 0) {
		$fallback = get_user_uri($current_user->user_login);
	} else {
		$fallback = $globals['base_url'].'login.php';
	}
	header("Location: $fallback");
	die;
}

$user=new User();
$user->username = $login;
if(!$user->read()) {
	not_found();
}

$globals['ads'] = true;
if ($current_user->user_id) $globals['ads_branding'] = false;
// Enable user AdSense
// do_user_ad: 0 = noad, > 0: probability n/100
// 100 if the user is the current one
if($current_user->user_id == $user->id && $globals['external_user_ads'] && !empty($user->adcode)) {
	$globals['user_adcode'] = $user->adcode;
	$globals['user_adchannel'] = $user->adchannel;
	$globals['do_user_ad']	= 100;
}


if (isset($_POST['process'])) {
	if (!check_form_auth_ip()) {
		header("Location: http://".get_server_name().$globals['base_url']."profile.php");
		die;
	}
	$globals['secure_page'] = True;
	$messages = save_profile();
} else {
	$globals['secure_page'] = False;
	$messages = array();
}

do_header(_('edición del perfil del usuario'). ': ' . $user->username);


//echo $save_messages; // We do it later because teh profile could change header's info
//show_profile();

$form = new stdClass;
$form->hash = md5($site_key.$user->id.mnminclude);
$form->admin_mode = $admin_mode;
$form->auth_link = get_auth_link();
$form->user_levels = $user_levels;
$form->avatars_enabled = is_avatars_enabled();

Haanga::Load('profile.html', compact('user', 'form', 'messages'));


do_footer();


function save_profile() {
	global $db, $user, $current_user, $globals, $admin_mode, $site_key;
	$errors = 0; // benjami: control added (2005-12-22)
	$pass_changed=false;
	$messages = array();

	$form_hash = md5($site_key.$user->id.mnminclude);
	if(isset($_POST['disabledme']) && intval($_POST['disable']) == 1 && $_POST['form_hash'] == $form_hash && $_POST['user_id'] == $current_user->user_id ) {
		$old_user_login = $user->username;
		$old_user_id = $user->id;
		$user->disable(true);
		Log::insert('user_delete', $old_user_id, $old_user_id );
		syslog(LOG_NOTICE, "Meneame, disabling $old_user_id ($old_user_login) by $current_user->user_login -> $user->username ");
		$current_user->Logout(get_user_uri($user->username));
		die;
	}


	if(!isset($_POST['save_profile']) || !isset($_POST['process']) ||
		($_POST['user_id'] != $current_user->user_id && !$admin_mode) ) return;

	if ( empty($_POST['form_hash']) || $_POST['form_hash'] != $form_hash ) {
		array_push($messages, _('Falta la clave de control'));
		$errors++;
	}

	if(!empty($_POST['username']) && trim($_POST['username']) != $user->username) {
		$newname = trim($_POST['username']);

		if (strlen($newname)<3) {
			array_push($messages, _('nombre demasiado corto'));
			$errors++;
		}

		if(!check_username($newname)) {
			array_push($messages, _('nombre de usuario erróneo, caracteres no admitidos'));
			$errors++;
		} elseif (user_exists($newname, $user->id) ) {
			array_push($messages, _('el usuario ya existe'));
			$errors++;
		} else {
			$user->username=$newname;
		}
	}

	if($user->email != trim($_POST['email']) && !check_email(trim($_POST['email']))) {
		array_push($messages, _('el correo electrónico no es correcto'));
		$errors++;
	} elseif (!$admin_mode && trim($_POST['email']) != $current_user->user_email && email_exists(trim($_POST['email']), false)) {
		array_push($messages, _('ya existe otro usuario con esa dirección de correo'));
		$errors++;
	} else {
		$user->email=trim($_POST['email']);
	}
	$user->url=htmlspecialchars(clean_input_url($_POST['url']));


	// Check IM address
	if (!empty($_POST['public_info'])) {
		$_POST['public_info']  = htmlspecialchars(clean_input_url($_POST['public_info']));
		$public = $db->escape($_POST['public_info']);
		$im_count = intval($db->get_var("select count(*) from users where user_id != $user->id and user_level != 'disabled' and user_level != 'autodisabled' and user_public_info='$public'"));
		if ($im_count > 0) {
			array_push($messages, _('ya hay otro usuario con la misma dirección de MI, no se ha grabado'));
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
				array_push($messages, _('número telefónico erróneo, no se ha grabado'));
				$_POST['phone'] = '';
				$errors++;
			} else {
				$phone = $db->escape($_POST['phone']);
				$phone_count = intval($db->get_var("select count(*) from users where user_id != $user->id and user_level != 'disabled' and user_level != 'autodisabled' and user_phone='$phone'"));
				if ($phone_count > 0) {
					array_push($messages, _('ya hay otro usuario con el mismo número, no se ha grabado'));
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
				array_push($messages, _('código AdSense incorrecto, no se ha grabado'));
				$_POST['adcode'] = '';
				$errors++;
			} else {
				$adcode_count = intval($db->get_var("select count(*) from users where user_id != $user->id and user_level != 'disabled' and user_level != 'autodisabled' and user_adcode='".$_POST['adcode']."'"));
				if ($adcode_count > 0) {
					array_push($messages, _('ya hay otro usuario con la misma cuenta, no se ha grabado'));
					$_POST['adcode'] = '';
					$errors++;
				}
			}
		}
		if (!empty($_POST['adcode']) && !empty($_POST['adchannel']) && $user->adchannel != $_POST['adchannel']) {
			if ( !preg_match('/^[0-9]{10,12}$/', $_POST['adchannel'])) {
				array_push($messages, _('canal AdSense incorrecto, no se ha grabado'));
				$_POST['adchannel'] = '';
				$errors++;
			}
		}
		$user->adcode = $_POST['adcode'];
		$user->adchannel = $_POST['adchannel'];
	}

	$user->names=clean_text($_POST['names']);
	if(!empty($_POST['password']) || !empty($_POST['password2'])) {
		if(! check_password($_POST["password"]) ) {
			array_push($messages, _('Clave demasiado corta, debe ser de 6 o más caracteres e incluir mayúsculas, minúsculas y números'));
			$errors=1;
		} else if(trim($_POST['password']) !== trim($_POST['password2'])) {
			array_push($messages, _('las claves no son iguales, no se ha modificado'));
			$errors = 1;
		} else {
			$user->pass=md5(trim($_POST['password']));
			array_push($messages, _('La clave se ha cambiado'));
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
			$avatar_mtime = avatars_manage_upload($user->id, 'image');
			if (!$avatar_mtime) {
				array_push($messages, _('error guardando la imagen'));
				$errors = 1;
				$user->avatar = 0;
			} else {
				$user->avatar = $avatar_mtime;
			}
		} else {
			array_push($messages, _('el tamaño de la imagen excede el límite'));
			$errors = 1;
			$user->avatar = 0;
		}
	} elseif ($_POST['avatar_delete']) {
		$user->avatar = 0;
		avatars_remove($user->id);
	}
	// Reset avatar for the logged user
	if ($current_user->user_id == $user->id) $current_user->user_avatar = $user->avatar;

	if (!$errors) {
		if (empty($user->ip)) {
			$user->ip=$globals['user_ip'];
		}
		$user->store();
		$user->read();
		if (!$admin_mode && ($current_user->user_login != $user->username ||
					$current_user->user_email != $user->email || $pass_changed)) {
			$current_user->Authenticate($user->username, $user->pass);
		}
		array_push($messages, _('datos actualizados'));
	}
	return $messages;
}

?>
