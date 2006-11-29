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
