<?php
include_once('../config.php');

$id = $db->escape($_GET['id']);
$annotation = new Annotation($id);
echo '<div style="text-align: left">';
if ($annotation->read()) {
	$array = unserialize($annotation->text);
	if (! is_array($object)) {
		$array = (array) $array;
	}
	echo '<strong style="font-variant: small-caps">' . _('modificación') . ':</strong> ' . get_date_time($annotation->time);
	echo '<ul>';
	foreach ($array as $k => $v) {
		echo "<li><strong style='font-variant: small-caps'>$k</strong>: $v</li>\n";
	}
	echo '</ul>';
} else {
	echo _('objeto inexistente').': ', __($id);
}
echo '</div>';
?>
