<?php
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'geo.php');

geo_init('onLoad', false, 2);
array_push($globals['extra_js'], 'markermanager.js');

do_header(_('mapa de las últimas noticias') . ' | ' . _('menéame'));
do_tabs('main', 'map');

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_vertical_tags();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

echo '<div class="topheading"><h2>'._('noticias de las últimas 24 horas').'</h2></div>';

echo '<div style="margin:0 0 10px 20px; text-align:center">';

echo '<form action="" id="map-control" name="map-control">';

echo '<label>'._('publicadas').'&nbsp;<img src="'.$globals['base_static'].'img/geo/common/geo-published01.png" width="20" height="25" alt="'._('publicadas').'" title="'._('publicadas').'"/><input type="checkbox" checked="checked"  id="published" onclick="toggle(\'published\')" /></label>';
echo '&nbsp;&nbsp;&nbsp;';
echo '<label>'._('pendientes').'&nbsp;<img src="'.$globals['base_static'].'img/geo/common/geo-new01.png" width="20" height="25" alt="'._('pendientes').'" title="'._('pendientes').'"/><input type="checkbox" checked="checked"  id="queued" onclick="toggle(\'queued\')" /></label>';
echo '&nbsp;&nbsp;&nbsp;';
echo '<label>'._('autores').'&nbsp;<img src="'.$globals['base_static'].'img/geo/common/geo-user01.png" width="20" height="25" alt="'._('autores').'" title="'._('autores').'"/><input type="checkbox"  id="author" onclick="toggle(\'author\')" /></label>';


echo '</form>';
echo '</div>';


echo '<div id="map" style="width: 95%; height: 500px;margin:0 0 0 20px"></div></div>'
?>

<script type="text/javascript">
//<![CDATA[
var baseicon;
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
		geo_load_xml('link', 'published', 0);
	}
	if (queued) {
		geo_load_xml('link', 'queued', 0);
	}
	if (author) {
		geo_load_xml('author', '', 0);
	}
}

function onLoad(foo_lat, foo_lng, foo_zoom, foo_icontype) {
	baseicon = new GIcon();
	baseicon.iconSize = new GSize(20, 25);
	baseicon.iconAnchor = new GPoint(10, 25);
	baseicon.infoWindowAnchor = new GPoint(5, 1);
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
			}
		});
	}
}
//]]>
</script>
<?php

do_footer_menu();
do_footer();
