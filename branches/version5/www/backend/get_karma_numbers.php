<?php
include_once('../config.php');

if (empty($current_user->user_login)) {
	echo _('usuario no identificado');
	die;
}

if (!empty($_GET['id']) && $current_user->user_level == 'god') {
	$user = new User(intval($_GET['id']));
} else {
	$user = new User($current_user->user_id);
}

echo '<div style="text-align: left">';
if ($user->karma_log) {
	echo '<strong>' . _('última modificación') . ':</strong> ' . get_date_time($user->karma_calculated);
	echo '<ul>';
	foreach (preg_split("/\n/", $user->karma_log) as $line) {
		$line = trim($line);
		if($line) echo "<li>$line</li>\n";
	}
	echo '</ul>';
} else {
	print _('no hay registros para este usuario');
}
echo '</div>';
?>
