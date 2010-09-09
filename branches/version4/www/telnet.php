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

echo '<div class="sneaker">';
echo '<div class="sneaker-legend">';
echo '<form action="" class="sneaker-control" id="sneaker-control" name="sneaker-control">';
echo '<label>'._('votos publicadas: ').'<input type="checkbox" checked="checked" name="sneak-pubvotes" id="pubvotes-status" onclick="toggle_control(\'pubvotes\')" /></label> &nbsp;';
echo '<label>'._('voto').': <input type="checkbox" checked="checked" name="sneak-vote" id="vote-status" onclick="toggle_control(\'vote\')" /> [+]</label>&nbsp;';
echo '<label>'._('problema').': <input type="checkbox" checked="checked" name="sneak-problem" id="problem-status" onclick="toggle_control(\'problem\')" /> [-]</label>&nbsp;';
echo '<label>'._('comentario').': <input type="checkbox" checked="checked" name="sneak-comment" id="comment-status" onclick="toggle_control(\'comment\')" /> [C]</label>&nbsp;';
echo '<label>'._('nueva').': <input type="checkbox" checked="checked" name="sneak-new" id="new-status" onclick="toggle_control(\'new\')" /> [&rarr;]</label>&nbsp;';
echo '<label>'._('publicada').': <input type="checkbox" checked="checked" name="sneak-published" id="published-status" onclick="toggle_control(\'published\')" /> [&larr;]</label>&nbsp;';

if ($current_user->user_id > 0) $chat_checked = 'checked="checked"';
else $chat_checked = '';
echo '<label>'._('mensaje').': <input type="checkbox" '.$chat_checked.' name="sneak-chat" id="chat-status" onclick="toggle_control(\'chat\')" /> [T]</label>&nbsp;';
echo '&nbsp;[<a href="sneak.php" title="'._('ir a fisgona tradicional').'">'._('fisgona').'</a>]<br/>';
echo '<abbr title="'._('total&nbsp;(registrados+jabber+anónimos)').'">'._('fisgonas').'</abbr>: <strong><span style="font-size: 120%;" id="ccnt"> </span></strong>';
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<abbr title="'._('tiempo medio en milisegundos para procesar cada petición al servidor').'">ping</abbr>: <span id="ping">---</span>';
echo "</form>\n";
if ($current_user->user_id > 0) {
	echo '<form name="chat_form" onsubmit="return send_chat(this);">';
	echo _('mensaje') . ': <input type="text" name="comment" id="comment-input" value="" size="90" maxlength="230" autocomplete="off" />&nbsp;<input type="submit" value="'._('enviar').'" class="sendmessage"/>';
	echo '</form>';
}
echo '</div>' . "\n";
echo '<div class="sneaker-item">';
echo '<div class="sneaker-ts"><strong>'._('hora').'</strong></div>';
echo '<div class="sneaker-type"><strong>'._('acción').'</strong></div>';
echo '<div class="sneaker-votes"><strong><abbr title="'._('meneos').'">me</abbr>/<abbr title="'._('comentarios').'">co</abbr></strong></div>';
echo '<div class="sneaker-story"><strong>'._('noticia').'</strong></div>';
echo '<div class="sneaker-who"><strong>'._('quién/qué').'</strong></div>';
echo '<div class="sneaker-status"><strong>'._('estado').'</strong></div>';
echo "</div>\n";


echo '<div id="items'.$i.'">';
for ($i=0; $i<$max_items;$i++) {
	echo '<div class="sneaker-item">&nbsp;</div>';
}
echo "</div>\n";

echo '</div>';
echo "</body></html>\n";
?>
