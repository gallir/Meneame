<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');

header('Content-Type: text/plain; charset=UTF-8');
stats_increment('ajax');

$user = intval($_REQUEST['type']);
if ($user != $current_user->user_id) {
	error(_('usuario incorrecto'));
}

$meta_src = $meta_new = intval($_REQUEST['id']);
if ($meta_new == 0) {
	// expire the cookie
	setcookie("mnm_user_meta", '', time()-3600, $globals['base_url']);
} else {
	$meta_uri = $db->get_var("select category_uri from categories where category_id = '$meta_new' and category_parent = 0");
	if(empty($meta_uri)) {
		error(_('meta incorrecta'). " $meta_new");
	}
	if ($meta_uri == $_COOKIE['mnm_user_meta']) {
		setcookie("mnm_user_meta", '', time()-3600, $globals['base_url']);
		$meta_new = 0;
	} else {
		$expiration = time() + 3600000; // Valid for 1000 hours
		setcookie("mnm_user_meta", $meta_uri, $expiration, $globals['base_url']);
	}
}

echo meta_teaser($meta_src, $meta_new);

function error($mess) {
	echo "ERROR: $mess\n";
	die;
}

?>
