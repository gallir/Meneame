/*******************
VOTES FUNCTIONS
BASIC FUNCTIONS
************************************/
var update_voters = false;

function menealo(user, id, htmlid, md5)
{
	var url = base_url + "backend/menealo.php";
	var content = "id=" + id + "&user=" + user + "&md5=" + md5;
	url = url + "?" + content;
	disable_vote_link(id, "...", '#FFC8AF');
	$.get(url, {},  
		 function(html) {
			if (/^ERROR:/.test(html)) {
				html = html.substring(6, html.length);
				parseAnswer(htmlid, true, html);
				updateVoters(id);
			} else {
				// Just a warning, do nothing
				if (/^WARN:/.test(html)) {
					alert(html);
				} else {
					parseAnswer (htmlid, false, html);
					updateVoters(id);
				}
			}
		}
	);
}

function menealo_comment(user, id, value)
{
	var url = base_url + "backend/menealo_comment.php";
	var content = "id=" + id + "&user=" + user + "&value=" + value;
	var myid = 'comment-'+id;
	url = url + "?" + content;
	$.get(url, {}, 
		 function(html) {
			if (/^ERROR:/.test(html) || /^WARN:/.test(html)) {
				html = html.substring(6, html.length);
				alert(html);
			} else {
				vote_karma_image = html.split(",");
				votes = parseInt(vote_karma_image[0]);
				karma = parseInt(vote_karma_image[1]);
				image = vote_karma_image[2];
				$('#vc-'+id).html(votes);
				$('#vk-'+id).html(karma+" ");
				if (image.length > 0) {
					$('#c-votes-'+id).html('<img src="'+image+'"/>');
				}
			}
		}
	);
}

function menealo_post(user, id, value)
{
	var url = base_url + "backend/menealo_post.php";
	var content = "id=" + id + "&user=" + user + "&value=" + value;
	var myid = 'comment-'+id;
	url = url + "?" + content;
	$.get(url, {}, 
		 function(html) {
			if (/^ERROR:/.test(html) || /^WARN:/.test(html)) {
				html = html.substring(6, html.length);
				alert(html);
			} else {
				vote_karma_image = html.split(",");
				votes = parseInt(vote_karma_image[0]);
				karma = parseInt(vote_karma_image[1]);
				image = vote_karma_image[2];
				$('#vc-'+id).html(votes);
				$('#vk-'+id).html(karma+" ");
				if (image.length > 0) {
					$('#c-votes-'+id).html('<img src="'+image+'"/>');
				}
			}
		}
	);
}


function disable_problem_form(id) {
	$('#problem-' + id).hide();
}

function disable_vote_link(id, mess, background) {
	//$('#a-va-' + id).hide();
	$('#a-va-' + id).html('<span>'+mess+'</span>');
	$('#a-va-' + id).css('background', background);
	//$('#a-va-' + id).show('fast');
}

function parseAnswer (id, error, server_answer)
{
	answer = server_answer.split("~");
	linkid = answer[0];
	if (error || answer.length  != 5  || id != linkid) {
		alert(server_answer);
		disable_vote_link(id, "grr...", '#FFCBAA');
		disable_problem_form(id);
		return false;
	}
	votes = answer[1];
	negatives = answer[2];
	karma = answer[3];
	value = answer[4];
	updateLinkValues(id, votes, negatives, karma, value);
	return false;
}

function updateLinkValues (id, votes, negatives, karma, value) {
	if ($('#a-votes-' + id).html() != votes) {
		$('#a-votes-' + id).hide();
		$('#a-votes-' + id).html(votes);
		$('#a-votes-' + id).fadeIn('slow');
	}
	$('#a-neg-' + id).html(negatives);
	$('#a-karma-' + id).html(karma+" ");
	if (value > 0) {
		disable_vote_link(id, "¡chachi!", '#FFFFFF');
		disable_problem_form(id);
	} else if (value < 0) {
		disable_vote_link(id, ":-(", '#FFFFFF');
		disable_problem_form(id);
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
	$.get(url, {}, 
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
	if (! confirm("¿Seguro que desea reportarlo?") ) {
		frm.ratings.selectedIndex=0;
		return false;
	}
	var content = "id=" + id + "&user=" + user + "&md5=" + md5 + '&value=' +frm.ratings.value;
	var url=base_url + "backend/problem.php?" + content;
	$.get(url, {}, 
		 function(html) {
			if (/^ERROR:/.test(html)) {
				html = html.substring(6, html.length);
				parseAnswer(id, true, html);
			} else {
				parseAnswer(id, false, html);
				updateVoters(id);
			}
		}
	);
	return false;
}

function updateVoters(id) {
	if (update_voters) {
		get_votes('meneos.php', 'voters', 'voters-container',1, id);
	}
}

// Get voters by Beldar <beldar.cat at gmail dot com>
// Generalized for other uses (gallir at gmail dot com)
function get_votes(program,type,container,page,id) {
	var url = base_url + 'backend/'+program+'?id='+id+'&p='+page+'&type='+type;
	$('#'+container).load(url);
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
	if (this.tooltipText.clientWidth > 0  && document.documentElement.clientWidth > 0 && xL > document.documentElement.clientWidth * 0.6) {
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
	if (type == 'id') {
		target_text = 'comment-' + element;
		target_author = 'cauthor-'+element;
		target = document.getElementById(target_text);
		author_target = document.getElementById(target_author);
		if (! target || ! author_target) return false;
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


// This function report the ajax request to stats trackers
// Only known how to do it with urchin/Google Analytics
// See http://www.google.com/support/analytics/bin/answer.py?answer=33985&topic=7292
function reportAjaxStats(page) {
	return; // Slow down
	if (window.urchinTracker) {
		urchinTracker(page+'.ajax'); 
	}
}
