<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'geo.php');

$globals['ads'] = true;
geo_init('onLoad', false, 2);
array_push($globals['extra_js'], 'markermanager.js');

do_header(_('mapa de publicadas'));
do_banner_top();
echo '<div id="container">'."\n";
do_sidebar();
echo '<div id="contents">';
do_tabs('main', 'map');
echo '<div class="topheading"><h2>noticias de las últimas 24 horas</h2></div>';

echo '<div style="margin:0 0 10px 20px; text-align:center">';

echo '<form action="" id="map-control" name="map-control">';

echo _('publicadas').'&nbsp;<img src="http://labs.google.com/ridefinder/images/mm_20_red.png" width="12" height="20" alt="'._('publicadas').'" title="'._('publicadas').'"/><input type="checkbox" checked="checked"  id="published" onclick="toggle(\'published\')" />';
echo '&nbsp;&nbsp;&nbsp;';
echo _('pendientes').'&nbsp;<img src="http://labs.google.com/ridefinder/images/mm_20_yellow.png" width="12" height="20" alt="'._('pendientes').'" title="'._('pendientes').'"/><input type="checkbox" checked="checked"  id="queued" onclick="toggle(\'queued\')" />';
echo '&nbsp;&nbsp;&nbsp;';
echo _('autores').'&nbsp;<img src="http://labs.google.com/ridefinder/images/mm_20_blue.png" width="12" height="20" alt="'._('autores').'" title="'._('authors').'"/><input type="checkbox"  id="author" onclick="toggle(\'author\')" />';


echo '</form>';
echo '</div>';


echo '<div id="map" style="width: 100%; height: 500px;margin:0 0 0 20px"></div></div>'
?>

<script type="text/javascript">
var iconred = "http://labs.google.com/ridefinder/images/mm_20_red.png"
var iconwhite = "http://labs.google.com/ridefinder/images/mm_20_white.png"
var iconblue = "http://labs.google.com/ridefinder/images/mm_20_blue.png"
var iconorange = "http://labs.google.com/ridefinder/images/mm_20_orange.png"
var iconpurple = "http://labs.google.com/ridefinder/images/mm_20_purple.png"
var iconyellow = "http://labs.google.com/ridefinder/images/mm_20_yellow.png"
var baseicon = new GIcon();
//baseicon.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
baseicon.iconSize = new GSize(12, 20);
//baseicon.shadowSize = new GSize(22, 20);
baseicon.iconAnchor = new GPoint(6, 20);
baseicon.infoWindowAnchor = new GPoint(5, 1);
var geo_marker_mgr = null;


var published = true;
var queued = true;
var author = false;


function toggle(what, field) {
	eval(what +' = ! '+what);
	load_xmls();
	return false;
}

function load_xmls() {
	if (geo_marker_mgr)
		geo_marker_mgr.clearMarkers();
	if (published) {
		geo_load_xml('link', 'published', 0, iconred);
	}
	if (queued) {
		geo_load_xml('link', 'queued', 0, iconorange);
	}
	if (author) {
		geo_load_xml('author', '', 0, iconblue);
	}
	//setTimeout("geo_load_xml('link', 'queued', 0, iconorange)", 300);
}

function onLoad() {
	if (geo_basic_load(18, 15, 2)) {
		geo_map.addControl(new GLargeMapControl());
		// From http://gmaps-utility-library.googlecode.com/svn/trunk/markermanager/
		geo_marker_mgr = new MarkerManager(geo_map, {trackMarkers:false});
		load_xmls();
		GEvent.addListener(geo_map, 'click', function (overlay, point) {
			if (overlay && overlay.myId > 0) {
				GDownloadUrl(base_url+"geo/"+overlay.myType+".php?id="+overlay.myId, function(data, responseCode) {
					overlay.openInfoWindowHtml(data);
				});
			} //else if (point) geo_map.panTo(point);
		});
	}
}
</script>
<?

do_footer();
?>
