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

$globals['ads'] = true;
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

do_header("login");
echo '<div id="newswrap">' . "\n";


if($_GET["op"] === 'recover' || !empty($_POST['recover'])) {
	do_recover();
} else {
	do_login();
}

echo '</div>'."\n"; // singlewrap

do_footer();


function do_login() {
	global $current_user, $globals;

	$previous_login_failed =  log_get_date('login_failed', $globals['original_user_ip_int'], 0, 90);

	echo '<form action="login.php" id="xxxthisform" method="post">'."\n";
	
	if($_POST["processlogin"] == 1) {
		$username = clean_input_string(trim($_POST['username']));
		$password = trim($_POST['password']);
		$persistent = $_POST['persistent'];
		if ($previous_login_failed > 2  && !ts_is_human()) {
			log_insert('login_failed', $globals['original_user_ip_int'], 0);
			recover_error(_('El código de seguridad no es correcto!'));
		} elseif ($current_user->Authenticate($username, $password, $persistent) == false) {
			log_insert('login_failed', $globals['original_user_ip_int'], 0);
			recover_error(_('usuario inexistente, sin validar, o clave incorrecta'));
			$previous_login_failed++;
		} else {
			if(!empty($_REQUEST['return'])) {
				header('Location: '.$_REQUEST['return']);
			} else {
				header('Location: ./');
			}
			die;
		}
	}
	echo '<p><label for="name">'._('usuario').':</label><br />'."\n";
	echo '<input type="text" name="username" size="25" tabindex="1" id="name" value="'.htmlentities($username).'" /></p>'."\n";
	echo '<p><label for="password">'._('clave').':</label><br />'."\n";
	echo '<input type="password" name="password" id="password" size="25" tabindex="2"/></p>'."\n";
	echo '<p><label for="remember">'._('recuérdame').': </label><input type="checkbox" name="persistent" id="remember" tabindex="3"/></p>'."\n";
	if ($previous_login_failed > 2) {
		ts_print_form();
	}
	echo '<p><input type="submit" value="login" class="button" tabindex="4" />'."\n";
	echo '<input type="hidden" name="processlogin" value="1"/></p>'."\n";
	echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'"/>'."\n";
	echo '</form>'."\n";
	echo '<div><strong><a href="login.php?op=recover">'._('¿Has olvidado la contraseña?').'</a></strong></div>'."\n";
}

function do_recover() {
	global $site_key, $globals;


	if(!empty($_POST['recover'])) {
		if (!ts_is_human()) {
			recover_error(_('El código de seguridad no es correcto!'));
		} else {
			require_once(mnminclude.'user.php');
			$user=new User();
			if (preg_match('/.+@.+/', $_POST['username'])) {
				// It's an email address
				$user->email=$_POST['username'];
			} else {
				$user->username=$_POST['username'];
			}
			if(!$user->read()) {
				recover_error(_('el usuario o email no existe'));
				return false;
			}
			if($user->level == 'disabled') {
				recover_error(_('cuenta deshabilitada'));
				return false;
			}
			require_once(mnminclude.'mail.php');
			$sent = send_recover_mail($user);
		}
	}
	if (!$sent) {
		echo '<form action="login.php" method="post">'."\n";
		echo '<label for="name">'._('introduce nombre de usuario o email').':</label><br />'."\n";
		echo '<input type="text" name="username" size="25" tabindex="1" id="name" value="'.$username.'" />'."\n";
		echo '<p>'._('(recibirás un e-mail para cambiar la contraseña)').'</p>';
		echo '<input type="hidden" name="recover" value="1"/>'."\n";
		echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'"/>'."\n";
		ts_print_form();
		echo '<br /><input type="submit" value="'._('recibir e-mail').'" class="button" />'."\n";
		echo '</form>'."\n";
	}
}

function recover_error($message) {
	echo '<div class="form-error">';
	echo "<p>$message</p>";
	echo "</div>\n";
}

?>
