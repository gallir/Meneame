<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'ts.php');
include(mnminclude.'ban.php');
include(mnminclude.'log.php');

do_header(_("registro"), "post");

echo '<div class="genericform">'."\n";

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

echo '</div>' . "\n";
do_footer();
exit;

function do_register0() {
	echo '<div class="recoverpass" align="center"><h4><a href="login.php?op=recover">'._('¿Has olvidado la contraseña?').'</a></h4></div>';

	echo '<form action="register.php" method="post" id="thisform" onSubmit="return check_checkfield(\'acceptlegal\', \''._('no has aceptado las condiciones de legales de uso').'\')">' . "\n";
	echo '<fieldset>' . "\n";
	echo '<legend><span class="sign">' . _("registro") . '</span></legend>' . "\n";
	echo '<p><label for="name">' . _("nombre de usuario") . ':</label><br />' . "\n";

	echo '<input type="text" name="username" id="name" value="" onkeyup="enablebutton(this.form.checkbutton1, this.form.submit, this)" size="25" tabindex="1"/>' . "\n";
	echo '<span id="checkit"><input type="button" class="button" id="checkbutton1" disabled="disabled" value="'._('verificar').'" onclick="checkfield(\'username\', this.form, this.form.username)"/></span>' . "\n";
	echo '<br/><span id="usernamecheckitvalue"></span></p>' . "\n";

	echo '<p><label for="email">email:</label><br />' . "\n";
	echo '<span class="note">'._('es importante que sea correcta, recibirás un correo para validar la cuenta').'</span> <br />';
	echo '<input type="text" id="email" name="email" value=""  onkeyup="enablebutton(this.form.checkbutton2, this.form.submit, this)" size="25" tabindex="2"/>' . "\n";
		echo '<input type="button" class="button" id="checkbutton2" disabled="disabled" value="'._('verificar').'" onclick="checkfield(\'email\', this.form, this.form.email)"/>' . "\n";
	echo '<br/><span id="emailcheckitvalue"></span></p>' . "\n";

	echo '<p><label for="password">' . _("clave") . ':</label><br />' . "\n";
	echo '<span class="note">'._('al menos cinco caracteres').' </span><br />';
	echo '<input type="password" id="password" name="password" size="25" tabindex="3"/></p>' . "\n";
	echo '<p><label for="verify">' . _("verificación de clave") . ': </label><br />' . "\n";
	echo '<input type="password" id="verify" name="password2" size="25" tabindex="4"/></p>' . "\n";

	echo '<p><span class="note">'._('has leído y aceptas las ');
	do_legal(_('condiciones de uso'), 'target="_blank"', false);
	echo ' <input type="checkbox" id="acceptlegal" name="acceptlegal" value="accept" tabindex="5"/></span></p>' . "\n";

	echo '<p><input type="submit" class="button" disabled="disabled" name="submit" value="'._('crear usuario').'" class="log2" tabindex="6" /></p>' . "\n";
	echo '<input type="hidden" name="process" value="1"/>' . "\n";

	echo '</fieldset>' . "\n";
	echo '</form>' . "\n";
}

function do_register1() {
	global $db, $globals;

	if($_POST["acceptlegal"] !== 'accept' ) {
		register_error(_("no has aceptado las condiciones de uso"));
		return;
	}
	if (!check_user_fields()) return;
	echo '<br style="clear:both" />';


	echo '<form action="register.php" method="post" id="thisform">' . "\n";
	echo '<fieldset><legend><span class="sign">'._('validación').'</span></legend>'."\n";
	ts_print_form();
	echo '<input type="submit" name="submit" class="button" value="'._('continuar').'" />';
	echo '<input type="hidden" name="process" value="2" />';
	echo '<input type="hidden" name="email" value="'.clean_input_string($_POST["email"]).'" />'; // extra sanity, in fact not needed
	echo '<input type="hidden" name="username" value="'.clean_input_string($_POST["username"]).'" />'; // extra sanity, in fact not needed
	echo '<input type="hidden" name="password" value="'.clean_input_string($_POST["password"]).'" />'; // extra sanity, in fact not needed
	echo '<input type="hidden" name="password2" value="'.clean_input_string($_POST["password2"]).'" />'; // extra sanity, in fact not needed
	echo '</fieldset></form>'."\n";
}

