<?
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
include_once('../config.php');

$type = $db->escape($_GET['type']);
$id = intval($_GET['id']);
$version = intval($_GET['version']);

if (empty($type) || ! $id ) not_found();

$media = new Upload($type, $id, $version);

if (! $media->read()) not_found();
//echo $media->filename();

if ($media->access == 'public' || $current_user->user_id > 0) {
	header("Content-type: $media->mime");
	if ($media->size > 0) {
		header("Content-lenght: $media->size");
	}
	//header('Cache-Control: max-age=120');
	$media->readfile();
} else {
	header("Content-type: text/html");
	echo '<b>'._('Debe estar autentificado para ver esta imagen') . '</b>';
}
exit;
?>
