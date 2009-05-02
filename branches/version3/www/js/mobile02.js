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
	disable_vote_link(id, "...", '#FFC8AF');
	getJSON(url,  
		 function(data) {
				parseLinkAnswer(id, data);
		}
	);
}

function disable_vote_link(id, mess, background) {
	var ob = document.getElementById('a-va-' + id);
	try {
		ob.innerHTML = '<span>'+mess+'</span>';
		ob.style.setProperty('background-color', background);
	} catch (e) {}
}

function parseLinkAnswer (id, link)
{
	if (link.error || id != link.id) {
		disable_vote_link(id, "grr...", '#FFCBAA');
		alert("Error: "+link.error);
		return false;
	}
	votes = parseInt(link.votes)+parseInt(link.anonymous);
	var ob = document.getElementById('a-votes-' + link.id);
	try { ob.innerHTML = votes+"" }
	catch (e) {}
	if (link.value > 0) {
		disable_vote_link(link.id, "¡chachi!", '#FFFFFF');
	} else if (link.value < 0) {
		disable_vote_link(link.id, ":-(", '#FFFFFF');
	}
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

