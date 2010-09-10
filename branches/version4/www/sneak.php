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


#$globals['ads'] = true;
$globals['favicon'] = 'img/favicons/favicon-sneaker.ico';

init_sneak();

// Start html
array_push($globals['extra_css'], 'es/sneak.css?'.$globals['sneak_version']);
if (!empty($_REQUEST['friends'])) {
	do_header(_('amigos en la fisgona'));
} elseif ($current_user->user_id > 0 && !empty($_REQUEST['admin']) && $current_user->admin) {
	do_header(_('admin'));
} else {
	do_header(_('fisgona'));
}

Haanga::Load('sneak/base.html');

// Check the tab options and set corresponging JS variables
if ($current_user->user_id > 0) {
	if (!empty($_REQUEST['friends'])) {
		$option = 2;
	} elseif (!empty($_REQUEST['admin']) && $current_user->admin) {
		$option = 3;
	} else {
		$option = 1;
	}
	Haanga::Load('sneak/tabs.html', compact('option'));
}
//////


$items = array();
for ($i=0; $i<$max_items;$i++) {
	$items[$i] = $i;
}
$globals['sneak_telnet'] = false;
Haanga::Load('sneak/form.html', compact('items'));

do_footer();

?>
