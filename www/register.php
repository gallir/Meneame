<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include_once(mnminclude.'html1.php');
include_once(mnminclude.'recaptcha2.php');
include_once(mnminclude.'ban.php');

$globals['ads'] = false;

// Clean return variable
if(!empty($_REQUEST['return'])) {
	$_REQUEST['return'] = clean_input_string($_REQUEST['return']);
}

if ($current_user->user_id > 0) {
	if(!isset($_COOKIE['return_site'])) {
		$_COOKIE['return_site'] = get_server_name();
	}
	header("Location: http://".$_COOKIE['return_site'].get_user_uri($current_user->user_login));
	die;
}

if(isset($_POST["process"])) {
	$globals['secure_page'] = True;
} else {
	setcookie('return_site', get_server_name(), 0, $globals['base_url'], UserAuth::domain());
}

do_header(_("registro"), "post");

echo '<section class="section section-large text-center">'."\n";

if(isset($_POST["process"])) {
	switch (intval($_POST["process"])) {
		case 1:
			do_register1();
			break;
		case 2:
			do_register2();
			break;
	}
} else {
	do_register0();
}

echo '</section>' . "\n";
do_footer();
exit;

function do_register0() {
	echo '<h1>'._('Únete a Menéame').'</h1>';
	echo '<p class="intro">'._('Forma parte de la mayor comunidad de contenidos en español. Tú haces la portada.').'</p>';

	echo '<div class="container container-small">';
		print_oauth_icons_large($_REQUEST['return']);

		echo '<div class="separator"><b></b><span>O</span><b></b></div>';

		echo '<form id="form-register" method="post" class="form">';
			echo '<div class="legend">'._('Registrarme con mi correo').'</div>';

			echo '<div class="form-group input-validate">';
				echo '<span class="input-status fa"></span>';
				echo '<input type="text" name="username" tabindex="1" id="name" value="'.htmlspecialchars($_POST['username']).'" class="form-control" placeholder="'._('Nombre de usuario').'" required />';
			echo '</div>';

			echo '<div class="form-group input-validate">';
				echo '<span class="input-status fa"></span>';
				echo '<input type="email" name="email" tabindex="2" id="email" value="'.htmlspecialchars($_POST['email']).'" class="form-control" placeholder="'._('Correo electrónico').'" required />';
			echo '</div>';

			echo '<div class="form-group input-validate">';
				echo '<span class="input-status fa"></span>';
				echo '<a href="#" class="input-password-show"><i class="fa fa-eye"></i></a>';
				echo '<input type="password" name="password" id="password" tabindex="3" class="form-control" placeholder="'._('Contraseña').'" required />';
				echo '<div class="input-info">'._('Al menos ocho caracteres, incluyendo mayúsculas, minúsculas y números').'</div>';
			echo '</div>';

			echo '<div class="form-group">';
				echo '<button type="submit" name="login" class="btn btn-block btn-mnm" tabindex="4">'._('Crear usuario').'</button>';
			echo '</div>';

			echo '<input type="hidden" name="process" value="1" />';
			echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'" />';

			echo '<div class="bottomline">'._('Cuando te registras aceptas las <a href="/legal" target="_blank">Condiciones de Uso, Política de Privacidad y el Uso de Cookies</a>').'.</div>';
		echo '</form>';
	echo '</div>';
}

function do_register1() {
	if ($error = check_register_error()) {
		return do_register_error($error);
	}

	echo '<h1>'._('Prevención de bots').'</h1>';

	echo '<div class="container container-small">';
		echo '<form id="form-register" method="post" class="form">';
			ts_print_form();

			echo '<div class="form-group">';
				echo '<button type="submit" name="submit" class="btn btn-block btn-mnm" tabindex="2">'._('Continuar').'</button>';
			echo '</div>';

			echo '<input type="hidden" name="process" value="2" />';
			echo '<input type="hidden" name="email" value="'.htmlspecialchars($_POST['email']).'" />';
			echo '<input type="hidden" name="username" value="'.htmlspecialchars($_POST['username']).'" />';
			echo '<input type="hidden" name="password" value="'.htmlspecialchars($_POST['password']).'" />';

			echo '<div class="bottomline">'._('Cuando te registras aceptas las <a href="/legal" target="_blank">Condiciones de Uso, Política de Privacidad y el Uso de Cookies</a>').'.</div>';
		echo '</form>';
	echo '</div>';
}

