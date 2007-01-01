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
do_header(_('¡HOYGAN!'));

?>
<script type="text/javascript">
//<![CDATA[
var my_version = '<? echo $sneak_version; ?>';
var ts=<? echo (time()-3600); ?>; // just due a freaking IE cache problem
var server_name = '<? echo get_server_name(); ?>';
var sneak_base_url = 'http://'+'<? echo get_server_name().$globals['base_url'];?>'+'/backend/sneaker.php';
var mykey = <? echo rand(100,999); ?>;


var default_gravatar = 'http://'+server_name+'/img/common/no-gravatar-2-20.jpg';
var do_animation = true;
var animating = false;
var animation_colors = Array("#ffc387", "#ffc891", "#ffcd9c", "#ffd2a6", "#ffd7b0", "#ffddba", "#ffe7cf", "#ffecd9", "#fff1e3", "#fff6ed", "#fffbf7", "transparent");
var colors_max = animation_colors.length - 1;
var current_colors = Array();
var animation_timer;



// Reload the mnm banner each 5 minutes
var mnm_banner_reload = 180000;

function play_pause() {
	if (is_playing()) {
		document.images['play-pause-img'].src = "img/common/sneak-play01.png";
		//document.getElementById('play-pause').innerHTML = '<img src="img/common/play.png">';
		if( document.getElementById('comment-input'))
			document.getElementById('comment-input').disabled=true;
		do_pause();
		
	} else {
		document.images['play-pause-img'].src = "img/common/sneak-pause01.png";
		//document.getElementById('play-pause').innerHTML = '<img src="img/common/pause.png">';
		if (document.getElementById('comment-input'))
			document.getElementById('comment-input').disabled=false;
		do_play();
	}
	return false;

}

function set_initial_color(i) {
	var j;
	if (i >= colors_max)
		j = colors_max - 1;
	else j = i;
	current_colors[i] = j;
	items[i].style.backgroundColor = animation_colors[j];
}

function animate_background() {
	if (current_colors[0] == colors_max) {
		clearInterval(animation_timer);
		animating = false;
		return;
	}
	for (i=new_items-1; i>=0; i--) {
		if (current_colors[i] < colors_max) {
			current_colors[i]++;
			items[i].style.backgroundColor = animation_colors[current_colors[i]];
		} else 
			new_items--;
	}
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
		html += '<div class="sneaker-type"><img src="img/common/sneak-chat01.png" width="20" height="16" alt="<?echo _('mensaje');?>" title="<?echo _('mensaje');?>" /></div>';
		html += '<div class="sneaker-votes">&nbsp;</div>';
		html += '<div class="sneaker-chat">'+put_smiley(to_hoygan(data.title))+'</div>';
		html += '<div class="sneaker-who">';
		html += '<a href="'+base_url+'user.php?login='+data.who.substring(0,15)+'"><img src="'+base_url+'backend/get_avatar.php?id='+data.uid+'&amp;size=20" width=20 height=20></a>';
		html += '&nbsp;<a href="'+base_url+'user.php?login='+data.who+'">'+data.who.substring(0,15)+'</a></div>';
		html += '<div class="sneaker-status">'+data.status+'</div>';
		return html;
	}

	/* All the others */
	if (data.type == 'vote')
		html += '<div class="sneaker-type"><img src="img/common/sneak-vote01.png" width="20" height="16" alt="<?echo _('voto');?>" title="<?echo _('voto');?>" /></div>';
	else if (data.type == 'problem')
		html += '<div class="sneaker-type"><img src="img/common/sneak-problem01.png" width="20" height="16" alt="<?echo _('problema');?>" title="<?echo _('problema');?>" /></div>';
	else if (data.type == 'comment')
		html += '<div class="sneaker-type"><img src="img/common/sneak-comment01.png" width="20" height="16" alt="<?echo _('HOYGAN');?>" title="<?echo _('HOYGAN');?>" /></div>';
	else if (data.type == 'new')
		html += '<div class="sneaker-type"><img src="img/common/sneak-new01.png" width="20" height="16" alt="<?echo _('nueva');?>" title="<?echo _('nueva');?>"/></div>';
	else if (data.type == 'published')
		html += '<div class="sneaker-type"><img src="img/common/sneak-published01.png" width="20" height="16" alt="<?echo _('publicada');?>" title="<?echo _('publicada');?>" /></div>';
	else
		html += '<div class="sneaker-type">'+data.type+'</div>';

	html += '<div class="sneaker-votes">'+data.votes+'/'+data.com+'</div>';
	if ("undefined" != typeof(data.cid) && data.cid > 0) anchor='#c-'+data.cid;
	else anchor='';
	html += '<div class="sneaker-story"><a href="'+data.link+anchor+'">'+to_hoygan(data.title)+'</a></div>';
	if (data.type == 'problem')
		html += '<div class="sneaker-who"><span class="sneaker-problem">&nbsp;'+data.who+'</span></div>';
	else if (data.uid > 0)  {
		html += '<div class="sneaker-who">';
		html += '<a href="'+base_url+'user.php?login='+data.who+'"><img src="'+base_url+'backend/get_avatar.php?id='+data.uid+'&amp;size=20" width=20 height=20></a>';
		html += '&nbsp;<a href="'+base_url+'user.php?login='+data.who+'">'+data.who.substring(0,15)+'</a></div>';
	} else 
		html += '<div class="sneaker-who">&nbsp;'+data.who.substring(0,15)+'</div>';
	if (data.status == '<? echo _('publicada');?>')
		html += '<div class="sneaker-status"><a href="'+base_url+'"><span class="sneaker-published">'+data.status+'</span></a></div>';
	else if (data.status == '<? echo _('descartada');?>')
		html += '<div class="sneaker-status"><a href="'+base_url+'shakeit.php?view=discarded"><span class="sneaker-discarded">'+data.status+'</span></a></div>';
	else 
		html += '<div class="sneaker-status"><a href="'+base_url+'shakeit.php">'+data.status+'</a></div>';
	return html;
}

