<?php
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at uib dot es> and 
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>

include_once('../config.php');

if ($current_user->user_level != 'god' && $current_user->user_level != 'admin') {
	echo _('usuario no autorizado');
	die;
}

$user_id = intval($_GET['id']);
if (! $user_id > 0) {
	echo _('usuario no identificado');
	die;
}


$clones = $db->get_col("select distinct user_login from clones, users where clon_from = $user_id and user_id = clon_to order by clon_date desc limit 20");
if (! $clones) {
	print _('no hay clones para este usuario');
	die;
}

echo '<ul>';
foreach ($clones as  $clon) {
	echo '<li><a href="'.get_user_uri($clon).'">'.$clon."</a></li>\n";
}
echo '</ul>';

?>
