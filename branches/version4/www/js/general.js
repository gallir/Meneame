<?
/****************************
*
* WARN
*	  this files should be called from a generalxx.js.php file
*
*****************************/
?>

function redirect(url)
{
	document.location=url;
	return false;
}

function menealo(user, id)
{
	var url = base_url + "backend/menealo.php";
	var content = "id=" + id + "&user=" + user + "&key=" + base_key + "&l=" + link_id + "&u=" + document.referrer;
	url = url + "?" + content;
	disable_vote_link(id, -1, "...", '');
	$.getJSON(url,
		 function(data) {
				parseLinkAnswer(id, data);
		}
	);
	reportAjaxStats('vote', 'link');
}

function menealo_comment(user, id, value)
{
	var url = base_url + "backend/menealo_comment.php";
	var content = "id=" + id + "&user=" + user + "&value=" + value + "&key=" + base_key + "&l=" + link_id ;
	url = url + "?" + content;
	$.getJSON(url,
		 function(data) {
			if (data.error) {
				alert("<? echo _('Error:') ?> "+data.error);
				return false;
			} else {
				$('#vc-'+id).html(data.votes+"");
				$('#vk-'+id).html(data.karma+"");
				if (data.image.length > 0) {
					$('#c-votes-'+id).html('<img src="'+data.image+'"/>');
				}
			}
		}
	);
	reportAjaxStats('vote', 'comment');
}

function menealo_post(user, id, value)
{
	var url = base_url + "backend/menealo_post.php";
	var content = "id=" + id + "&user=" + user + "&value=" + value + "&key=" + base_key + "&l=" + link_id ;
	url = url + "?" + content;
	$.getJSON(url,
		 function(data) {
			if (data.error) {
				alert("<? echo _('Error:') ?> "+data.error);
				return false;
			} else {
				$('#vc-'+id).html(data.votes+"");
				$('#vk-'+id).html(data.karma+"");
				if (data.image.length > 0) {
					$('#c-votes-'+id).html('<img src="'+data.image+'"/>');
				}
			}
		}
	);
	reportAjaxStats('vote', 'post');
}


function disable_vote_link(id, value, mess, background) {
	if (value < 0) span = '<span class="negative">';
	else span = '<span>';
	$('#a-va-' + id).html(span+mess+'</span>');
	if (background.length > 0) $('#a-va-' + id).css('background', background);
}

function parseLinkAnswer (id, link) {
	$('#problem-' + id).hide();
	if (link.error || id != link.id) {
		disable_vote_link(id, -1, "<? echo _('grr...') ?>", '');
		alert("<? echo _('Error:') ?> "+link.error);
		return false;
	}
	votes = parseInt(link.votes)+parseInt(link.anonymous);
	if ($('#a-votes-' + link.id).html() != votes) {
		$('#a-votes-' + link.id).hide();
		$('#a-votes-' + link.id).html(votes+"");
		$('#a-votes-' + link.id).fadeIn('slow');
	}
	$('#a-neg-' + link.id).html(link.negatives+"");
	$('#a-usu-' + link.id).html(link.votes+"");
	$('#a-ano-' + link.id).html(link.anonymous+"");
	$('#a-karma-' + link.id).html(link.karma+"");
	disable_vote_link(link.id, link.value, link.vote_description, '');
	return false;
}

function securePasswordCheck(field) {
	if (field.value.length > 5 && field.value.match("^(?=.{6,})(?=(.*[a-z].*))(?=(.*[A-Z0-9].*)).*$", "g")) {
		if (field.value.match("^(?=.{8,})(?=(.*[a-z].*))(?=(.*[A-Z].*))(?=(.*[0-9].*)).*$", "g")) {
			field.style.backgroundColor = "#8FFF00";
		} else {
			field.style.backgroundColor = "#F2ED54";
		}
	} else {
		field.style.backgroundColor = "#F56874";
	}
	return false;
}

function checkEqualFields(field, against) {
	if(field.value == against.value) {
		field.style.backgroundColor = '#8FFF00';
	} else {
		field.style.backgroundColor = "#F56874";
	}
	return false;
}

function enablebutton (button, button2, target)
{
	var string = target.value;
	if (button2 != null) {
		button2.disabled = false;
	}
	if (string.length > 0) {
		button.disabled = false;
	} else {
		button.disabled = true;
	}
}

function checkfield (type, form, field)
{
	var url = base_url + 'backend/checkfield.php?type='+type+'&name=' + encodeURIComponent(field.value);
	$.get(url,
		 function(html) {
			if (html == 'OK') {
				$('#'+type+'checkitvalue').html('<span style="color:black">"' + encodeURI(field.value) + '": ' + html + '</span>');
				form.submit.disabled = '';
			} else {
				$('#'+type+'checkitvalue').html('<span style="color:red">"' + encodeURI(field.value) + '": ' + html + '</span>');
				form.submit.disabled = 'disabled';
			}
		}
	);
	return false;
}

function check_checkfield(fieldname, mess) {
	field = document.getElementById(fieldname);
	if (field && !field.checked) {
		alert(mess);
		// box is not checked
		return false;
	}
}