function to_hoygan(str) 
{
	str=str.replace(/á/gi, 'a');
	str=str.replace(/é/gi, 'e');
	str=str.replace(/í/gi, 'i');
	str=str.replace(/ó/gi, 'o');
	str=str.replace(/ú/gi, 'u');

	str=str.replace(/yo/gi, 'io');
	str=str.replace(/m([pb])/gi, 'n$1');
	str=str.replace(/qu([ei])/gi, 'k$1');
	str=str.replace(/ct/gi, 'st');
	str=str.replace(/cc/gi, 'cs');
	str=str.replace(/ll([aeou])/gi, 'y$1');
	str=str.replace(/ya/gi, 'ia');
	str=str.replace(/yo/gi, 'io');
	str=str.replace(/g([ei])/gi, 'j$1');
	str=str.replace(/^([aeiou][a-z]{3,})/gi, 'h$1');
	str=str.replace(/ ([aeiou][a-z]{3,})/gi, ' h$1');
	str=str.replace(/[zc]([ei])/gi, 's$1');
	str=str.replace(/z([aou])/gi, 's$1');
	str=str.replace(/c([aou])/gi, 'k$1');

	str=str.replace(/b([aeio])/gi, 'vvv;$1');
	str=str.replace(/v([aeio])/gi, 'bbb;$1');
	str=str.replace(/vvv;/gi, 'v');
	str=str.replace(/bbb;/gi, 'b');

	str=str.replace(/oi/gi, 'oy');
	str=str.replace(/xp([re])/gi, 'sp$1');
	str=str.replace(/es un/gi, 'esun');
	str=str.replace(/(^| )h([ae]) /gi, '$1$2 ');
	str=str.replace(/aho/gi, 'ao');
	str=str.replace(/a ver /gi, 'haber ');
	str=str.replace(/ por /gi, ' x ');
	str=str.replace(/ñ/gi, 'ny');
	str=str.replace(/buen/gi, 'GÜEN');

        // benjami
	str=str.replace(/windows/gi, 'güindous');
	str=str.replace(/we/gi, 'güe');
	// str=str.replace(/'. '/gi, '');
	str=str.replace(/,/gi, ' ');
	str=str.replace(/hola/gi, 'ola');
	str=str.replace(/ r([aeiou])/gi, ' rr$1'); // cal l'expressio regular per a principi de paraula? :)


	// todo = que no canvii les url
	
	return str.toUpperCase();
}

