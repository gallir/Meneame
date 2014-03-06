function comment_reply(id) {
	ref = '#' + id + ' ';
	textarea = $('#comment');
	if (textarea.length == 0 ) return;
	var re = new RegExp(ref);
	var oldtext = textarea.val();
	if (oldtext.match(re)) return;
	if (oldtext.length > 0 && oldtext.charAt(oldtext.length-1) != "\n") oldtext = oldtext + "\n";
	textarea.val(oldtext + ref);
	textarea.get(0).focus();
}

function post_load_form(id, container) {
	var url = base_url + 'backend/post_edit.php?id='+id+"&key="+base_key;
	$.get(url, function (html) {
			if (html.length > 0) {
				if (html.match(/^ERROR:/i)) {
					mDialog.notify(html, 2);
				} else {
					$('#'+container).html(html);
				}
				reportAjaxStats('html', 'post_edit');
			}
		});
}


function post_new() {
	post_load_form(0, 'addpost');
}

function post_edit(id) {
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
	textarea.get(0).focus();
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

function check_file_size(id, size) {
	var input = document.getElementById(id);
	if (input.files != undefined) {
		for (var i = 0; i < input.files.length; i++) {
			if (input.files[i].fileSize > size) {
				mDialog.notify('<i>'+input.files[i].fileName + "<\/i>: {% trans _('tamaño máximo excedido') %}" + " " + input.files[i].fileSize + " > " + size, 5);
				return;
			}
		}
		mDialog.notify("{% trans _('tamaño OK') %}", 1);
	}
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
	return false;
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
		return '<'+tag+'>'+text+'</'+tag+'>';
}

