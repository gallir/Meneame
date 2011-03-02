{% spacefull %}
var base_url="{{ globals.base_url }}";
var base_static="{{ globals.base_static }}";
var mobile_version = true;

function getAJAX(url, fn) {
	var ajax;
	try {
		ajax = new XMLHttpRequest ();
		ajax.open ("GET", url, true);
		ajax.send (null);
		ajax.onreadystatechange = function () {
			if (ajax.readyState == 4) {
				fn(ajax.responseText);
			}
		}
	} catch (e) {}
}

function getJSON(url, fn) {
	getAJAX(url, function (data) {
			 fn(eval('('+data+')'));
		});
}

function menealo(user, id)
{
	var url = base_url + "backend/menealo.php";
	var content = "id=" + id + "&user=" + user + "&key=" + base_key + "&u=" + document.referrer;
	url = url + "?" + content;
	getJSON(url,
		 function(data) {
				parseLinkAnswer(id, data);
		}
	);
}

function disable_vote_link(id, value, mess, background) {
	if (value < 0) span = '<span class="negative">';
	else span = '<span>';

	var ob = document.getElementById('a-va-' + id);
	try {
		ob.innerHTML = span+mess+'</span>';
		if (background.length > 0) ob.style.setProperty('background-color', background);
	} catch (e) {}
}

function parseLinkAnswer (id, link)
{
	if (link.error || id != link.id) {
		disable_vote_link(id, "{% trans _('grr...') %}", '');
		alert("{% trans _('Error:') %} "+link.error);
		return false;
	}
	votes = parseInt(link.votes)+parseInt(link.anonymous);
	var ob = document.getElementById('a-votes-' + link.id);
	try { ob.innerHTML = votes+"" }
	catch (e) {}
	disable_vote_link(link.id, link.value, link.vote_description, '');
	return false;
}

function load_html(program,type,container,page,id) {
	var url = base_url + 'backend/'+program+'?id='+id+'&p='+page+'&type='+type;
	getAJAX(url, function (data) {
			var ob = document.getElementById(container);
			try { ob.innerHTML = data }
			catch (e) {}
		});
}

{% endspacefull %}