function put_smiley(str) {
	str=str.replace(/:-{0,1}\)/gi, '<img src="img/smileys/smiley.gif" alt=":-)" title=":-)"/>');
	str=str.replace(/^;-{0,1}\)|[^t];-{0,1}\)/gi, ' <img src="img/smileys/wink.gif" alt=";)" title=";)" />');
	str=str.replace(/:-{0,1}&gt;/gi, '<img src="img/smileys/cheesy.gif" alt=":->" title=":->" />');
	str=str.replace(/:-{0,1}D|:grin:/gi, '<img src="img/smileys/grin.gif" alt=":-D" title=":-D"/>');
	str=str.replace(/:oops:|&lt;:\(/gi, '<img src="img/smileys/embarassed.gif" alt="&lt;&#58;(" title="&#58;oops&#58; &lt;&#58;(" />');
	str=str.replace(/&gt;:-{0,1}\(/gi, '<img src="img/smileys/angry.gif" alt="&gt;&#58;-(" title="&gt;&#58;-(" />');
	str=str.replace(/\?(:-){0,1}\(/gi, '<img src="img/smileys/huh.gif" alt="?(" title="?(" />');
	str=str.replace(/:-{0,1}\(/gi, '<img src="img/smileys/sad.gif" alt=":-(" title=":-(" />');
	str=str.replace(/:-{0,1}O/g, '<img src="img/smileys/shocked.gif" alt=":-O" title=":-O" />');
	str=str.replace(/8-{0,1}[D\)]|:cool:/g, '<img src="img/smileys/cool.gif" alt="8-D" title=":cool: 8-D"/>');
	str=str.replace(/:roll:/gi, '<img src="img/smileys/rolleyes.gif" alt=":roll:" title=":roll:" />');
	str=str.replace(/^:-{0,1}P| :-{0,1}P/gi, ' <img src="img/smileys/tongue.gif" alt=":-P" title=":-P" />');
	str=str.replace(/:-{0,1}x/gi, ' <img src="img/smileys/lipsrsealed.gif" alt=":-x" title=":-x" />');
	str=str.replace(/([^ps]|^):-{0,1}\//gi, '$1<img src="img/smileys/undecided.gif" alt=":-/" title=":-/ :/" />');
	str=str.replace(/:'\(|:cry:/gi, '<img src="img/smileys/cry.gif" alt=":\'(" title=":cry: :\'(" />');
	str=str.replace(/([^a-zA-Z]|^)[xX]D+|:lol:/g, '<img src="img/smileys/laugh.gif" alt="xD" title=":lol: xD" />');
	str=str.replace(/ :-{0,1}S/gi, ' <img src="img/smileys/confused.gif" alt=":-S" title=":-S :S"/>');
	str=str.replace(/:-{0,1}\|/gi, '<img src="img/smileys/blank.gif" alt=":-|" title=":-| :|"/>');
	str=str.replace(/:-{0,1}\*/gi, '<img src="img/smileys/kiss.gif" alt=":-*" title=":-* :*"/>');

	return str;
}

//]]>
</script>
<script type="text/javascript" src="http://<? echo get_server_name().$globals['base_url']; ?>js/sneak06.js.php"></script>
<?

do_banner_top();
echo '<div id="container-wide">' . "\n";

//echo '<div class="air-with-footer">';

echo '<div class="sneaker">';
echo '<div class="sneaker-legend">';
echo '<form action="" class="sneaker-control" id="sneaker-control" name="sneaker-control">';
echo '<img id="play-pause-img" onclick="play_pause()" src="img/common/sneak-pause01.png" alt="play/pause" title="play/pause">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<input type="checkbox" checked="checked" name="sneak-pubvotes" id="pubvotes-status" onclick="toggle_control(\'pubvotes\')" /><img src="img/common/sneak-vote-published01.png" width="20" height="16" title="'._('votos de publicadas').'" alt="'._('votos de publicadas').'" />';
echo '<input type="checkbox" checked="checked" name="sneak-vote" id="vote-status" onclick="toggle_control(\'vote\')" /><img src="img/common/sneak-vote01.png" width="20" height="16" title="'._('meneos').'" alt="'._('meneos').'" />';
echo '<input type="checkbox" checked="checked" name="sneak-problem" id="problem-status" onclick="toggle_control(\'problem\')" /><img src="img/common/sneak-problem01.png" width="20" height="16" alt="'._('problema').'" title="'._('problema').'"/>';
echo '<input type="checkbox" checked="checked" name="sneak-comment" id="comment-status" onclick="toggle_control(\'comment\')" /><img src="img/common/sneak-comment01.png" width="20" height="16" alt="'._('HOYGAN').'" title="'._('HOYGAN').'"/>';
echo '<input type="checkbox" checked="checked" name="sneak-new" id="new-status" onclick="toggle_control(\'new\')" /><img src="img/common/sneak-new01.png" width="20" height="16" alt="'._('nueva').'" title="'._('nueva').'"/>';
echo '<input type="checkbox" checked="checked" name="sneak-published" id="published-status" onclick="toggle_control(\'published\')" /><img src="img/common/sneak-published01.png" width="20" height="16" alt="'._('publicada').'" title="'._('publicada').'"/>';

if ($current_user->user_id > 0) $chat_checked = 'checked="checked"';
else $chat_checked = '';
echo '<input type="checkbox" '.$chat_checked.' name="sneak-chat" id="chat-status" onclick="toggle_control(\'chat\')" /><img src="img/common/sneak-chat01.png" width="20" height="16" alt="'._('mensaje').'" title="'._('mensaje').'"/>';

echo _('fisgonas').  ': <strong><span style="font-size: 140%;" id="ccnt"> </span></strong>';
echo "</form>\n";
if ($current_user->user_id > 0) {
	echo '<form name="chat_form" onsubmit="return send_chat(this);">';
	echo _('mensaje') . ': <input type="text" name="comment" id="comment-input" value="" size="90" maxlength="195" />&nbsp;<input type="submit" value="'._('enviar').'" class="sendmessage"/>';
	echo '</form>';
}
echo '</div>' . "\n";
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


for ($i=0; $i<$max_items;$i++) {
	echo '<div id="sneaker-'.$i.'" class="sneaker-item">&nbsp;';
	echo "</div>\n";


}

echo '</div>';
echo '<script type="text/javascript">setTimeout("start_sneak()", 500);</script>' . "\n";

do_footer(false);
?>
