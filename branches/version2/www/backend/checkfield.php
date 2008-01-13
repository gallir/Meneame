<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

stats_increment('ajax');

header('Content-Type: text/plain; charset=UTF-8');
$type=clean_input_string($_REQUEST['type']);
$name=clean_input_string($_GET['name']);
#echo "$type, $name...";
switch ($type) {
	case 'username':
		if (!check_username(trim($_GET['name']))) {
			echo _('caracteres inválidos o no comienzan con una letra');
			return;
		}
		if (strlen($name)<3) {
			echo _('nombre demasiado corto');
			return;
		}
		if (strlen($name)>24) {
			echo _('nombre demasiado largo');
			return;
		}
		if(!($current_user->user_id > 0 && $current_user->user_login == $name) && user_exists($name)) {
			echo _('el usuario ya existe');
			return;
		}
		echo "OK";
		break;
	case 'email':
		if (!check_email($name)) {
			echo _('dirección de correo no válida');
			return;
		}
		if(!($current_user->user_id > 0 && $current_user->user_email == $name) && email_exists($name)) {
			echo _('ya existe otro usuario con esa dirección de correo');
			return;
		}
		echo "OK";
		break;

	case 'ban_hostname':
		if (strlen($name)>64) {
			echo _('nombre demasiado largo');
			return;
		}
		require_once(mnminclude.'ban.php');

		if(check_ban($name, 'hostname')) {
			echo $globals['ban_message'];
			return;
		}

		echo "OK";
		break;
	case 'ban_email':
		if (strlen($name)>64) {
			echo _('nombre demasiado largo');
			return;
		}
		if (check_email($name)) {
			echo _('dirección de correo no válida');
			return;
		}
		require_once(mnminclude.'ban.php');
		if(check_ban($name, 'email')) {
			echo $globals['ban_message'];
			return;
		}
		echo "OK";
		break;
	case 'ban_ip':
		if (strlen($name)>64) {
			echo _('nombre demasiado largo');
			return;
		}
		require_once(mnminclude.'ban.php');
		if(check_ban($name, 'ip')) {
			echo $globals['ban_message'];
			return;
		}
		echo "OK";
		break;
	case 'ban_proxy':
		if (strlen($name)>64) {
			echo _('nombre demasiado largo');
			return;
		}
		require_once(mnminclude.'ban.php');
		if(check_ban($name, 'proxy')) {
			echo $globals['ban_message'];
			return;
		}
		echo "OK";
		break;
	case 'ban_words':
		if (strlen($name)>64) {
			echo _('nombre demasiado largo');
			return;
		}
		require_once(mnminclude.'ban.php');
		if(check_ban($name, 'words')) {
			echo $globals['ban_message'];
			return;
		}
		echo "OK";
		break;

	default:
		echo "KO $type";
}
?>
