<?
include('../libs/sneak.php');
header('Content-Type: text/javascript; charset=UTF-8');
header('Cache-Control: max-age=3600');
?>


var new_items = 0;
var max_items = <? echo $max_items; ?>;
var data_timer;
var min_update = 15000;
var next_update = 3000;
var xmlhttp;
var requests = 0;
var ping_time = 0;
var ping_start;
var total_requests = 0;
var max_requests = 2000;
var comment = '';
var last_comment_sent=0;
var comment_period = 10; //seconds
var ccnt = 0; 	// Connected counter

var play = true;

var show_vote = true;
var show_problem = true;
var show_comment = true;
var show_new = true;
var show_published = true;
var show_chat = true;
var show_post = true;
var show_pubvotes = true;


function start_sneak() {
	//xmlhttp = new myXMLHttpRequest ();
	$.ajaxSetup({timeout: 10000, async: true});

	$(document).ajaxError(function (request, settings) {
		data_timer = setTimeout('get_data()', next_update/2);
		xmlhttp = undefined;
	});

	$(document).ajaxSend(function (request, settings) {
		var date_object = new Date();
		ping_start = date_object.getTime();
	});

	$(document).ajaxStop(function (request, settings) {
		xmlhttp = undefined;
	});

	if (!get_options_cookie()) {
		check_control('vote');
		check_control('problem');
		check_control('comment');
		check_control('new');
		check_control('published');
		check_control('chat');
		check_control('post');
		check_control('pubvotes');
	}
	do_play();
	return false;
}

function abort_request () {
	clearTimeout(data_timer);
	if ("object" == typeof(xmlhttp)) {
		xmlhttp.abort();
	}
}

function get_data() {
	abort_request();
	url=sneak_base_url+'?k='+mykey+'&time='+ts+'&v='+my_version+'&r='+total_requests;
	url = url + get_options_string();
	if(comment.length > 0) {
		xmlhttp=$.post(url, {'chat': comment}, received_data);
		comment = '';
	} else {
		xmlhttp=$.get(url, {}, received_data);
	}
	requests++;
	total_requests++;
	return false;
}

function received_data(data) {
	// Update ping time
	var date_object = new Date();
	if (ping_time == 0) 
		ping_time = date_object.getTime() - ping_start;
	else
		ping_time = parseInt(0.6 * ping_time + 0.4 * (date_object.getTime() - ping_start));

	$('#ping').html(ping_time);

	var new_data = Array();
	eval (data);
	$('#ccnt').html(ccnt);
	new_items= new_data.length;
	if(new_items > 0) {
		if (do_animation) clear_animation();
		next_update = Math.round(0.5*next_update + 0.5*min_update/(new_items*2));

		//Remove old items
		$('#items').children().slice(max_items-new_items).remove();

		for (i=new_items-1; i>=0 ; i--) {
			html = $('<div class="sneaker-item">'+to_html(new_data[i])+'</div>');
			set_initial_display(html, i);
			$('#items').prepend(html);
		}
		if (do_animation) {
			animation_timer = setInterval('animate_background()', 100);
			animating = true;
		}
	} else next_update = Math.round(next_update*1.05);
	if (next_update < 3000) next_update = 3000;
	if (next_update > min_update) next_update = min_update;
	if (requests > max_requests) {
		if ( !confirm('<? echo _('Fisgón: ¿desea continuar conectado?');?>') ) {
			mnm_banner_reload = 0;
			return;
		}
		requests = 0;
		total_requests = 0;
		next_update = 100;
	}
	data_timer = setTimeout('get_data()', next_update);
}

function send_chat(form) {
	var currentTime = new Date();

	if(check_command(form.comment.value)) return false;

	if(!is_playing()) {
		alert("<? echo _('está en pausa'); ?>");
		return false;
	}
	if(show_chat == false) {
		alert("<? echo _('tiene deshabilitado los comentarios'); ?>");
		return false;
	}
	if(form.comment.value.length < 4) {
		alert("<? echo _('mensaje demasiado corto'); ?>");
		return false;
	}
	if( currentTime.getTime() < last_comment_sent + (comment_period*1000)) {
		alert("<? echo _('sólo se puede enviar un mensaje cada');?> " + comment_period + " <? echo _('segundos');?>");
		return false;
	}
	abort_request();
	comment=form.comment.value;
	last_comment_sent = currentTime.getTime();
	form.comment.value='';
	if (do_animation && animating) {
		data_timer = setTimeout('get_data()', 500)
	} else {
		get_data();
	}
	requests = 0;
	return false;
}

function check_command(comment) {
	if (!comment.match(/^!/)) return false;
	if (comment.match(/^!jefa/)) {
		window.location = 'telnet.php';
		return true;
	}
	if (comment.match(/^!fisgona/)) {
		window.location = 'sneak.php';
		return true;
	}
	return false;
}

function check_control(what) {
	var status = document.getElementById(what+'-status');
	if (!status) return false;
	if (status.checked) {
		eval('show_'+what+' = true');
		return true;
	} else {
		eval('show_'+what+' = false');
		return false;
	}
}

function set_control(what) {
	var status = document.getElementById(what+'-status');
	if (!status) return false;
	eval('status.checked = show_'+what);
}

function toggle_control(what) {
	abort_request();
	check_control(what);
	ts-=3600;
	set_options_cookie();
	if (is_playing()) {
		data_timer = setTimeout('get_data()', 100)
		$('#items').children().html('&nbsp;');
	}
	requests = 0;
	return false;
}

function get_options_string() {
	var options = '';
	if (show_chat == false) options += '&nochat=1';
	if (show_post == false) options += '&nopost=1';
	if (show_vote == false) options += '&novote=1';
	if (show_problem == false) options += '&noproblem=1';
	if (show_comment == false) options += '&nocomment=1';
	if (show_new == false) options += '&nonew=1';
	if (show_published == false) options += '&nopublished=1';
	if (show_pubvotes == false) options += '&nopubvotes=1';
	if (show_friends == true) options += '&friends=1';
	return options;
}

function set_options_from_string(string) {
	if (string.match(/&nochat=1/)) {
		show_chat = false; 
	}
	set_control('chat');
	if (string.match(/&nopost=1/)) {
		show_post = false; 
	}
	set_control('post');
	if (string.match(/&novote=1/)) {
		show_vote = false;
	}
	set_control('vote');
	if (string.match(/&noproblem=1/)) {
		show_problem = false;
	}
	set_control('problem');
	if (string.match(/&nocomment=1/)) {
		show_comment = false;
	}
	set_control('comment');
	if (string.match(/&nonew=1/)) {
		show_new = false;
	}
	set_control('new');
	if (string.match(/&nopublished=1/)) {
		show_published = false;
	}
	set_control('published');
	if (string.match(/&nopubvotes=1/)) {
		show_pubvotes = false;
	}
	set_control('pubvotes');
}

function set_options_cookie() {
	var options = get_options_string();
	createCookie('mnm-sneak-options', options,1000);
}

function get_options_cookie() {
	var options = readCookie('mnm-sneak-options');
	if (options != null) {
		set_options_from_string(options);
		return true;
	}
	return false;
}

function createCookie(name,value,days)
{
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	} else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name)
{
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name)
{
	createCookie(name,"",-1);
}

function is_playing () {
	return play;
}

function do_pause() {
	abort_request();
	play = false;
}

function do_play() {
	play = true;
	get_data();
}

