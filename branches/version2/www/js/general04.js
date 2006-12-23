/**********
Basic AJAX
**************/
function myXMLHttpRequest ()
{
	var xmlhttplocal = false;
	if (typeof XMLHttpRequest != 'undefined') {
		try {
			xmlhttplocal = new XMLHttpRequest ();
		}
		catch (e) {
  			xmlhttplocal = false;
		}
	}
	if (!xmlhttplocal) {
		try {
			xmlhttplocal = new ActiveXObject ("Msxml2.XMLHTTP")
		}
		catch (e) {
			try {
				xmlhttplocal = new ActiveXObject ("Microsoft.XMLHTTP")
			}
			catch (E) {
				xmlhttplocal = false;
				alert ('couldn\'t create xmlhttp object');
			}
  		}
	}
	return (xmlhttplocal);
}

/*******************
VOTES FUNCTIONS
BASIC FUNCTIONS
************************************/
var mnmxmlhttp = Array ();
var mnmString = Array ();
var update_voters = false;

function menealo (user, id, htmlid, md5)
{
	url = base_url + "backend/menealo.php";
	content = "id=" + id + "&user=" + user + "&md5=" + md5;
	mnmxmlhttp[htmlid] = new myXMLHttpRequest ();
	if (mnmxmlhttp[htmlid]) {
		/*
			mnmxmlhttp[htmlid].open ("POST", url, true);
			mnmxmlhttp[htmlid].setRequestHeader ('Content-Type',
					   'application/x-www-form-urlencoded');
			mnmxmlhttp[htmlid].send (content);
		*/
		url = url + "?" + content;
		mnmxmlhttp[htmlid].open ("GET", url, true);
		mnmxmlhttp[htmlid].send (null);


		warnmatch = new RegExp ("^WARN:");
		errormatch = new RegExp ("^ERROR:");
		target = document.getElementById ('a-va-' + htmlid);
		/* Too away the text also because it gives a weird effect */
		disable_vote_link(id, "...", '#FFC8AF');
		mnmxmlhttp[htmlid].onreadystatechange = function () {
			if (mnmxmlhttp[htmlid].readyState == 4) {
				mnmString[htmlid] = mnmxmlhttp[htmlid].responseText;
				if (mnmString[htmlid].match (errormatch)) {
					mnmString[htmlid] = mnmString[htmlid].substring (6, mnmString[htmlid].length);
					parseAnswer (htmlid, true, mnmString[htmlid]);
					updateVoters(id);
				} else {
					// Just a warning, do nothing
					if (mnmString[htmlid].match (warnmatch)) {
						alert(mnmString[htmlid]);
					} else {
						parseAnswer (htmlid, false, mnmString[htmlid]);
						updateVoters(id);
					}
				}
				reportAjaxStats('/vote');
			}
		}
	} else {
		alert('Couldn\'t create XmlHttpRequest');
	}
}

function menealo_comment (user, id, value)
{
	url = base_url + "backend/menealo_comment.php";
	content = "id=" + id + "&user=" + user + "&value=" + value;
	myid = 'comment-'+id;
	mnmxmlhttp[myid] = new myXMLHttpRequest ();
	if (mnmxmlhttp[myid]) {
		url = url + "?" + content;
		mnmxmlhttp[myid].open ("GET", url, true);
		mnmxmlhttp[myid].send (null);
		warnmatch = new RegExp ("^WARN:");
		errormatch = new RegExp ("^ERROR:");
		mnmxmlhttp[myid].onreadystatechange = function () {
			if (mnmxmlhttp[myid].readyState == 4) {
				mnmString[myid] = mnmxmlhttp[myid].responseText;
				if (mnmString[myid].match (errormatch) || mnmString[myid].match (warnmatch)) {
					mnmString[myid] = mnmString[myid].substring (6, mnmString[myid].length);
					alert (mnmString[myid]);
				} else {
					vote_karma_image = mnmString[myid].split(",");
					votes = parseInt(vote_karma_image[0]);
					karma = parseInt(vote_karma_image[1]);
					image = vote_karma_image[2];
					target1 = document.getElementById ('vc-'+id);
					if(target1) target1.innerHTML = votes;
					target1 = document.getElementById ('vk-'+id);
					if(target1) target1.innerHTML = karma;
					if (image.length > 0) {
						target1 = document.getElementById ('c-votes-' + id);
						if (target1) target1.innerHTML = '<img src="'+image+'"/>';
					}
				}
				reportAjaxStats('/comment_vote');
			}
		}
	} else {
		alert('Couldn\'t create XmlHttpRequest');
	}
}


function disable_problem_form(id) {
	target = document.getElementById ('problem-' + id);
	if (target) {
		target.ratings.disabled=true;
		target.innerHTML = "";
	}
}

