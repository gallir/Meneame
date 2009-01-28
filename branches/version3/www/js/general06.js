/*******************
VOTES FUNCTIONS
BASIC FUNCTIONS
************************************/

function menealo(user, id, htmlid, md5)
{
	var url = base_url + "backend/menealo.php";
	var content = "id=" + id + "&user=" + user + "&md5=" + md5;
	url = url + "?" + content;
	disable_vote_link(id, "...", '#FFC8AF');
	$.getJSON(url,  
		 function(data) {
				parseLinkAnswer(htmlid, data);
		}
	);
	reportAjaxStats('vote', 'link');
}

function menealo_comment(user, id, value)
{
	var url = base_url + "backend/menealo_comment.php";
	var content = "id=" + id + "&user=" + user + "&value=" + value;
	var myid = 'comment-'+id;
	url = url + "?" + content;
	$.getJSON(url, 
		 function(data) {
			if (data.error) {
				alert("Error: "+data.error);
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
	var content = "id=" + id + "&user=" + user + "&value=" + value;
	var myid = 'comment-'+id;
	url = url + "?" + content;
	$.getJSON(url,
		 function(data) {
			if (data.error) {
				alert("Error: "+data.error);
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


function disable_vote_link(id, mess, background) {
	$('#a-va-' + id).html('<span>'+mess+'</span>');
	$('#a-va-' + id).css('background', background);
}

function parseLinkAnswer (id, link)
{
	$('#problem-' + id).hide();
	if (link.error || id != link.id) {
		disable_vote_link(id, "grr...", '#FFCBAA');
		alert("Error: "+link.error);
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
	if (link.value > 0) {
		disable_vote_link(link.id, "¡chachi!", '#FFFFFF');
	} else if (link.value < 0) {
		disable_vote_link(link.id, ":-(", '#FFFFFF');
	}
	return false;
}

function securePasswordCheck(field) {
	/*La función comprueba si la clave contiene al menos
	 *ocho caracteres e incluye mayúsculas, minúsculas y números.
	 *
	 * Function checks if the password provided contains at least
	 * eight chars, including upper, lower and numbers.
	 *
	 * jotape - jplopez.net */

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

function report_problem(frm, user, id, md5 /*id, code*/) {
	if (frm.ratings.value == 0)
		return;
	if (! confirm("¿Seguro que desea votar '" + frm.ratings.options[frm.ratings.selectedIndex].text +"'?") ) {
		frm.ratings.selectedIndex=0;
		return false;
	}
	var content = "id=" + id + "&user=" + user + "&md5=" + md5 + '&value=' +frm.ratings.value;
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
	var url = base_url + 'backend/'+program+'?id='+id+'&p='+page+'&type='+type;
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

	$.modal('<div class="header" id="modalHeader"><div id="modalTitle">'+title+'</div></div><div class="content" id="modalContent">Loading...</div>',
			{});
	$.get(url, function(data){
	// create a modal dialog with the data
		$('#modalContent').html(data);
	});
	reportAjaxStats('modal', 'view');
}

// See http://www.shiningstar.net/articles/articles/javascript/dynamictextareacounter.asp?ID=AW
function textCounter(field,cntfield,maxlimit) {
	if (field.value.length > maxlimit)
	// if too long...trim it!
		field.value = field.value.substring(0, maxlimit);
	// otherwise, update 'characters left' counter
	else
		cntfield.value = maxlimit - field.value.length;
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
	tooltip.tooltipShadow=null;
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
* ex :  <div id="myHtmlElement" onmouseover="tooltip.show(this)"...></div>
*/

tooltip.show = function (event, text) {
      // we save text of title attribute to avoid the showing of tooltip generated by browser
	if (this.dom2  == false ) return false;
	if (this.tooltipShadow == null) {
		this.tooltipShadow = document.createElement("div");
		this.tooltipShadow.setAttribute("id", "tooltip-shadow");
		document.body.appendChild(tooltip.tooltipShadow);

		this.tooltipText = document.createElement("div");
		this.tooltipText.setAttribute("id", "tooltip-text");
		document.body.appendChild(this.tooltipText);
	}
	this.saveonmouseover=document.onmousemove;
	document.onmousemove = this.mouseMove;
	this.mouseMove(event); // This already moves the div to the right position
	this.setText(text);
	this.tooltipText.style.visibility ="visible";
	this.tooltipShadow.style.visibility ="visible";
	this.active = true;
	return false;
}


tooltip.setText = function (text) {
	tooltip.tooltipShadow.style.width = 0+"px";
	tooltip.tooltipShadow.style.height = 0+"px";
	this.tooltipText.innerHTML=text;
	setTimeout('tooltip.setShadow()', 1);
	return false;
}

tooltip.setShadow = function () {
	tooltip.tooltipShadow.style.width = tooltip.tooltipText.clientWidth+"px";
	tooltip.tooltipShadow.style.height = tooltip.tooltipText.clientHeight+"px";
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
	if (this.tooltipShadow != null ) {
		this.tooltipText.style.visibility = "hidden";
		this.tooltipShadow.style.visibility = "hidden";
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
	this.tooltipShadow.style.left = xLS +"px";
	this.tooltipShadow.style.top = yLS +"px";
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
		target_text = 'comment-' + element;
		target_author = 'cauthor-'+element;
		target = document.getElementById(target_text);
		author_target = document.getElementById(target_author);
		if (! target || ! author_target)  {
			this.ajax_delayed(event,'get_comment_tooltip.php',element+"&link="+link);
			return;
		}
		text = '<strong>'+author_target.innerHTML+'</strong><br/>'+target.innerHTML;
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
		this.show(event, 'cargando...'); // Translate this to your language: it's "loading..." ;-)
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

/************************
Simple format functions
**********************************/
/*
  Code from http://www.gamedev.net/community/forums/topic.asp?topic_id=400585
  strongly improved by Juan Pedro López for http://meneame.net
  2006/10/01, jotape @ http://jplopez.net
*/

function applyTag(id, tag) {
	obj = document.getElementById(id);
	if (obj) wrapText(obj, tag, tag);
}

function wrapText(obj, tag) {
	if(typeof obj.selectionStart == 'number') {
		// Mozilla, Opera and any other true browser
		var start = obj.selectionStart;
		var end   = obj.selectionEnd;

		if (start == end || end < start) return false;
		obj.value = obj.value.substring(0, start) +  replaceText(obj.value.substring(start, end), tag) + obj.value.substring(end, obj.value.length);
	} else if(document.selection) {
		// Damn Explorer
		// Checking we are processing textarea value
		obj.focus();
		var range = document.selection.createRange();
		if(range.parentElement() != obj) return false;
		if (range.text == "") return false;
		if(typeof range.text == 'string')
	        document.selection.createRange().text =  replaceText(range.text, tag);
	} else
		obj.value += text;
}

function replaceText(text, tag) {
		text = text.replace(/(^|\s)[\*_]([^\s]+)[\*_]/gm, '$1$2')
		text = text.replace(/([^\s]+)/gm, tag+"$1"+tag)
		return text;
}


// This function report the ajax request to stats events if enabled in your account
// http://code.google.com/intl/es/apis/analytics/docs/eventTrackerOverview.html
function reportAjaxStats(category, action) {
	if (pageTracker._trackEvent) {
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
