<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function send_recover_mail ($user) {
	global $site_key, $globals;

	$now = time();

	if (! empty($globals['email_domain'])) $domain = $globals['email_domain'];
	else $domain = get_server_name();

	$key = md5($user->id.$user->pass.$now.$site_key.get_server_name());
	$url = 'http://'.get_server_name().$globals['base_url'].'profile.php?login='.$user->username.'&t='.$now.'&k='.$key;
	//echo "$user->username, $user->email, $url<br />";
	$to      = $user->email;
	$subject = _('Recuperación o verificación de contraseña de '). get_server_name();
	$subject = mb_encode_mimeheader($subject,"UTF-8", "B", "\n");
	$message = $to . _(': para poder acceder sin la clave, conéctate a la siguiente dirección en menos de dos horas:') . "\n\n$url\n\n";
	$message .= _('Pasado este tiempo puedes volver a solicitar acceso en: ') . "\nhttp://".get_server_name().$globals['base_url']."login.php?op=recover\n\n";
	$message .= _('Una vez en tu perfil, puedes cambiar la clave de acceso.') . "\n" . "\n";
	$message .= "\n\n". _('Este mensaje ha sido enviado a solicitud de la dirección: ') . $globals['user_ip'] . "\n\n";
	$message .= "-- \n  " . _('el equipo de menéame');
	$message = wordwrap($message, 70);
	$headers = 'Content-Type: text/plain; charset="utf-8"'."\n" . 
				'From: '._('Avisos').' '.$domain.' <'._('no_contestar')."@$domain>\n".
				'Reply-To: '._('no_contestar')."@$domain\n".
				'X-Mailer: meneame.net/PHP/' . phpversion(). "\n";
	$headers .= 'MIME-Version: 1.0' . "\n";
	//$pars = '-fweb@'.get_server_name();
	mail($to, $subject, $message, $headers);
	echo '<p><strong>' ._ ('Correo enviado, mira tu buzón, allí están las instrucciones. Mira también en la carpeta de spam.') . '</strong></p>';
	return true;
}
?>
