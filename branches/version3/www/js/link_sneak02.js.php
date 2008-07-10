<?
include('../config.php');
header('Content-Type: text/javascript; charset=UTF-8');
header('Cache-Control: max-age=3600');
?>

var ts=<? echo (time()-86400); ?>; // just due a freaking IE cache problem
var server_name = '<? echo get_server_name(); ?>';
var base_url = '<? echo $globals['base_url']; ?>';
var sneak_base_url = 'http://'+'<? echo get_server_name().$globals['base_url'];?>'+'backend/link_sneaker.php';


var default_gravatar = 'http://'+server_name+'/img/mnm/no-gravatar-2-20.jpg';
var do_animation = true;
var animating = false;
var animation_colors = Array("#ffc387", "#ffc891", "#ffcd9c", "#ffd2a6", "#ffd7b0", "#ffddba", "#ffe7cf", "#ffecd9", "#fff1e3", "#fff6ed", "#fffbf7", "transparent");
var colors_max = animation_colors.length - 1;
var current_colors = Array();
var animation_timer;

var new_items = 0;
var max_items = 10;
var request_timer;
var min_update = 30000;
var next_update = 7000;
var sneak_xmlhttp;
var requests = 0;
var max_requests = 500;

var play = true;


function start_link_sneak() {
	get_data();
	return false;
}

function get_data() {
	url=sneak_base_url+'?time='+ts+'&v=-1&r='+requests+'&link='+link_id;
	xmlhttp=$.get(url, {}, received_data);
	requests++;
	return false;
}

function received_data(data) {
	// We get new_data array
	var new_data = Array();
	if (data.match(/^ERROR/)) {
		alert(data);
		return false;
	}
	eval (data);
	if (link_votes_0 != link_votes || link_negatives_0 != link_negatives || link_karma_0 != link_karma) {
		updateLinkValues (link_id, link_votes, link_negatives, link_karma, 0);
		link_votes_0 = link_votes;
		link_negatives_0 = link_negatives;
		link_karma_0 = link_karma;
	}
	new_items= new_data.length;
	if(new_items > 0) {
		if (do_animation) clearInterval(animation_timer);
		next_update = Math.round(0.5*next_update + 0.5*min_update/(new_items*2));
		shift_items(new_items);
		for (i=0; i<new_items && i<max_items; i++) {
			$('#sneaker-'+i).html(to_html(new_data[i]));
			if (do_animation) set_initial_color(i);
		}
		if (do_animation) {
			animation_timer = setInterval('animate_background()', 100);
			animating = true;
		}
	} else next_update = Math.round(next_update*1.25);

	if (next_update < 10000) next_update = 10000;
	if (next_update > min_update) next_update = min_update;
	if (requests > max_requests) {
		if ( !confirm('<? echo _('Fisgón: ¿desea continuar conectado?');?>') ) {
			return;
		}
		requests = 0;
		next_update = 100;
	}
	timer = setTimeout('get_data()', next_update);
	reportAjaxStats('/mini_sneaker_request');
}

function shift_items(n) {
	for (i=max_items-1;i>=n;i--) {
		j = i - n;
		$('#sneaker-'+i).html($('#sneaker-'+j).html());
	}
}

function clear_items() {
	for (i=0;i<max_items;i++) {
		$('#sneaker-'+i).html('&nbsp;');
	}
}


///////////////////////
///// HTML functions

function set_initial_color(i) {
	var j;
	if (i >= colors_max)
		j = colors_max - 1;
	else j = i;
	current_colors[i] = j;
	$('#sneaker-'+i).css('background', animation_colors[j]);
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
			$('#sneaker-'+i).css('background', animation_colors[current_colors[i]]);
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

	html = '<div class="mini-sneaker-ts">'+timeStr+'<\/div>';

	tooltip_ajax_call= "onmouseout=\"tooltip.clear(event);\"  onclick=\"tooltip.clear(this);\"";
	if (data.type == 'vote')
		if (data.status == '<? echo _('publicada');?>')
			html += '<div class="mini-sneaker-type" '+tooltip_ajax_call+'><img src="'+base_url+'img/common/sneak-vote-published01.png" width="21" height="17" alt="<?echo _('voto');?>" title="<?echo _('voto');?>" /><\/div>';
		else
			html += '<div class="mini-sneaker-type" '+tooltip_ajax_call+'><img src="'+base_url+'img/common/sneak-vote01.png" width="21" height="17" alt="<?echo _('voto');?>" title="<?echo _('voto');?>" /><\/div>';
	else if (data.type == 'problem')
		html += '<div class="mini-sneaker-type" '+tooltip_ajax_call+'><img src="'+base_url+'img/common/sneak-problem01.png" width="21" height="17" alt="<?echo _('problema');?>" title="<?echo _('problema');?>" /><\/div>';
	else if (data.type == 'comment') {
		tooltip_ajax_call += " onmouseover=\"return tooltip.ajax_delayed(event, 'get_comment_tooltip.php', '"+data.id+"', 10000);\"";
		html += '<div class="mini-sneaker-type"><img src="'+base_url+'img/common/sneak-comment01.png" width="21" height="17" alt="<?echo _('comentario');?>" '+tooltip_ajax_call+'/><\/div>';
	} else
		html += '<div class="mini-sneaker-type">'+data.type+'<\/div>';

	html += '<div class="mini-sneaker-votes">'+data.votes+'/'+data.com+'<\/div>';
	if (data.type == 'problem')
		html += '<div class="mini-sneaker-who"><span class="sneaker-problem">&nbsp;'+data.who+'<\/span><\/div>';
	else if (data.uid > 0)  {
		html += '<div class="mini-sneaker-who">';
		if (data.icon != undefined && data.icon.length > 0) {
			html += '<a href="'+base_url+'user.php?login='+data.who+'"><img src="'+data.icon+'" width=20 height=20 /><\/a>';
		}
		html += '&nbsp;<a href="'+base_url+'user.php?login='+data.who+'">'+data.who.substring(0,15)+'<\/a><\/div>';
	} else 
		html += '<div class="mini-sneaker-who">&nbsp;'+data.who.substring(0,15)+'<\/div>';


	if (data.status == '<? echo _('publicada');?>')
		html += '<div class="mini-sneaker-status"><a href="'+base_url+'"><span class="sneaker-published">'+data.status+'<\/span><\/a><\/div>';
	else if (data.status == '<? echo _('descartada');?>')
		html += '<div class="mini-sneaker-status"><a href="'+base_url+'shakeit.php?view=discarded"><span class="sneaker-discarded">'+data.status+'<\/span><\/a><\/div>';
	else 
		html += '<div class="mini-sneaker-status"><a href="'+base_url+'shakeit.php">'+data.status+'<\/a><\/div>';

	return html;
}