function report_problem(frm, user, id) {
	if (frm.ratings.value == 0)
		return;
	if (! confirm("<? echo _('confirme desea votar:') ?> «" + frm.ratings.options[frm.ratings.selectedIndex].text +"»") ) {
		frm.ratings.selectedIndex=0;
		return false;
	}
	var content = "id=" + id + "&user=" + user + '&value=' +frm.ratings.value + "&key=" + base_key  + "&l=" + link_id + "&u=" + document.referrer;
	var url=base_url + "backend/problem.php?" + content;
	$.getJSON(url,
		 function(data) {
			parseLinkAnswer(id, data);
		}
	);
	reportAjaxStats('vote', 'link');
	return false;
}

// Get voters by Beldar <beldar.cat at gmail dot com>
// Generalized for other uses (gallir at gmail dot com)
function get_votes(program,type,container,page,id) {
	var url = base_url + 'backend/'+program+'?id='+id+'&p='+page+'&type='+type+"&key="+base_key;
	$('#'+container).load(url);
	reportAjaxStats('html', program);
}

// Modal functions
//
$.extend($.modal.defaults, {
	closeHTML: '<a class="modalCloseImg" title="Close">x</a>',
	opacity: "50"
});

function modal_from_ajax(url, title) {
	if (typeof(title) == "undefined") title = '&nbsp';

	$.modal('<div class="header" id="modalHeader"><div id="modalTitle">'+title+'</div></div><div class="content" id="modalContent"><? echo ('cargando...')?></div>',
			{});
	$.get(url, function(data){
	// create a modal dialog with the data
		$('#modalContent').html(data);
	});
	reportAjaxStats('modal', 'view');
}

// This function report the ajax request to stats events if enabled in your account
// http://code.google.com/intl/es/apis/analytics/docs/eventTrackerOverview.html
function reportAjaxStats(category, action) {
	if (typeof(pageTracker) !=	'undefined' && typeof(pageTracker._trackEvent) !=  'undefined') {
		pageTracker._trackEvent(category, action);
	}
}

function bindTogglePlusMinus(img_id, link_id, container_id) {
	$(document).ready(function (){
		$('#'+link_id).bind('click',
			function() {
				if ($('#'+img_id).attr("src") == plus){
					$('#'+img_id).attr("src", minus);
				}else{
					$('#'+img_id).attr("src", plus);
				}
				$('#'+container_id).slideToggle("fast");
				return false;
			}
		);
	});
}


/*/
 *	JSOC - An object Cache framework for JavaScript
 *	version 0.12.0 [beta]
 * http://dev.webframeworks.com/dist/JSOC-license.txt
 * version: 0.12.0
/*/

JSOC = function(){
	var Cache = {};
	return {
		"get":function(n){
			var obj = {}, val = Cache[n];
			obj[n] = val;
			if(val) return obj;
		},
		"getMulti":function(l){
			var a = [];
			for (var k in l) a.push(this.get(l[k]));
			return a;
		},
		"getType":function(t){
			var a = [];
			for (var o in Cache) if(typeof(Cache[o])==t.toLowerCase()){a.push(this.get(o))}
			return a;
		},
		"set":function(n,v){
			if(Cache[n]) delete(Cache[n]);
			Cache[n]=v;
			if (arguments[2]){
				var ttl = arguments[2].ttl || null;
				if(ttl) var self = this, to = setTimeout(function(){self.remove(n)}, ttl);
			}
			return (Cache[n])?1:0;
		},
		"add":function(n,v){
			if(!Cache[n]){
				Cache[n]=v;
				if (arguments[2]){
					var ttl = arguments[2].ttl || null;
					if(ttl) var self = this, to = setTimeout(function(){self.remove(n)}, ttl);
				}
				return (Cache[n])?1:0;
			}
		},
		"replace":function(n,v){
			if(Cache[n]){
				delete(Cache[n]);
				Cache[n]=v;
				if (arguments[2]){
					var ttl = arguments[2].ttl || null;
					if(ttl) var self = this, to = setTimeout(function(){self.remove(n)}, ttl);
				}
				return (Cache[n])?1:0;
			}
		},
		"remove":function(n){
			delete(Cache[n]);
			return (!Cache[n])?1:0;
		},
		"flush_all":function(){
			for(var k in Cache) delete(Cache[k]);
			return 1;
		}
	}
}

function clk(f, id) {
	f.href=base_url + 'backend/go.php?id=' + id;
	return true;
}

/**************************************
Tooltips functions
***************************************/
/**
  Stronglky modified, onky works with DOM2 compatible browsers.
	Ricardo Galli
  From http://ljouanneau.com/softs/javascript/tooltip.php
 */

