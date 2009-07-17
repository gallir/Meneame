
function post_load_form(id, container) {
	var url = base_url + 'backend/post_edit.php?id='+id+"&key="+base_key;
	$.get(url, function (html) {
			if (html.length > 0) {
				if (html.match(/^ERROR:/i)) {
					alert(html);
				} else {
					$('#'+container).html(html);
				}
				reportAjaxStats('html', 'post_edit');
			}
		});
}


function post_new() {
	//get_votes('post_edit.php','','addpost',0 , 0);
	post_load_form(0, 'addpost');
}

function post_edit(id) {
	//get_votes('post_edit.php', 'edit_post', 'pcontainer-'+id, 0, id);
	post_load_form(id, 'pcontainer-'+id);
}

function post_reply(id, user) {
	ref = '@' + user + ',' + id + ' ';
	textarea = $('#post');
	if (textarea.length == 0) {
		post_new();
	}
	post_add_form_text(ref, 1);
}

function post_add_form_text(text, tries) {
	if (! tries) tries = 1;
	textarea = $('#post');
	if (tries < 20 && textarea.length == 0) {
			tries++;
			setTimeout('post_add_form_text("'+text+'", '+tries+')', 50);
			return false;
	}
	if (textarea.length == 0 ) return false;
	var re = new RegExp(text);
	var oldtext = textarea.val();
	if (oldtext.match(re)) return false;
	if (oldtext.length > 0 && oldtext.charAt(oldtext.length-1) != ' ') oldtext = oldtext + ' ';
	textarea.val(oldtext + text);	
}