function disable_vote_link(id, mess, background) {
	target = document.getElementById ('a-va-' + id);
	if (target) {
		target.innerHTML = '<span>'+mess+'</span>';
		target.style.background = background;
	}
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
	target = document.getElementById ('a-votes-' + id);
	if (target) target.innerHTML = votes;
	target = document.getElementById ('a-neg-' + id);
	if (target) target.innerHTML = negatives;
	target = document.getElementById ('a-karma-' + id);
	if (target) target.innerHTML = karma;
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
	url = base_url + 'backend/checkfield.php?type='+type+'&name=' + field.value;
	checkitxmlhttp = new myXMLHttpRequest ();
	checkitxmlhttp.open ("GET", url, true);
	checkitxmlhttp.onreadystatechange = function () {
		if (checkitxmlhttp.readyState == 4) {
			responsestring = checkitxmlhttp.responseText;
			if (responsestring == 'OK') {
				document.getElementById (type+'checkitvalue').innerHTML = '<span style="color:black">"' + field.value + 
						'": ' + responsestring + '</span>';
				form.submit.disabled = '';
			} else {
				document.getElementById (type+'checkitvalue').innerHTML = '<span style="color:red">"' + field.value + '": ' +
				responsestring + '</span>';
				form.submit.disabled = 'disabled';
			}
			reportAjaxStats('/check_field');
		}
	}
  //  xmlhttp.setRequestHeader('Accept','message/x-formresult');
  checkitxmlhttp.send (null);
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
	content = "id=" + id + "&user=" + user + "&md5=" + md5 + '&value=' +frm.ratings.value;
	url=base_url + "backend/problem.php?" + content;
	mnmxmlhttp[id] = new myXMLHttpRequest ();
	mnmxmlhttp[id].open("GET",url,true);
	mnmxmlhttp[id].onreadystatechange=function() {
		if (mnmxmlhttp[id].readyState==4) {
			errormatch = new RegExp ("^ERROR:");
			response = mnmxmlhttp[id].responseText;
			if (response.match(errormatch)) {
				response = response.substring (6, response.length);
				parseAnswer(id, true, response);
			} else {
				parseAnswer(id, false, response);
				updateVoters(id);
			}
			reportAjaxStats('/problem');
		}
  	}
	mnmxmlhttp[id].send(null);
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
	var myxmlhttp = new myXMLHttpRequest ();
	myxmlhttp.open('get', url, true);
	myxmlhttp.onreadystatechange = function () {
		if(myxmlhttp.readyState == 4){
			response = myxmlhttp.responseText;
			if (response.length > 1) {
				document.getElementById(container).innerHTML = response;
			}
			reportAjaxStats('/get_html');
		}
	}
	myxmlhttp.send(null);
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
 *
 * Can show a tooltip over an element
 * Content of tooltip is the title attribute value of the element
 * copyright 2004 Laurent Jouanneau. http://ljouanneau.com/soft/javascript
 * release under LGPL Licence
 * works with dom2 compliance browser, and IE6. perhaps IE5 or IE4.. not Nestcape 4
 *
 * To use it :
 * 1.include this script on your page
 * 2.insert this element somewhere in your page
 *       <div id="tooltip"></div>
 * 3. style it in your CSS stylesheet (set color, background etc..). You must set
 * this two style too :
 *     div#tooltip { position:absolute; visibility:hidden; ... }
 * 4.the end. test it ! :-)
 *
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
tooltip.elementInitialWidth = 0;

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
	this.elementInitialWidth = this.tooltipText.scrollWidth;
	this.mouseMove(event); // This already moves the div to the right position
	//this.moveTo(this.x + this.offsetx , this.y + this.offsety);

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
	if (this.elementInitialWidth > 0  && document.documentElement.clientWidth > 0 && xL > document.documentElement.clientWidth * 0.6) {
		xL = xL  - this.elementInitialWidth;
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
		this.timeout = setTimeout("tooltip.ajax_request('"+script+"', '"+id+"', "+maxcache+")", 200);
	}
}

tooltip.ajax_request = function(script, id, maxcache) {
	tooltip.timeout = null;
	var myxmlhttp = new myXMLHttpRequest ();
	var url = base_url + 'backend/'+script+'?id='+id;
	myxmlhttp.open('get', url, true);
	myxmlhttp.onreadystatechange = function () {
		if(myxmlhttp.readyState == 4){
			response = myxmlhttp.responseText;
			if (response.length > 1) {
				tooltip.cache.set(script+id, response, {'ttl':maxcache});
				//tooltip.cache.set(script+id, response, {'ttl':'60000'});
				tooltip.setText(response);
			}
			reportAjaxStats('/tooltip');
		}
	}
	myxmlhttp.send(null);
}

/************************
Simple format functions
**********************************/
/*
  Code from http://www.gamedev.net/community/forums/topic.asp?topic_id=400585
  strongly improved by Juan Pedro López for http://meneame.net
  2006/10/01, jotape @ http://jplopez.net
*/

function applyTag(id, tag)
{
	obj = document.getElementById(id);
	if (obj) wrapText(obj, tag, tag);
};

function wrapText(obj, beginTag, endTag)
{
	if(typeof obj.selectionStart == 'number')
	{
		// Mozilla, Opera and any other true browser
		var start = obj.selectionStart;
		var end   = obj.selectionEnd;

		if (start == end || end < start) return false;

		while (obj.value.charAt(start) == ' ') start++;
		while (obj.value.charAt(end-1) == ' ') end--;

		if (start == end || end < start) return false;

		obj.value = obj.value.substring(0, start) + beginTag + obj.value.substring(start, end).replace(/\s+/gm, beginTag+" "+endTag) + endTag + obj.value.substring(end, obj.value.length);
	}
	else if(document.selection)
	{
		// Damn Explorer
		// Checking we are processing textarea value
		obj.focus();
		var range = document.selection.createRange();
		if(range.parentElement() != obj) return false;

		if (range.text == "") return false;

		if(typeof range.text == 'string')
	        document.selection.createRange().text = beginTag + range.text.replace(/\s+/gm, beginTag+" "+endTag) + endTag;
	}
	else
		obj.value += text;
};

// This function report the ajax request to stats trackers
// Only known how to do it with urchin/Google Analytics
// See http://www.google.com/support/analytics/bin/answer.py?answer=33985&topic=7292
function reportAjaxStats(page) {
	if (window.urchinTracker) {
		urchinTracker(page); 
	}
}
