<?php
include_once('../config.php');

if (empty($current_user->user_login)) {
	echo _('usuario no identificado');
	die;
}

if (!empty($_GET['id']) && $current_user->user_level == 'god') {
	$user = intval($_GET['id']);
} else {
	$user = $current_user->user_id;
}

$annotation = new Annotation("karma-$user");
echo '<div style="text-align: left">';
if ($annotation->read()) {
	echo '<strong>' . _('última modificación') . ':</strong> ' . get_date_time($annotation->time);
	echo '<ul>';
	foreach (preg_split("/\n/", $annotation->text) as $line) {
		$line = trim($line);
		if($line) echo "<li>$line</li>\n";
	}
	echo '</ul>';
} else {
	print _('no hay registros para este usuario');
}
echo '</div>';
?>
