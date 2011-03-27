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
if (empty($_GET['version'])) $version = 0;
else $version = intval($_GET['version']);

if (empty($type) || ! $id ) not_found();

$media = new Upload($type, $id, $version);

if (! $media->read()) not_found();

if (true || $media->access == 'public' || $current_user->user_id > 0) {
	header("Content-Type: $media->mime");
	header('Last-Modified: ' . date('r', $media->date));
	header('Cache-Control: max-age=3600');
	if (! empty($globals['xsendfile'])) {
		header($globals['xsendfile'].': '.$media->url());
	} else {
		if ($media->size > 0) {
			header("Content-Length: $media->size");
		}
		$media->readfile();
	}
} else {
	header("Content-Type: text/html");
	echo '<b>'._('Debe estar autentificado para ver esta imagen') . '</b>';
}
exit(0);
?>
