<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'recaptcha2.php');

$globals['ads'] = false;

// Clean return variable
if(!empty($_REQUEST['return'])) {
	$_REQUEST['return'] = clean_input_string($_REQUEST['return']);
}

if($_GET["op"] == 'logout') {
	// check the user is really authenticated (to avoid bucles due to bad caching)
	if ($current_user->user_id > 0) {
		$current_user->Logout($_REQUEST['return']);
	} else {
		header ('HTTP/1.1 303 Load');
		setcookie('return_site', '', $globals['now'] - 3600, $globals['base_url'], UserAuth::domain());
		header("Location: ".$_COOKIE['return_site'].$globals['base_url']);
		exit();
	}
}

// We need it because we modify headers
ob_start();

if ($_POST["processlogin"] == 1) {
	$globals['secure_page'] = True;
	if(!isset($_COOKIE['return_site'])) {
		$_COOKIE['return_site'] = $globals['scheme'].'//'.get_server_name();
	}
} else {
	setcookie('return_site', $globals['scheme'].'//'.get_server_name(), 0, $globals['base_url'], UserAuth::domain());
}

do_header("login");
echo '<div id="singlewrap">'."\n";
echo '<section class="section section-large text-center">';

if($_GET["op"] === 'recover' || !empty($_POST['recover'])) {
	do_recover();
} else {
	do_login();
}

echo '</section>';
echo '</div>'."\n"; // singlewrap

do_footer();

function do_login() {
	if ($post = do_login_post()) {
		list($error, $failed) = $post;
	} else {
		$error = $failed = null;
	}

	if (empty($error) && (strpos($_REQUEST['return'], '/submit') !== false)) {
		$info = _('Para enviar una historia debes ser un usuario registrado');
	} else {
		$info = null;
	}

	echo '<h1>'._('Acceder a Menéame').'</h1>';
	echo '<p class="intro">'._('Forma parte de la mayor comunidad de contenidos en español. Tú haces la portada.').'</p>';

	echo '<div class="container container-small">';
		print_oauth_icons_large($_REQUEST['return']);

		echo '<div class="separator"><b></b><span>O</span><b></b></div>';

		echo '<form method="post" class="form">';
			echo '<div class="legend">'._('Acceder con mi correo').'</div>';

			if ($error) {
				echo '<div class="response response-error">'.$error.' <span>('.$failed.')</span></div>';
			} elseif ($info) {
				echo '<div class="response response-info">'.$info.'</div>';
			}

			echo '<div class="form-group">';
				echo '<input type="text" name="username" tabindex="1" id="name" value="'.htmlspecialchars($_POST['username']).'" class="form-control" placeholder="'._('Usuario o Correo electrónico').'" required />';
			echo '</div>';

			echo '<div class="form-group">';
				echo '<input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder="'._('Contraseña').'" required />';
			echo '</div>';

			if (($failed > 2) || ($globals['captcha_first_login'] && !UserAuth::user_cookie_data()) ) {
				ts_print_form();
			}

			echo '<div class="form-group">';
				echo '<div class="checkbox"><label><input type="checkbox" name="persistent" id="remember" tabindex="3" /> '._('Recuérdame durante 30 días').'</label></div>';
			echo '</div>';

			echo '<div class="form-group">';
				echo '<button type="submit" name="login" class="btn btn-mnm btn-block" tabindex="4">'._('Acceder').'</button>';
			echo '</div>';

			echo '<input type="hidden" name="processlogin" value="1" />';
			echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'" />';
		echo '</form>';

		echo '<div class="bottomline"><a href="login?op=recover">'._('¿Has olvidado tu contraseña?').'</a></div>';
	echo '</div>';
}

function do_login_post() {
	if (empty($_POST["processlogin"])) {
		return;
	}

	global $current_user, $globals;

	$failed =  Log::get_date('login_failed', $globals['user_ip_int'], 0, 300);

	$username = clean_input_string(trim($_POST['username']));
	$password = trim($_POST['password']);

	// Check form
	if (($failed > 2 || ($globals['captcha_first_login'] && ! UserAuth::user_cookie_data())) && !ts_is_human()) {
		Log::insert('login_failed', $globals['user_ip_int'], 0);

		return array(_('el código de seguridad no es correcto'), $failed);
	}

	if (strlen($password) > 0 && $current_user->Authenticate($username, $password, $_POST['persistent']) == false) {
		Log::insert('login_failed', $globals['user_ip_int'], 0);

		$failed++;

		return array(_('usuario o email inexistente, sin validar, o clave incorrecta'), $failed);
	}

	UserAuth::check_clon_from_cookies();

	header ('HTTP/1.1 303 Load');
	setcookie('return_site', '', $globals['now'] - 3600, $globals['base_url'], UserAuth::domain());

	$url = empty($_REQUEST['return']) ? $globals['base_url'] : $_REQUEST['return'];

	header('Location: '.$_COOKIE['return_site'].$url);

	die();
}

function do_recover() {
	global $site_key, $globals;

	$post = do_recover_post();

	if ($post === true) {
		return do_recover_success();
	}

	echo '<h1>'._('Recuperación de contraseña').'</h1>';

	echo '<p class="intro">'._('Recibirás un e-mail que te permitirá editar tus datos').'</p>';

	echo '<div class="container container-small">';
		echo '<form action="'.get_auth_link().'login" method="post" class="form">';
			if (is_string($post)) {
				echo '<div class="response response-error">'.$post.'</div>';
			}

			echo '<div class="form-group">';
				echo '<input type="email" name="email" tabindex="1" id="name" value="'.htmlspecialchars($_POST['email']).'" class="form-control" placeholder="'._('Indica tu correo electrónico').'" required />';
			echo '</div>';

			ts_print_form();

			echo '<div class="form-group">';
				echo '<button type="submit" name="login" class="btn btn-block btn-mnm" tabindex="4">'._('Enviar correo').'</button>';
			echo '</div>';

			echo '<input type="hidden" name="recover" value="1" />'."\n";
			echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'" />'."\n";
		echo '</form>';

		echo '<div class="bottomline"><a href="login">'._('Volver al login').'</a></div>';
	echo '</div>';
}

function do_recover_post() {
	if (empty($_POST['recover'])) {
		return;
	}

	if (!ts_is_human()) {
		return _('el código de seguridad no es correcto');
	}

	$user = new User();

	if (!preg_match('/.+@.+\..+$/', $_POST['email'])) {
		return _('el email no es válido');
	}

	$user->email = $_POST['email'];

	if (!$user->read()) {
		return _('el email no está relacionado con ninguna cuenta');
	}

	if ($user->disabled()) {
		return _('cuenta deshabilitada');
	}

	require_once(mnminclude.'mail.php');

	if (!send_recover_mail($user, false)) {
		return _('no se ha podido enviar el correo de recuperación de contraseña');
	}

	return true;
}

function do_recover_success() {
	echo '<h1>'._('Correo enviado :)').'</h1>';
	echo '<p class="intro">'._('Si no lo recibes en la bandeja de recibidos, revisa la bandeja de SPAM').'</p>';
	echo '<div class="bottomline"><a href="login">'._('Volver al login').'</a></div>';
}

function recover_error($message) {
	echo '<div class="form-error">'.$message."</div>\n";
}