if (typeof(JSOC) != "undefined") {
	// create the tooltip object
	function tooltip(){}
	// setup properties of tooltip object
	tooltip.id="tooltip";
	tooltip.main=null;
	tooltip.offsetx = 10;
	tooltip.offsety = 10;
	tooltip.shoffsetx = 8;
	tooltip.shoffsety = 8;
	tooltip.x = 0;
	tooltip.y = 0;
	tooltip.tooltipText=null;
	tooltip.title_saved='';
	tooltip.saveonmouseover=null;
	tooltip.timeout = null;
	tooltip.active = false;

	tooltip.cache = new JSOC();

	tooltip.ie = (document.all)? true:false;		// check if ie
	if(tooltip.ie) tooltip.ie5 = (navigator.userAgent.indexOf('MSIE 5')>0);
	else tooltip.ie5 = false;
	tooltip.dom2 = ((document.getElementById) && !(tooltip.ie5))? true:false; // check the W3C DOM level2 compliance. ie4, ie5, ns4 are not dom level2 compliance !! grrrr >:-(
}


/**
* Open ToolTip. The title attribute of the htmlelement is the text of the tooltip
* Call this method on the mouseover event on your htmlelement
* ex :	<div id="myHtmlElement" onmouseover="tooltip.show(this)"...></div>
*/

tooltip.show = function (event, text) {
	  // we save text of title attribute to avoid the showing of tooltip generated by browser
	if (this.dom2  == false ) return false;
	if (this.tooltipText == null) {
		this.tooltipText = document.createElement("div");
		this.tooltipText.setAttribute("id", "tooltip-text");
		document.body.appendChild(this.tooltipText);
	}
	this.saveonmouseover=document.onmousemove;
	document.onmousemove = this.mouseMove;
	this.mouseMove(event); // This already moves the div to the right position
	this.setText(text);
	this.tooltipText.style.visibility ="visible";
	this.active = true;
	return false;
}


tooltip.setText = function (text) {
	this.tooltipText.innerHTML=text;
	return false;
}

/**
* hide tooltip
* call this method on the mouseout event of the html element
* ex : <div id="myHtmlElement" ... onmouseout="tooltip.hide(this)"></div>
*/
tooltip.hide = function (event) {
	if (this.dom2  == false) return false;
	document.onmousemove=this.saveonmouseover;
	this.saveonmouseover=null;
	if (this.tooltipText != null ) {
		this.tooltipText.style.visibility = "hidden";
		this.tooltipText.innerHTML='';
	}
	this.active = false;
}



// Moves the tooltip element
tooltip.mouseMove = function (e) {
   // we don't use "this", but tooltip because this method is assign to an event of document
   // and so is dreferenced

	if (tooltip.ie) {
		tooltip.x = event.clientX;
		tooltip.y = event.clientY;
	} else {
		tooltip.x = e.pageX;
		tooltip.y = e.pageY;
	}
	tooltip.moveTo( tooltip.x +tooltip.offsetx , tooltip.y + tooltip.offsety);
}

// Move the tooltip element
tooltip.moveTo = function (xL,yL) {
	if (this.ie) {
		xL +=  document.documentElement.scrollLeft;
		yL +=  document.documentElement.scrollTop;
	}
	if (this.tooltipText.clientWidth > 0  && document.documentElement.clientWidth > 0 && xL > document.documentElement.clientWidth * 0.55) {
		xL = xL - this.tooltipText.clientWidth - 2*this.offsetx;
	}
	this.tooltipText.style.left = xL +"px";
	this.tooltipText.style.top = yL +"px";
	xLS = xL + this.shoffsetx;
	yLS = yL + this.shoffsety;
}

// Show the content of a given comment
tooltip.c_show = function (event, type, element, link) {
	  // we save text of title attribute to avoid the showing of tooltip generated by browser
	if (this.dom2  == false ) return false;
	if (element == 0 && link > 0) { // It's a #0  from a comment
		this.ajax_delayed(event,'get_link.php',link);
		return;
	}
	if (type == 'id') {
		target = $('#c-'+element+'>:first');
		author_target = $('#cauthor-'+element);
		if (target.length == 0 || author_target.length == 0)  {
			this.ajax_delayed(event,'get_comment_tooltip.php',element+"&link="+link);
			return;
		}
		text = '<strong>'+author_target.html()+'</strong><br/>'+target.html();
	} else if (type == 'order') {
		this.ajax_delayed(event,'get_comment_tooltip.php',element+"&link="+link);
		return;
	} else {
		text = element;
	}
	return this.show(event, text);
}


tooltip.clear = function (event) {
	if (this.timeout != null) {
		clearTimeout(this.timeout);
		this.timeout = null;
	}
	this.hide(event);
}

tooltip.ajax_delayed = function (event, script, id, maxcache) {
	maxcache = maxcache || 600000; // 10 minutes in cache
	if (this.active) return false;
	if ((object = this.cache.get(script+id)) != undefined) {
		tooltip.show(event, object[script+id]);
	} else {
		this.show(event, "<? echo ('cargando...')?>");
		this.timeout = setTimeout("tooltip.ajax_request('"+script+"', '"+id+"', "+maxcache+")", 100);
	}
}

tooltip.ajax_request = function(script, id, maxcache) {
	var url = base_url + 'backend/'+script+'?id='+id;
	tooltip.timeout = null;
	$.ajax({
		url: url,
		dataType: "html",
		success: function(html) {
			tooltip.cache.set(script+id, html, {'ttl':maxcache});
			tooltip.setText(html);
		}
	});
	reportAjaxStats('tooltip', 'ajax');
}
