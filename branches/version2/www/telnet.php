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
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' .
 "\n";
//echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$dblang.'" lang="'.$dblang.'">' . "\n";
echo '<head>' . "\n";
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
echo "<title>"._('fisgona')." // men&eacute;ame</title>\n";
echo '<meta name="generator" content="meneame" />' . "\n";
echo '<style type="text/css" media="screen">@import "'.$globals['base_url'].'css/es/telnet.css";</style>' . "\n";
echo '<link rel="icon" href="img/common/konsole.png" type="image/x-icon" />' . "\n";
do_js_includes();
echo '</head>' . "\n";
echo "<body>\n";

?>
<script type="text/javascript">
//<![CDATA[
var my_version = '<? echo $sneak_version; ?>';
var ts=<? echo (time()-3600); ?>; // just due a freaking IE cache problem
var server_name = '<? echo get_server_name(); ?>';
var sneak_base_url = 'http://'+'<? echo get_server_name().$globals['base_url'];?>'+'backend/sneaker.php';
var mykey = <? echo rand(100,999); ?>;

var show_friends = false;
var do_animation = false;

$(function(){start_sneak()});

function set_initial_display(item, i) {
	item.children().hide();
	item.children().fadeIn('normal');
}

function to_html(data) {
	var tstamp=new Date(data.ts*1000);
	var timeStr;

	var hours = tstamp.getHours();
	var minutes = tstamp.getMinutes();
	var seconds = tstamp.getSeconds();

	timeStr  = ((hours < 10) ? "0" : "") + hours;
	timeStr  += ((minutes < 10) ? ":0" : ":") + minutes;
	timeStr  += ((seconds < 10) ? ":0" : ":") + seconds;

	html = '<div class="sneaker-ts">'+timeStr+'</div>';

	/* If it's a comment */
	if (data.type == 'chat') {
		html += '<div class="sneaker-type">T</div>';
		html += '<div class="sneaker-votes">&nbsp;</div>';
		html += '<div class="sneaker-chat">'+data.title+'</div>';
		html += '<div class="sneaker-who">';
		html += '<a href="user.php?login='+data.who+'">'+data.who.substring(0,15)+'</a></div>';
		html += '<div class="sneaker-status">'+data.status+'</div>';
		return html;
	}

	/* All the others */
	if (data.type == 'vote')
		html += '<div class="sneaker-type">+</div>';
	else if (data.type == 'problem')
		html += '<div class="sneaker-type">-</div>';
	else if (data.type == 'comment')
		html += '<div class="sneaker-type">C</div>';
	else if (data.type == 'new')
		html += '<div class="sneaker-type">&rarr;</div>';
	else if (data.type == 'published')
		html += '<div class="sneaker-type">&larr;</div>';
	else if (data.type == 'discarded')
		html += '<div class="sneaker-type">&darr;</div>';
	else if (data.type == 'edited')
		html += '<div class="sneaker-type">E</div>';
	else if (data.type == 'cedited')
		html += '<div class="sneaker-type">e</div>';
	else
		html += '<div class="sneaker-type">'+data.type+'</div>';

	html += '<div class="sneaker-votes">'+data.votes+'/'+data.com+'</div>';
	if ("undefined" != typeof(data.cid) && data.cid > 0) anchor='#c-'+data.cid;
	else anchor='';

	html += '<div class="sneaker-story"><a href="'+data.link+anchor+'">'+data.title+'</a></div>';
	if (data.type == 'problem')
		html += '<div class="sneaker-who"><span class="sneaker-problem">'+data.who+'</span></div>';
	else if (data.uid > 0)  {
		html += '<div class="sneaker-who">';
		html += '<a href="user.php?login='+data.who+'">'+data.who.substring(0,15)+'</a></div>';
	} else 
		html += '<div class="sneaker-who">'+data.who.substring(0,15)+'</div>';
	if (data.status == '<? echo _('publicada');?>')
		html += '<div class="sneaker-status"><a href="./"><span class="sneaker-published">'+data.status+'</span></a></div>';
	else if (data.status == '<? echo _('descartada');?>')
		html += '<div class="sneaker-status"><a href="shakeit.php?meta=_discarded"><span class="sneaker-discarded">'+data.status+'</span></a></div>';
	else 
		html += '<div class="sneaker-status"><a href="shakeit.php">'+data.status+'</a></div>';
	return html;
}


//]]>
</script>
<script type="text/javascript" src="http://<? echo get_server_name().$globals['base_url']; ?>js/sneak10.js.php"></script>
<?

echo '<div class="sneaker">';
echo '<div class="sneaker-legend">';
echo '<form action="" class="sneaker-control" id="sneaker-control" name="sneaker-control">';
echo _('votos publicadas: ').'<input type="checkbox" checked="checked" name="sneak-pubvotes" id="pubvotes-status" onclick="toggle_control(\'pubvotes\')" /> &nbsp;';
echo _('voto: ').'<input type="checkbox" checked="checked" name="sneak-vote" id="vote-status" onclick="toggle_control(\'vote\')" /> [+]&nbsp;';
echo _('problema: ').'<input type="checkbox" checked="checked" name="sneak-problem" id="problem-status" onclick="toggle_control(\'problem\')" /> [-]&nbsp;';
echo _('comentario: ').'<input type="checkbox" checked="checked" name="sneak-comment" id="comment-status" onclick="toggle_control(\'comment\')" /> [C]&nbsp;';
echo _('nueva: ').'<input type="checkbox" checked="checked" name="sneak-new" id="new-status" onclick="toggle_control(\'new\')" /> [&rarr;]&nbsp;';
echo _('publicada: ').'<input type="checkbox" checked="checked" name="sneak-published" id="published-status" onclick="toggle_control(\'published\')" /> [&larr;]&nbsp;';

if ($current_user->user_id > 0) $chat_checked = 'checked="checked"';
else $chat_checked = '';
echo _('mensaje: ').'<input type="checkbox" '.$chat_checked.' name="sneak-chat" id="chat-status" onclick="toggle_control(\'chat\')" /> [T]&nbsp;';
echo '<abbr title="'._('total&nbsp;(registrados+anónimos)').'">'._('fisgonas').'</abbr>: <strong><span style="font-size: 120%;" id="ccnt"> </span></strong>';
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<abbr title="'._('tiempo medio en milisegundos para procesar cada petición al servidor').'">ping</abbr>: <span id="ping">---</span>';
echo "</form>\n";
if ($current_user->user_id > 0) {
	echo '<form name="chat_form" onsubmit="return send_chat(this);">';
	echo _('mensaje') . ': <input type="text" name="comment" value="" size="90" maxlength="230" autocomplete="off" />&nbsp;<input type="submit" value="'._('enviar').'" class="sendmessage"/>';
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
