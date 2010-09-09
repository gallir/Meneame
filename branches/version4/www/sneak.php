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
		$taboption = 2;
		echo '<script type="text/javascript">global_options.show_friends = true;</script>';
	} elseif (!empty($_REQUEST['admin']) && $current_user->user_id > 0 && ($current_user->admin)) {
		$taboption = 3;
		echo '<script type="text/javascript">global_options.show_admin = true;</script>';
	} else {
		$taboption = 1;
	}
	print_sneak_tabs($taboption);
}
//////


echo '<div class="sneaker">';
echo '<div class="sneaker-legend" onmouseout="tooltip.clear(event);" onmouseover="tooltip.clear(event);">';
echo '<form action="" class="sneaker-control" id="sneaker-control" name="sneaker-control">';
echo '<img id="play-pause-img" onclick="play_pause()" src="'.$globals['base_static'].'img/common/sneak-pause01.png" alt="play/pause" title="play/pause" />&nbsp;&nbsp;&nbsp;';
echo '<label><input type="checkbox" checked="checked" name="sneak-pubvotes" id="pubvotes-status" onclick="toggle_control(\'pubvotes\')" /><img src="'.$globals['base_static'].'img/common/sneak-vote-published01.png" width="21" height="17" title="'._('votos de publicadas').'" alt="'._('votos de publicadas').'" /></label>';
echo '<label><input type="checkbox" checked="checked" name="sneak-vote" id="vote-status" onclick="toggle_control(\'vote\')" /><img src="'.$globals['base_static'].'img/common/sneak-vote01.png" width="21" height="17" title="'._('meneos').'" alt="'._('meneos').'" /></label>';
echo '<label><input type="checkbox" checked="checked" name="sneak-problem" id="problem-status" onclick="toggle_control(\'problem\')" /><img src="'.$globals['base_static'].'img/common/sneak-problem01.png" width="21" height="17" alt="'._('problema').'" title="'._('problema').'"/></label>';
echo '<label><input type="checkbox" checked="checked" name="sneak-comment" id="comment-status" onclick="toggle_control(\'comment\')" /><img src="'.$globals['base_static'].'img/common/sneak-comment01.png" width="21" height="17" alt="'._('comentario').'" title="'._('comentario').'"/></label>';
echo '<label><input type="checkbox" checked="checked" name="sneak-new" id="new-status" onclick="toggle_control(\'new\')" /><img src="'.$globals['base_static'].'img/common/sneak-new01.png" width="21" height="17" alt="'._('nueva').'" title="'._('nueva').'"/></label>';
echo '<label><input type="checkbox" checked="checked" name="sneak-published" id="published-status" onclick="toggle_control(\'published\')" /><img src="'.$globals['base_static'].'img/common/sneak-published01.png" width="21" height="17" alt="'._('publicada').'" title="'._('publicada').'"/></label>';

// Only registered users can see the chat messages
if ($current_user->user_id > 0) {
	$chat_checked = 'checked="checked"';
	echo '<label><input type="checkbox" '.$chat_checked.' name="sneak-chat" id="chat-status" onclick="toggle_control(\'chat\')" /><img src="'.$globals['base_static'].'img/common/sneak-chat01.png" width="21" height="17" alt="'._('mensaje').'" title="'._('mensaje').'"/></label>';
}
echo '<label><input type="checkbox" checked="checked" name="sneak-post" id="post-status" onclick="toggle_control(\'post\')" /><img src="'.$globals['base_static'].'img/common/sneak-newnotame01.png" width="21" height="17" alt="'._('nótame').'" title="'._('nótame').'"/></label>';


echo '<abbr title="'._('total&nbsp;(registrados+jabber+anónimos)').'">'._('fisgonas').'</abbr>: <strong><span id="ccnt"> </span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<abbr title="'._('tiempo medio en milisegundos para procesar cada petición al servidor').'">ping</abbr>: <span id="ping">---</span>';
echo "</form>\n";
if ($current_user->user_id > 0) {
	echo '<form name="chat_form" action="" onsubmit="return send_chat(this);">';
	echo _('mensaje') . ': <input type="text" name="comment" id="comment-input" value="" size="90" maxlength="230" autocomplete="off" />&nbsp;<input type="submit" value="'._('enviar').'" class="button"/>';
	echo '</form>';
}

echo '</div>' . "\n";

echo '<div id="singlewrap">' . "\n";

echo '<div class="sneaker-item">';
echo '<div class="sneaker-title">';
echo '<div class="sneaker-ts"><strong>'._('hora').'</strong></div>';
echo '<div class="sneaker-type"><strong>'._('acción').'</strong></div>';
echo '<div class="sneaker-votes"><strong><abbr title="'._('meneos').'">me</abbr>/<abbr title="'._('comentarios').'">co</abbr></strong></div>';
echo '<div class="sneaker-story">&nbsp;<strong>'._('noticia').'</strong></div>';
echo '<div class="sneaker-who">&nbsp;<strong>'._('quién/qué').'</strong></div>';
echo '<div class="sneaker-status"><strong>'._('estado').'</strong></div>';
echo "</div>\n";
echo "</div>\n";


echo '<div id="items'.$i.'">';
for ($i=0; $i<$max_items;$i++) {
	echo '<div class="sneaker-item">&nbsp;</div>';
}
echo "</div>\n";
echo '</div>';
echo "</div>\n";

do_footer();

function print_sneak_tabs($option) {
	global $current_user, $globals;
	$active = array();
	$active[$option] = ' class="tabmain-this"';
	echo '<ul class="tabmain">' . "\n";

	echo '<li'.$active[1].'><a href="'.$globals['base_url'].'sneak.php">'._('todos').'</a></li>' . "\n";
	echo '<li'.$active[2].'><a href="'.$globals['base_url'].'sneak.php?friends=1">'._('amigos').'</a></li>' . "\n";
	if ($current_user->user_id > 0 && $current_user->admin) {
		echo '<li'.$active[3].'><a href="'.$globals['base_url'].'sneak.php?admin=1">'._('admin').'</a></li>' . "\n";
	}
	echo '<li><a href="'.$globals['base_url'].'telnet.php">&nbsp;<img src="'.$globals['base_static'].'img/common/konsole.png" alt="telnet"/>&nbsp;</a></li>' . "\n";

	echo '</ul>' . "\n";
}

?>
