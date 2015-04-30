<?php 
include('../config.php');
header('Content-Type: text/html; charset=utf-8'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://www.netvibes.com/api/0.3/profile">
<title>minifisgón</title>
<link rel="stylesheet" type="text/css" href="http://www.netvibes.com/api/0.3/style.css" />
<link rel="icon" type="image/png" href="http://<?php echo get_server_name().$globals['base_url']; ?>favicon.ico" />
<style type="text/css">
.sneaker { font-size: 8pt; }

.sneaker strong { font-size: 95%; color: #FF9400; }
.sneaker a { text-decoration: none; }

.sneakeritem { width: 100%; clear: both; border-bottom: 1px solid #FFE2C5; text-align: center; overflow: hidden; padding: 5px 0 5px 0; }
/*neaker-item" style="width: 100%;clear: both;border-bottom: 1px solid #FFE2C5;padding: 5px 0 5px 0;text-align: center;overflow: hidden;">*/
.sneakertype {float: left;display: block;width: 8%;}
.sneakervotes { float: left;display: block;width: 12%; }
.sneakerstory {float: left;display: block;width: 61%;text-align: left;}
/* "sneaker-story" style="float: left;display: block;width: 61%;text-align: left;margin-right: 2px; */
.sneakerwho { float: right;display: block;width: 16%;text-align: right;overflow: hidden; font-size:7pt;}
/*neaker-who" style="float: left;display: block;width: 15%;text-align: right;overflow: hidden;font-size:7pt; */
</style>
<script type="text/javascript" src="http://www.netvibes.com/api/0.3/emulation.js"></script>
<script type="text/javascript">
//<![CDATA[
var initialized = false;
var enabled = true;
var items = Array();
var ts=<?php echo (time()-3600); ?>;
var busy = false;
var animating = false;
var base_url = 'http://<?php echo get_server_name().$globals['base_url']; ?>backend/sneaker.php';
var mykey = <?php echo rand(100,999); ?>;
var items = Array();
var new_items = 0;
var max_items =  '<?php if(empty($_COOKIE['minifisgon_items'])) echo '15'; else echo $_COOKIE['minifisgon_items']; ?>';
var min_update = 20000;
var next_update =  '<?php if(empty($_COOKIE['minifisgon_secs'])) echo '20'; else echo $_COOKIE['minifisgon_secs']; ?>';
var requests = 0;
var max_requests = 1000;
var timer;
NV_ONLOAD = get_data;


function get_data() {
	if (busy || ! enabled) return;
	busy = true;
	if (!initialized) {
		timer = new PeriodicalExecuter(get_data, next_update);
		initialized = true;
	}
	var url=base_url+'?k='+mykey+'&time='+ts+'&v=-1&items='+max_items+'&r='+requests+'&nochat=1&nv';
	if(!NV_AJAX_REQUEST_URL) var NV_AJAX_REQUEST_URL= 'http://www.netvibes.com/ajaxProxy.php';
	var requestx = new Ajax.Request(NV_AJAX_REQUEST_URL + '?url=' + escape(url), { method: 'get', onSuccess: received_data });
	requests++;
	return false;
}

function received_data(xmlhttp) {
	var htm;
	if (xmlhttp.readyState != 4) return;
	busy = false;
	if (xmlhttp.status == 200 && xmlhttp.responseText.length > 10) {
		// We get new_data array
		var new_data = Array();
		eval (xmlhttp.responseText);
		new_items= new_data.length;
		if(new_items > 0) {
			shift_items(new_items);
			htm = '<div class="sneaker"><div class="sneakeritem"><div class="sneakertype"><strong>qué</strong></div><div class="sneakervotes"><strong><abbr title="meneos">me</abbr></strong></div><div class="sneakerstory">&nbsp;<strong>noticia</strong></div><div class="sneakerwho">&nbsp;<strong>quién</strong></div></div>';

			for (i=0; i<new_items &&  i<max_items; i++) {
				items[i] = '<div class="sneakeritem">';
				items[i] += to_html(new_data[i]);
				items[i] += '</div>';
			}

			for (i=0; i<max_items; i++) {
				htm += items[i];
			}
			htm += '</div>';
			NV_CONTENT.innerHTML = htm;
		}
	}
	if (requests > max_requests) {
		enabled = false;
		NV_CONTENT.innerHTML = '<p>Disabled, are you there?, reload or edit...</p>';
		requests = 0;
	}
}

function shift_items(n) {
	for (i=max_items-1;i>=n;i--) {
		items[i] = items[i-n];
	}
}

function clear_items() {
	for (i=0;i<max_items;i++) {
		items[i] = '';
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

	var html= '';
	/* All the others */
	if (data.type == 'vote')
		if (data.status == '<?php echo _('publicada');?>')
			html += '<div class="sneakertype"><img src="http://<?php echo get_server_name().$globals['base_url'];?>netvibes/icons/sneak-vote-publishedS.png" width="15" height="12" alt="voto" title="voto publicada" /></div>';
		else
			html += '<div class="sneakertype"><img src="http://<?php echo get_server_name().$globals['base_url'];?>netvibes/icons/sneak-voteS.png" width="15" height="12" alt="voto" title="voto pendiente" /></div>';
	else if (data.type == 'problem')
		html += '<div class="sneakertype"><img src="http://<?php echo get_server_name().$globals['base_url'];?>netvibes/icons/sneak-problemS.png" width="15" height="12" alt="problema" title="problema" /></div>';
	else if (data.type == 'comment')
		html += '<div class="sneakertype"><img src="http://<?php echo get_server_name().$globals['base_url'];?>netvibes/icons/sneak-commentS.png" width="15" height="12" alt="comentario" title="comentario" /></div>';
	else if (data.type == 'new')
		html += '<div class="sneakertype""><img src="http://<?php echo get_server_name().$globals['base_url'];?>netvibes/icons/sneak-newS.png" width="15" height="12" alt="nueva" title="nueva"/></div>';
	else if (data.type == 'published')
		html += '<div class="sneakertype"><img src="http://<?php echo get_server_name().$globals['base_url'];?>netvibes/icons/sneak-publishedS.png" width="15" height="12" alt="publicada" title="publicada" /></div>';
	else if (data.type == 'discarded')
		html += '<div class="sneakertype"><img src="http://<?php echo get_server_name().$globals['base_url'];?>netvibes/icons/sneak-rejectS.png" width="15" height="12" alt="publicada" title="descartada" /></div>';
	else if (data.type == 'edited')
		html += '<div class="sneakertype"><img src="http://<?php echo get_server_name().$globals['base_url'];?>netvibes/icons/sneak-edit-noticeS.png" width="15" height="12" alt="publicada" title="editada" /></div>';
	else if (data.type == 'cedited')
		html += '<div class="sneakertype"><img src="http://<?php echo get_server_name().$globals['base_url'];?>netvibes/icons/sneak-edit-commentS.png" width="15" height="12" alt="publicada" title="comentario editado" /></div>';
	else
		html += '<div class="sneakertype">'+data.type+'</div>';

	html += '<div class="sneakervotes">'+data.votes+'</div>';
	html += '<div class="sneakerstory"><a href="http://<?php echo get_server_name(); ?>'+data.link+'">'+data.title+'</a></div>';
	if (data.type == 'problem')
		html += '<div class="sneakerwho"><span class="sneakerproblem">'+data.who+'</span></div>';
	else if (data.uid > 0) 
		html += '<div class="sneakerwho"><a href="http://<?php echo get_server_name().$globals['base_url'];?>user.php?login='+data.who+'">'+data.who+'</a></div>';
	else 
		html += '<div class="sneakerwho">'+data.who+'</div>';
	return html;
}
//]]>
</script>
</head>
<body>
<p>Loading ...</p>

<form class="configuration" method="post" action="">
<fieldset>
  <label>Items :</label>
  <select name="minifisgon_items">
 <?php for ($i = 10; $i <= 25 ; $i+=5) {
	if ($max_items == $i ) $sel = 'selected="selected"';
	else $sel='';
	echo '<option value="'.$i.'" '.$sel. '>'.$i.'</option>';
	}
?>
  </select>
  <label>Segundos :</label>
  <select name="minifisgon_secs">
 <?php for ($i = 10; $i <= 60 ; $i+=5) {
	if ($next_update == $i ) $sel = 'selected="selected"';
	else $sel='';
	echo '<option value="'.$i.'" '.$sel. '>'.$i.'</option>';
	}
?>
  </select>
<input type="submit" value="ok" />
</fieldset>
</form>
</body>
</html>
