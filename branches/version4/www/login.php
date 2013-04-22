<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'ts.php');

include(mnminclude.'ban.php');
check_ip_noaccess();

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
		header("Location: http://".$_COOKIE['return_site'].$globals['base_url']);
		exit();
	}
}

// We need it because we modify headers
ob_start();

if ($_POST["processlogin"] == 1) {
	$globals['secure_page'] = True;
	if(!isset($_COOKIE['return_site'])) {
		$_COOKIE['return_site'] = get_server_name();
	}
} else {
	setcookie('return_site', get_server_name(), 0, $globals['base_url'], UserAuth::domain());
}

do_header("login");
echo '<div id="singlewrap">' . "\n";

if($_GET["op"] === 'recover' || !empty($_POST['recover'])) {
	do_recover();
} else {
	do_login();
}

echo '</div>'."\n"; // singlewrap

do_footer();


function do_login() {
	global $current_user, $globals;

	$form_ip_check = check_form_auth_ip();
	$previous_login_failed =  Log::get_date('login_failed', $globals['form_user_ip_int'], 0, 300);

	// Show menéame intro only if first try and the there were not previous logins
	if (! $globals['mobile'] && $previous_login_failed < 3 && empty($_POST["processlogin"]) && empty($_COOKIE['u'])) {
		echo '<div class="faq" style="float:right; width:55%; margin-top: 10px;">'."\n";
		// Only prints if the user was redirected from submit.php
		if (!empty($_REQUEST['return']) && preg_match('/submit\.php/', $_REQUEST['return'])) {
			echo '<p style="border:1px solid #FF9400; font-size:1.3em; background:#FEFBEA; font-weight:bold; padding:0.5em 1em;">Para enviar una historia debes ser un usuario registrado</p>'."\n";
		}
		echo '<h3>'._('¿Qué es menéame?').'</h3>'."\n";
		echo '<p>'._('Es un sitio que te permite enviar una historia que será revisada por todos y será promovida, o no, a la página principal. Cuando un usuario envía una historia ésta queda en la <a href="shakeit.php">cola de pendientes</a> hasta que reúne los votos suficientes para ser promovida a la página principal').'.</p>'."\n";

		echo '<h3>'._('¿Todavía no eres usuario de menéame?').'</h3>'."\n";
		echo '<p>'._('Como usuario registrado podrás, entre otras cosas').':</p>'."\n";
		echo '<ul style="margin-left: 1.5em">'."\n";
		echo '<li>'."\n";
		echo '<strong>'._('Enviar historias').'</strong><br />'."\n";
		echo '<p>'._('Una vez registrado puedes enviar las historias que consideres interesantes para la comunidad. Si tienes algún tipo de duda sobre que tipo de historias puedes enviar revisa nuestras <a href="faq-es.php">preguntas frecuentes sobre menéame</a>').'.</p>'."\n";
		echo '</li>'."\n";
		echo '<li>'."\n";
		echo '<strong>'._('Escribir comentarios').'</strong><br />'."\n";
		echo '<p>'._('Puedes escribir tu opinión sobre las historias enviadas a menéame mediante comentarios de texto. También puedes votar positivamente aquellos comentarios ingeniosos, divertidos o interesantes y negativamente aquellos que consideres inoportunos').'.</p>'."\n";
		echo '</li>'."\n";
		echo '<li>'."\n";
		echo '<strong>'._('Perfil de usuario').'</strong><br />'."\n";
		echo '<p>'._('Toda tu información como usuario está disponible desde la página de tu perfil. También puedes subir una imagen que representará a tu usuario en menéame. Incluso es posible compartir los ingresos publicitarios de Menéame, solo tienes que introducir el código de tu cuenta Google Adsense desde tu perfil').'.</p>'."\n";
		echo '</li>'."\n";
		echo '<li>'."\n";
		echo '<strong>'._('Chatear en tiempo real desde la fisgona').'</strong><br />'."\n";
		echo '<p>'._('Gracias a la <a href="sneak.php">fisgona</a> puedes ver en tiempo real toda la actividad de menéame. Además como usuario registrado podrás chatear con mucha más gente de la comunidad menéame').'</p>'."\n";
		echo '</li>'."\n";
		echo '</ul>'."\n";
		echo '<h3><a href="register.php" style="color:#FF6400; text-decoration:underline; display:block; width:8em; text-align:center; margin:0 auto; padding:0.5em 1em; border:3px double #FFE2C5; background:#FFF3E8;">Regístrate ahora</a></h3>'."\n";
		echo '</div>'."\n";

		echo '<div class="genericform" style="float:left; width:40%; margin: 0">'."\n";
	} else {
		echo '<div class="genericform" style="float:auto;">'."\n";
	}
	echo '<form action="'.get_auth_link().'login.php" id="thisform" method="post">'."\n";

	if($_POST["processlogin"] == 1) {
		// Check the IP, otherwise redirect
		if (!$form_ip_check) {
			header ('HTTP/1.1 303 Load');
			header("Location: http://".$_COOKIE['return_site'].$globals['base_url']."login.php");
			die;
		}

		$username = clean_input_string(trim($_POST['username']));
		$password = trim($_POST['password']);

		// Check form
		if (($previous_login_failed > 2 || ($globals['captcha_first_login'] == true && ! UserAuth::user_cookie_data()) )
				&& !ts_is_human()) {
			Log::insert('login_failed', $globals['form_user_ip_int'], 0);
			recover_error(_('el código de seguridad no es correcto'). " ($previous_login_failed)");
		} elseif ($current_user->Authenticate($username, md5($password), $_POST['persistent']) == false) {
			Log::insert('login_failed', $globals['form_user_ip_int'], 0);
			$previous_login_failed++;
			recover_error(_('usuario o email inexistente, sin validar, o clave incorrecta'). " ($previous_login_failed)");
		} else {
			UserAuth::check_clon_from_cookies();

			// If the user is authenticating from a mobile device, keep her in the standard version
			if ($globals['mobile']) {
				setcookie('nomobile', '1', 0, $globals['base_url'], UserAuth::domain());
			}

			header ('HTTP/1.1 303 Load');
			if(!empty($_REQUEST['return'])) {
				header('Location: http://'.$_COOKIE['return_site'].$_REQUEST['return']);
 			} else {
				header('Location: http://'.$_COOKIE['return_site'].$globals['base_url']);
			}
			die;
		}
	}
	echo '<fieldset>'."\n";
	echo '<legend><span class="sign">login</span></legend>'."\n";
	echo '<p><label for="name">'._('usuario o email').':</label><br />'."\n";
	echo '<input type="text" name="username" size="25" tabindex="1" id="name" value="'.htmlentities($username).'" /></p>'."\n";
	echo '<p><label for="password">'._('clave').':</label><br />'."\n";
	echo '<input type="password" name="password" id="password" size="25" tabindex="2"/></p>'."\n";
	echo '<p><label for="remember">'._('recuérdame').': </label><input type="checkbox" name="persistent" id="remember" tabindex="3"/></p>'."\n";

	// Print captcha
	if ($previous_login_failed > 2 || ($globals['captcha_first_login'] == true && ! UserAuth::user_cookie_data()) ) {
		ts_print_form();
	}

	get_form_auth_ip();

	echo '<p><input type="submit" value="login" class="button" tabindex="4" /></p>'."\n";

	echo '<div style="text-align:center">';
	print_oauth_icons($_REQUEST['return']);
	echo '</div>'."\n";

	echo '<input type="hidden" name="processlogin" value="1"/>'."\n";
	echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'"/>'."\n";
	echo '</fieldset>'. "\n";
	echo '</form>'."\n";
	echo '<div class="recoverpass" style="text-align:center"><h4><a href="login.php?op=recover">'._('¿has olvidado la contraseña?').'</a></h4></div>'."\n";
	echo '</div>'."\n";
	echo '<br/>&nbsp;';
}

