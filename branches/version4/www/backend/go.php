<?
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

$id = intval($_GET['id']);
if ($id > 0) {
		$url = $db->get_var("select link_url from links where link_id = $id");
		if ($url) {
			header('HTTP/1.1 301 Moved');
			header('Cache-Control: no-cache');
			header('Location: ' . $url);
			$db->query("INSERT INTO link_clicks (id, counter) VALUES ($id,1) ON DUPLICATE KEY UPDATE counter=counter+1");
			exit(0);
		}
}
require(mnminclude.$globals['html_main']);
do_error(_('enlace inexistente'), 404);
?>

