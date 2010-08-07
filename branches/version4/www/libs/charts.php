<?
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function chart_link_karma_history($link_id) {
	global $globals;

	echo '<script src="'.$globals['base_static'].'js/jquery.flot.min.js" type="text/javascript"></script>'."\n";
	echo '<div id="flot" style="width:500px;height:250px;"></div>'."\n";
	include ('foreign/chart_link_karma_history.js');
}
?>