function do_register2() {
	if (!ts_is_human()) {
		return do_register_error(_('el código de seguridad no es correcto'));
	}

	if ($error = check_register_error()) {
		return do_register_error($error);
	}

	// Extra check
	if (!check_security_key($_POST['base_key'])) {
		return do_register_error(_('código incorrecto o pasó demasiado tiempo'));
	}

	$username = clean_input_string(trim($_POST['username'])); // sanity check

	if (user_exists($username)) {
		return do_register_error(_('el usuario ya existe'));
	}

	global $db, $globals;

	$dbusername = $db->escape($username); // sanity check
	$password = UserAuth::hash(trim($_POST['password']));
	$email = clean_input_string(trim($_POST['email'])); // sanity check
	$dbemail = $db->escape($email); // sanity check
	$user_ip = $globals['user_ip'];

	if (!$db->query("INSERT INTO users (user_login, user_login_register, user_email, user_email_register, user_pass, user_date, user_ip) VALUES ('$dbusername', '$dbusername', '$dbemail', '$dbemail', '$password', now(), '$user_ip')")) {
		return do_register_error(_('el usuario no ha podido ser registrado en la base de datos'));
	}

	$user = new User();
	$user->username = $username;

	if (!$user->read()) {
		return do_register_error(_('el usuario no ha podido ser registrado en la base de datos'));
	}

	require_once(mnminclude.'mail.php');

	if (!send_recover_mail($user, false)) {
		return do_register_error(_('error enviando el correo electrónico, seguramente está bloqueado'));
	}

	$globals['user_ip'] = $user_ip; //we force to insert de log with the same IP as the form

	Log::insert('user_new', $user->id, $user->id);

	syslog(LOG_INFO, "new user $user->id $user->username $email $user_ip");

	echo '<h1>'._('¡Ya estás dentro!').'</h1>';

	echo '<div class="container container-small">';
		echo '<hr />';

		echo '<div class="legend">'._('Tienes un correo electrónico').'</div>';

		echo '<div class="row">';
			echo '<div class="col-xs-3">';
				echo '<i class="result-final-icon result-final-icon-success fa fa-check-circle-o"></i>';
			echo '</div>';

			echo '<div class="col-xs-9 text-left">';
				echo '<p class="text-large">'.__('Revisa tu correo, allí estarán las instrucciones para comenzar a participar de forma activa en la comunidad.').'</p>';
				echo '<p class="text-large">'.__('¡Ah! Si no lo ves, échale un vistazo a la carpeta de SPAM, a veces pasa.').'</p>';
			echo '</div>';
		echo '</div>';

		echo '<a href="/" class="btn btn-block btn-mnm">'._('Ir a la portada').'</a>';
	echo '</div>';
}

function check_register_error() {
	if (check_ban_proxy()) {
		return _("IP no permitida");
	}

	if (!isset($_POST["username"]) || strlen($_POST["username"]) < 3) {
		return _("nombre de usuario erróneo, debe ser de 3 o más caracteres alfanuméricos");
	}

	if (!check_username($_POST["username"])) {
		return _("nombre de usuario erróneo, caracteres no admitidos o no comienzan con una letra");
	}

	if (user_exists(trim($_POST["username"])) ) {
		return _("el usuario ya existe");
	}

	if (!check_email(trim($_POST["email"]))) {
		return _("el correo electrónico no es correcto");
	}

	if (email_exists(trim($_POST["email"])) ) {
		return _("dirección de correo duplicada, o fue usada recientemente");
	}

	if (preg_match('/[ \']/', $_POST["password"])) {
		return _("caracteres inválidos en la clave");
	}

	if (!check_password($_POST["password"])) {
		return _("clave demasiado corta, debe ser de 8 o más caracteres e incluir mayúsculas, minúsculas y números");
	}

	global $globals, $db;

	if (empty($globals['skip_ip_register'])) {
		return;
	}

	// Check registers from the same IP network
	$user_ip = $globals['user_ip'];
	$ip_classes = explode(".", $user_ip);

	// From the same IP
	$registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 24 hour) and log_type in ('user_new', 'user_delete') and log_ip = '$user_ip'");

	if ($registered) {
		syslog(LOG_NOTICE, "Meneame, register not accepted by IP address ($_POST[username]) $user_ip");

		return _("para registrar otro usuario desde la misma dirección debes esperar 24 horas");
	}

	// Check class
	// nnn.nnn.nnn
	$ip_class = $ip_classes[0] . '.' . $ip_classes[1] . '.' . $ip_classes[2] . '.%';
	$registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 6 hour) and log_type in ('user_new', 'user_delete') and log_ip like '$ip_class'");

	if ($registered) {
		syslog(LOG_NOTICE, "Meneame, register not accepted by IP class ($_POST[username]) $ip_class");

		return _("para registrar otro usuario desde la misma red debes esperar 6 horas"). " ($ip_class)";
	}

	// Check class
	// nnn.nnn
	$ip_class = $ip_classes[0] . '.' . $ip_classes[1] . '.%';
	$registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 1 hour) and log_type in ('user_new', 'user_delete') and log_ip like '$ip_class'");

	if ($registered > 2) {
		syslog(LOG_NOTICE, "Meneame, register not accepted by IP class ($_POST[username]) $ip_class");

		return _("para registrar otro usuario desde la misma red debes esperar unos minutos") . " ($ip_class)";
	}
}

function do_register_error($message, $back) {
	echo '<h1>'._('Error en el registro').'</h1>';

	echo '<div class="container container-small">';
		echo '<hr />';

		echo '<div class="row">';
			echo '<div class="col-xs-3">';
				echo '<i class="result-final-icon result-final-icon-error fa fa-times"></i>';
			echo '</div>';

			echo '<div class="col-xs-9 text-left">';
				echo '<div class="legend">'._('¡Vaya! Algo malo ha ocurrido').'</div>';
				echo '<p class="text-large">'.$message.'</p>';
			echo '</div>';
		echo '</div>';

		echo '<a href="'.$back.'" class="btn btn-block btn-mnm">'._('Volver al registro').'</a>';
	echo '</div>';
}