function do_recover() {
	global $site_key, $globals;

	echo '<div class="genericform">'."\n";
	echo '<fieldset>'."\n";
	echo '<legend><span class="sign">'._("recuperación de contraseñas").'</span></legend>'."\n";

	if(!empty($_POST['recover'])) {
		if (!ts_is_human()) {
			recover_error(_('el código de seguridad no es correcto'));
		} else {
			$error = false;
			$user=new User();
			if (preg_match('/.+@.+\..+$/', $_POST['email'])) {
				// It's an email address
				$user->email=$_POST['email'];
			} else {
				recover_error(_('el email no es válido'));
				$error = true;
			}

			if(!$error && !$user->read()) {
				recover_error(_('el email no está relacionado con ninguna cuenta'));
				$error = true;
			}
			if(!$error && $user->disabled()) {
				recover_error(_('cuenta deshabilitada'));
				$error = true;
			}
			if (!$error) {
				require_once(mnminclude.'mail.php');
				$sent = send_recover_mail($user);
			}
		}
	}
	if (!$sent) {
		echo '<form action="login.php" id="thisform-recover" method="post">'."\n";
		echo '<label for="name" style="font-size:120%">'._('indica el email de la cuenta').':</label><br />'."\n";
		echo '<input type="text" name="email" size="25" tabindex="1" id="name" value="'.htmlspecialchars($_POST['email']).'" />'."\n";
		echo '<p>'._('(recibirás un e-mail que te permitirá editar tus datos)').'</p>&nbsp;<br/>';
		echo '<input type="hidden" name="recover" value="1"/>'."\n";
		echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'"/>'."\n";
		ts_print_form();
		echo '<br /><input type="submit" value="'._('recibir e-mail').'" class="button" />'."\n";
		echo '</form>'."\n";
	}
	echo '</fieldset>'."\n";
	echo '</div>'."\n";
}

function recover_error($message) {
	echo '<div class="form-error">';
	echo "$message";
	echo "</div>\n";
}

?>
