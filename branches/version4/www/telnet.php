<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'sneak.php');


init_sneak();

// Start html
header("Content-type: text/html; charset=utf-8");

Haanga::Load('sneak/telnet_base.html');

$globals['sneak_telnet'] = true;
Haanga::Load('sneak/form.html', compact('max_item'));

?>
