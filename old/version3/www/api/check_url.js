// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


// NEW SCRIPT: INSTEAD OF THIS SCRIPT USE checkurl.js.php !

var url = 'http://meneame.net/api/check_url.php?url='+encodeURIComponent(document.URL);

function write_iframe() {
	var span = document.getElementById("meneame");
	span.innerHTML='<iframe width="98" height="17" scrolling="no" frameborder="0" marginwidth="0" marginheight="0" vspace="0" hspace="0" allowtransparency="true" src="'+url+'"></iframe>';
}

document.write('<span id="meneame" style="width: 98px; height: 17px; border: none; padding: 0; margin: 0; background: transparent ; "><script type="text/javascript">setTimeout("write_iframe()", 200)</script></span>');
