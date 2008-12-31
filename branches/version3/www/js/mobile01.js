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
}

function disable_vote_link(id, mess, background) {
	$('#a-va-' + id).html('<span>'+mess+'</span>');
	$('#a-va-' + id).css('background', background);
}

function parseLinkAnswer (id, link)
{
	if (link.error || id != link.id) {
		disable_vote_link(id, "grr...", '#FFCBAA');
		alert("Error: "+link.error);
		return false;
	}
	votes = parseInt(link.votes)+parseInt(link.anonymous);
	$('#a-votes-' + link.id).html(votes+"");
	if (link.value > 0) {
		disable_vote_link(link.id, "¡chachi!", '#FFFFFF');
	} else if (link.value < 0) {
		disable_vote_link(link.id, ":-(", '#FFFFFF');
	}
	return false;
}

function get_votes(program,type,container,page,id) {
	var url = base_url + 'backend/'+program+'?id='+id+'&p='+page+'&type='+type;
	$('#'+container).load(url);
}

