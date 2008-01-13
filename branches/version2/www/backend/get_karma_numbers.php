<?php
include_once('../config.php');

stats_increment('ajax');

if (empty($globals['karma_log']) || empty($current_user->user_login)) {
	echo _('fichero de karma incorrecto o usuario no identificado');
	die;
}
$fd = @fopen($globals['karma_log'], 'r');
if (! $fd) {
	echo _('no se puedo abrir el fichero ') . $globals['karma_log'];
	die;
}

if (!empty($_GET['id']) && $current_user->user_level == 'god') {
	$user = intval($_GET['id']);
} else {
	$user = $current_user->user_id;
}

$found = false;
while (($line = fgets($fd))) {
	if (preg_match("/^0*$user /i", $line)) {
		$found = true;
		$line = preg_replace('/^\d+ /', '', $line);
		echo "$line<br />\n";
	} elseif ($found) {
		break;
	}
}
fclose($fd);

if (!$found) {
	print _('no hay registros para este usuario');
}
?>
