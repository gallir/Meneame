<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

$globals['ads'] = true;

// Clean return variable
if(!empty($_REQUEST['return']))
	$_REQUEST['return'] = clean_input_string($_REQUEST['return']);

if($_GET["op"] === 'logout') {
	$current_user->Logout($_REQUEST['return']);
}

// We need it because we modify headers
ob_start();

do_header("login");
do_banner_top();
echo '<div id="container-wide">' . "\n";

echo '<div id="genericform-contents">'."\n";
//echo '<div id="genericform">'."\n";

if($_GET["op"] === 'recover' || !empty($_POST['recover'])) {
	do_recover();
} else {
	do_login();
}

echo '</div>'."\n";
//echo '</div>'."\n";

do_footer();


function do_login() {
	global $current_user;

	// Start posavasos & ashacz code
	
	echo '<div id="mini-faq" style="float:left; width:65%; margin-top: 10px;">'."\n";
	// gallir: Only prints if the user was redirected from submit.php
	if (!empty($_REQUEST['return']) && preg_match('/submit\.php/', $_REQUEST['return'])) { 
		echo '<p style="border:1px solid #FF9400; font-size:1.3em; background:#FEFBEA; font-weight:bold; padding:0.5em 1em;">Para enviar una historia debes ser un usuario registrado</p>'."\n";
	}
	echo '<h3>¿Qué es menéame?</h3>'."\n";
	echo '<p>Es un web que te permite enviar una historia que será revisada por todos y será promovida, o no, a la página principal. Cuando un usuario envía una historia ésta queda en la <a href="shakeit.php" title="Cola de historias pendientes">cola de pendientes</a> hasta que reúne los votos suficientes para ser promovida a la página principal.</p>'."\n";
		
	echo '<h3>¿Todavía no eres usuario de menéame?</h3>'."\n";
	echo '<p>Como usuario registrado podrás, entre otras cosas:</p>'."\n";
	echo '<ul>'."\n";
	echo '<li>'."\n";
	echo '<strong>Enviar historias</strong><br />'."\n";
	echo 'Una vez registrado puedes enviar las historias que consideres interesantes para la comunidad. Si tienes algún tipo de duda sobre que tipo de historias puedes enviar revisa nuestras <a href="faq-es.php" title="Acerca de meneame">preguntas frecuentes sobre menéame.</a>'."\n";
	echo '</li>'."\n";
	echo '<li>'."\n";
	echo '<strong>Escribir comentarios</strong><br />'."\n";
	echo 'Puedes escribir tu opinión sobre las historias enviadas a menéame mediante comentarios de texto. También puedes votar positivamente aquellos comentarios ingeniosos, divertidos o interesantes y negativamente aquellos que consideres inoportunos.'."\n";
	echo '</li>'."\n";
	echo '<li>'."\n";
	echo '<strong>Perfil de usuario</strong><br />'."\n";
	echo 'Toda tu información como usuario está disponible desde la página de tu perfil. También puedes subir una imagen que representará a tu usuario en menéame. Incluso es posible compartir los ingresos publicitarios de Menéame, solo tienes que introducir el código de tu cuenta Google Adsense desde tu perfil.'."\n";
	echo '</li>'."\n";
	echo '<li>'."\n";
	echo '<strong>Chatear en tiempo real desde la fisgona</strong><br />'."\n";
	echo 'Gracias a la <a href="sneak.php" title="Fisgona">fisgona</a> puedes ver en tiempo real toda la actividad de menéame. Además como usuario registrado podrás chatear con mucha más gente de la comunidad menéame'."\n";
	echo '</li>'."\n";
	echo '</ul>'."\n";
	echo '<h3><a href="register.php" style="color:#FF6400; text-decoration:underline; display:block; width:8em; text-align:center; margin:0 auto; padding:0.5em 1em; border:3px double #FFE2C5; background:#FFF3E8;">Regístrate ahora</a></h3>'."\n";
	echo '</div>'."\n";
		
	echo '<div id="genericform" style="float:right; width:30%;">'."\n";	
	echo '<form action="login.php" id="thisform" method="post">'."\n";
 //End posavasos & ashacz code
	
	
	if($_POST["processlogin"] == 1) {
		$username = clean_input_string(trim($_POST['username']));
		$password = trim($_POST['password']);
		$persistent = $_POST['persistent'];
		if($current_user->Authenticate($username, $password, $persistent) == false) {
			recover_error(_('usuario inexistente, sin validar, o clave incorrecta'));
			//echo '<p><span class="error">'._('ERROR - Usuario o clave incorrecta').'</span></p>';
		} else {
			if(!empty($_REQUEST['return'])) {
				header('Location: '.$_REQUEST['return']);
			} else {
				header('Location: ./');
			}
			die;
		}
	}
	echo '<fieldset>'."\n";
	echo '<legend><span class="sign">login</span></legend>'."\n";
	echo '<p class="l-top"><label for="name">'._('usuario').':</label><br />'."\n";
	echo '<input type="text" name="username" size="25" tabindex="1" id="name" value="'.htmlentities($username).'" /></p>'."\n";
	echo '<p class="l-mid"><label for="password">'._('clave').':</label><br />'."\n";
	echo '<input type="password" name="password" id="password" size="25" tabindex="2"/></p>'."\n";
	echo '<p class="l-mid"><label for="remember">'._('recuérdame').': </label><input type="checkbox" name="persistent" id="remember" tabindex="3"/></p>'."\n";
	echo '<p class="l-bot"><input type="submit" value="login" class="genericsubmit" tabindex="4" />'."\n";
	echo '<input type="hidden" name="processlogin" value="1"/></p>'."\n";
	echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'"/>'."\n";
	echo '</fieldset>'. "\n";
	echo '</form>'."\n";
	echo '<div class="recoverpass" align="center"><h4><a href="login.php?op=recover">'._('¿Has olvidado la contraseña?').'</a></h4></div>'."\n";
	echo '</div>'."\n";
	echo '<br clear="all"/>&nbsp;';
}

function do_recover() {
	global $site_key, $globals;
	require_once(mnminclude.'ts.php');

	echo '<div id="genericform">'."\n";
	echo '<fieldset>'."\n";
	echo '<legend><span class="sign">'._("recuperación de contraseñas").'</span></legend>'."\n";

	if(!empty($_POST['recover'])) {
		if (!ts_is_human()) {
			recover_error(_('El código de seguridad no es correcto!'));
		} else {
			require_once(mnminclude.'user.php');
			$user=new User();
			$user->username=$_POST['username'];
			if(!$user->read()) {
				recover_error(_('el usuario no existe'));
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
		echo '<form action="login.php" id="thisform-recover" method="post">'."\n";
		echo '<label for="name">'._('usuario').':</label><br />'."\n";
		echo '<input type="text" name="username" size="25" tabindex="1" id="name" value="'.$username.'" />'."\n";
		echo '<p class="nobold">'._('(recibirás un e-mail para cambiar la contraseña)').'</p>';
		echo '<input type="hidden" name="recover" value="1"/>'."\n";
		echo '<input type="hidden" name="return" value="'.htmlspecialchars($_REQUEST['return']).'"/>'."\n";
		ts_print_form();
		echo '<br /><input type="submit" value="'._('recibir e-mail').'" class="genericsubmit" />'."\n";
		echo '</form>'."\n";
	}
	echo '</fieldset>'."\n";
	echo '</div>'."\n";
}

function recover_error($message) {
	echo '<div class="form-error">';
	echo "<p>$message</p>";
	echo "</div>\n";
}

?>
