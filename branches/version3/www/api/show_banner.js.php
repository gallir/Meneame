<?
header('Content-Type: text/javascript; charset=UTF-8');
header('Cache-Control: max-age=3600');
?>
// var mnm_date = new Date();
var mnm_banner_counter = 0;
var mnm_banner_url = 'api/show_banner.php?width='+mnm_banner_width+'&height='+mnm_banner_height+'&format='+mnm_banner_format+'&color_border='+mnm_banner_color_border+'&color_bg='+mnm_banner_color_bg+'&color_link='+mnm_banner_color_link+'&color_text='+mnm_banner_color_text+'&font_pt='+mnm_banner_font_pt;
//+'&time='+mnm_date.getTime();

function write_banner_frame() {
	mnm_banner_busy = true;
	var div = document.getElementById("mnm_banner");
	div.innerHTML='<iframe width="'+mnm_banner_width+'" height="'+mnm_banner_height+'" scrolling="no" frameborder="0" marginwidth="0" marginheight="0" vspace="0" hspace="0" allowtransparency="true" id="mnm_banner_ifr"></iframe>';
	setTimeout("mnm_banner_load()", 1);
}

function mnm_banner_load() {
	document.getElementById('mnm_banner_ifr').src=mnm_banner_url+'&c='+mnm_banner_counter;
	// Allow to reload the banner every two minutes or more
	if ("undefined" != typeof(mnm_banner_reload) &&  mnm_banner_reload > 60000) {
		setTimeout("mnm_banner_load()", mnm_banner_reload);
	}
	mnm_banner_counter++;
}

document.write('<div id="mnm_banner" style="width: 100%; height: 100%; overflow: hidden; border: none; padding: 0; margin: 0; background: transparent ; "><script type="text/javascript">setTimeout("write_banner_frame()", 10)</script></div>');

