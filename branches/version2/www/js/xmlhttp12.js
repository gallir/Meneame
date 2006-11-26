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

var mnmxmlhttp = Array ();
var mnmString = Array ();
var mnmPrevColor = Array ();
var responsestring = Array ();
var myxmlhttp = Array ();
var responseString = new String;
var xmlhttp = new myXMLHttpRequest ();
var update_voters = false;


function menealo (user, id, htmlid, md5)
{
  	if (xmlhttp) {
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
			mnmPrevColor[htmlid] = target.style.backgroundColor;
			target.style.backgroundColor = '#FFBE94';
			mnmxmlhttp[htmlid].onreadystatechange = function () {
				if (mnmxmlhttp[htmlid].readyState == 4) {
					mnmString[htmlid] = mnmxmlhttp[htmlid].responseText;
					if (mnmString[htmlid].match (errormatch)) {
						mnmString[htmlid] = mnmString[htmlid].substring (6, mnmString[htmlid].length);
						// myclearTimeout(row);
						// resetrowfull(row);
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
				}
			}
		} else {
			alert('Couldn\'t create XmlHttpRequest');
		}
	}
}

function menealo_comment (user, id, value)
{
  	if (xmlhttp) {
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
				}
			}
		} else {
			alert('Couldn\'t create XmlHttpRequest');
		}
	}
}


function disable_problem_form(id) {
	target = document.getElementById ('problem-' + id);
	if (target) {
		target.ratings.disabled=true;
		target.innerHTML = "";
	}
}

function disable_vote_link(id, mess) {
	target = document.getElementById ('a-va-' + id);
	if (target) {
		target.style.backgroundColor = mnmPrevColor[id];
		target.innerHTML = "<span>"+mess+"</span>";
	}
}

function parseAnswer (id, error, server_answer)
{
	answer = server_answer.split("~");
	linkid = answer[0];
	if (error || answer.length  != 5  || id != linkid) {
		alert(server_answer);
		disable_vote_link(id, "grr...");
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
		disable_vote_link(id, "¡chachi!");
		disable_problem_form(id);
	} else if (value < 0) {
		disable_vote_link(id, ":-(");
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
	xmlhttp.open("GET",url,true);
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4) {
			errormatch = new RegExp ("^ERROR:");
			response = xmlhttp.responseText;
			if (response.match(errormatch)) {
				response = response.substring (6, response.length);
				parseAnswer(id, true, response);
			} else {
				parseAnswer(id, false, response);
				updateVoters(id);
			}
		}
  	}
	xmlhttp.send(null);
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
	xmlhttp.open('get', url, true);
	xmlhttp.onreadystatechange = function () {
		if(xmlhttp.readyState == 4){
			response = xmlhttp.responseText;
			if (response.length > 1) {
				document.getElementById(container).innerHTML = response;
			}
		}
	}
	xmlhttp.send(null);
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