function do_register2() {
	global $db, $current_user, $globals;
	if ( !ts_is_human()) {
		register_error(_('El código de seguridad no es correcto.'));
		return;
	}

	if (!check_user_fields())  return;

	$username=clean_input_string(trim($_POST['username'])); // sanity check
	$dbusername=$db->escape($username); // sanity check
	$password=md5(trim($_POST['password']));
	$email=clean_input_string(trim($_POST['email'])); // sanity check
	$dbemail=$db->escape($email); // sanity check
	$user_ip = $globals['user_ip'];
	if (!user_exists($username)) {
		if ($db->query("INSERT INTO users (user_login, user_login_register, user_email, user_email_register, user_pass, user_date, user_ip) VALUES ('$dbusername', '$dbusername', '$dbemail', '$dbemail', '$password', now(), '$user_ip')")) {
			echo '<fieldset>'."\n";
			echo '<legend><span class="sign">'._("registro de usuario").'</span></legend>'."\n";
			require_once(mnminclude.'user.php');
			$user=new User();
			$user->username=$username;
			if(!$user->read()) {
				register_error(_('Error insertando usuario en la base de datos'));
			} else {
				require_once(mnminclude.'mail.php');
				$sent = send_recover_mail($user);
				log_insert('user_new', $user->id, $user->id);
			}
			echo '</fieldset>'."\n";
		} else {
			register_error(_("Error insertando usuario en la base de datos"));
		}
	} else {
		register_error(_("El usuario ya existe"));
	}
}

function check_user_fields() {
	global $globals, $db;
	$error = false;

	if(check_ban_proxy()) {
		register_error(_("IP no permitida"));
		$error=true;
	}
	if(!isset($_POST["username"]) || strlen($_POST["username"]) < 3) {
		register_error(_("Nombre de usuario erróneo, debe ser de 3 o más caracteres alfanuméricos"));
		$error=true;
	}
	if(!check_username($_POST["username"])) {
		register_error(_("Nombre de usuario erróneo, caracteres no admitidos o no comienzan con una letra"));
		$error=true;
	}
	if(user_exists(trim($_POST["username"])) ) {
		register_error(_("El usuario ya existe"));
		$error=true;
	}
	if(!check_email(trim($_POST["email"]))) {
		register_error(_("El correo electrónico no es correcto"));
		$error=true;
	}
	if(email_exists(trim($_POST["email"])) ) {
		register_error(_("Ya existe otro usuario con esa dirección de correo"));
		$error=true;
	}
	if(preg_match('/[ \']/', $_POST["password"]) || preg_match('/[ \']/', $_POST["password2"]) ) {
		register_error(_("Caracteres inválidos en la clave"));
		$error=true;
	}
	if(strlen($_POST["password"]) < 5 ) {
		register_error(_("Clave demasiado corta, debe ser de 5 o más caracteres"));
		$error=true;
	}
	if($_POST["password"] !== $_POST["password2"] ) {
		register_error(_("Las claves no coinciden"));
		$error=true;
	}

	// Check registers from the same IP network
	$user_ip = $globals['user_ip'];
	$ip_classes = explode(".", $user_ip);

	// From the same IP
	$registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 24 hour) and log_type in ('user_new', 'user_delete') and log_ip = '$user_ip'");
	if($registered > 0) {
		syslog(LOG_NOTICE, "Meneame, register not accepted by IP address ($_POST[username]) $user_ip");
		register_error(_("Para registrar otro usuario desde la misma dirección debes esperar 24 horas."));
		$error=true;
	}
	if ($error) return false;

	// Check class
	// nnn.nnn.nnn
	$ip_class = $ip_classes[0] . '.' . $ip_classes[1] . '.' . $ip_classes[2] . '.%';
	$registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 6 hour) and log_type in ('user_new', 'user_delete') and log_ip like '$ip_class'");
	if($registered > 0) {
		syslog(LOG_NOTICE, "Meneame, register not accepted by IP class ($_POST[username]) $ip_class");
		register_error(_("Para registrar otro usuario desde la misma red debes esperar 6 horas."). " ($ip_class)");
		$error=true;
	}
	if ($error) return false;

	// Check class
	// nnn.nnn
	$ip_class = $ip_classes[0] . '.' . $ip_classes[1] . '.%';
	$registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 1 hour) and log_type in ('user_new', 'user_delete') and log_ip like '$ip_class'");
	if($registered > 2) {
		syslog(LOG_NOTICE, "Meneame, register not accepted by IP class ($_POST[username]) $ip_class");
		register_error(_("Para registrar otro usuario desde la misma red debes esperar unos minutos.") . " ($ip_class)");
		$error=true;
	}
	if ($error) return false;

	return true;
}


function register_error($message) {
	echo '<div class="form-error">';
	echo "<p>$message</p>";
	echo "</div>\n";
}

?>
