<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1-mobile.php');
include(mnminclude.'ts.php');
include(mnminclude.'log.php');

$globals['ads'] = false;
// We use the original IP to avoid cheating by httheaders
$globals['original_user_ip_int'] = sprintf("%u", ip2long($_SERVER["REMOTE_ADDR"]));

// Clean return variable
if(!empty($_REQUEST['return']))
	$_REQUEST['return'] = clean_input_string($_REQUEST['return']);

if($_GET["op"] === 'logout') {
	$current_user->Logout($_REQUEST['return']);
}

// We need it because we modify headers
ob_start();
if ($_POST["processlogin"] == 1) {
	$globals['secure_page'] = True;
}


do_header("login");
echo '<div id="singlewrap">' . "\n";


if($_GET["op"] === 'recover' || !empty($_POST['recover'])) {
	do_recover();
} else {
	do_login();
}

echo '</div>'."\n";

do_footer();


function do_login() {
	global $current_user, $globals;

	$form_ip_check = check_form_auth_ip();
	$previous_login_failed =  log_get_date('login_failed', $globals['form_user_ip_int'], 0, 300);

	echo '<form action="'.get_auth_link().'login.php" id="xxxthisform" method="post">'."\n";
	
	if ($_POST["processlogin"] == 1) {
		// Check the IP, otherwise redirect
		if (!$form_ip_check) {
			header("Location: http://".get_server_name().$globals['base_url']."login.php");
       		die;
		}
		$username = clean_input_string(trim($_POST['username']));
		$password = trim($_POST['password']);
		if ($_POST['persistent']) {
			$persistent = 3600000; // 1000 hours
		} else {
			$persistent = 0;
		}

		// Check form
		if (($previous_login_failed > 2 || ($globals['captcha_first_login'] == true && ! UserAuth::user_cookie_data()) ) && !ts_is_human()) {
			log_insert('login_failed', $globals['form_user_ip_int'], 0);
			recover_error(_('el código de seguridad no es correcto'));
		} elseif ($current_user->Authenticate($username, md5($password), $persistent) == false) {
			log_insert('login_failed', $globals['form_user_ip_int'], 0);
			recover_error(_('usuario o email inexistente, sin validar, o clave incorrecta'));
			$previous_login_failed++;
		} else {
			UserAuth::check_clon_from_cookies();
			if(!empty($_REQUEST['return'])) {
				header('Location: '.$_REQUEST['return']);
			} else {
				header('Location: ./');
			}
			die;
		}
	}
	echo '<p><label for="name">'._('usuario o email').':</label><br />'."\n";
	echo '<input type="text" name="username" size="25" tabindex="1" id="name" value="'.htmlentities($username).'" /></p>'."\n";
	echo '<p><label for="password">'._('clave').':</label><br />'."\n";
	echo '<input type="password" name="password" id="password" size="25" tabindex="2"/></p>'."\n";
	echo '<p><label for="remember">'._('recuérdame').': </label><input type="checkbox" name="persistent" id="remember" tabindex="3"/></p>'."\n";

	// Print captcha
	if ($previous_login_failed > 2 || ($globals['captcha_first_login'] == true && ! UserAuth::user_cookie_data())) {
		ts_print_form();
	}

	get_form_auth_ip();

	echo '<p><input type="submit" value="login" tabindex="4" />'."\n";
	echo '<input type="hidden" name="processlogin" value="1"/></p>'."\n";
	echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'"/>'."\n";
	echo '</form>'."\n";
	echo '<div><strong><a href="login.php?op=recover">'._('¿has olvidado la contraseña?').'</a></strong></div>'."\n";
	echo '<div style="margin-top: 30px">';
	print_oauth_icons($_REQUEST['return']);
  	echo '</div>'."\n";

}

function do_recover() {
	global $site_key, $globals;


	if(!empty($_POST['recover'])) {
		if (!ts_is_human()) {
			recover_error(_('el código de seguridad no es correcto'));
		} else {
			$error = false;
			$user=new User();
			if (preg_match('/.+@.+/', $_POST['email'])) {
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
		echo '<form action="login.php" method="post">'."\n";
		echo '<label for="name">'._('indica el email de la cuenta').':</label><br />'."\n";
		echo '<input type="text" name="email" size="25" id="name" value="'.htmlspecialchars($_POST['email']).'" />'."\n";
		echo '<p>'._('(recibirás un e-mail que te permitirá editar tus datos)').'</p>&nbsp;<br/>';
		echo '<input type="hidden" name="recover" value="1"/>'."\n";
		echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'"/>'."\n";
		ts_print_form();
		echo '<br /><input type="submit" value="'._('recibir e-mail').'"/>'."\n";
		echo '</form>'."\n";
	}
}

function recover_error($message) {
	echo '<div class="error-text">';
	echo $message;
	echo "</div>\n";
}

?>
