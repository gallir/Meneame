<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
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
geo_init('onLoad');

do_header(_('mapa de publicadas'));
do_banner_top();
echo '<div id="container">'."\n";
do_sidebar();
echo '<div id="contents">';
do_tabs('main', _('geo'), true);
echo '<div id="map" style="width: 720px; height: 600px;margin:20px 0 0 20px"></div></div>';
?>
<script type="text/javascript">
	function onLoad() {
		if (geo_basic_load(false, false, 2)) {
			geo_map.addControl(new GLargeMapControl());
			geo_load_xml('link')
			/*
			var geoXml = new GGeoXml("http://<? echo get_server_name() . $globals['base_url'] ?>rss2.php?rows=100");
			geo_map.addOverlay(geoXml);
			*/
		}
	}

var baseicon = new GIcon();
baseicon.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
baseicon.iconSize = new GSize(12, 20);
baseicon.shadowSize = new GSize(22, 20);
baseicon.iconAnchor = new GPoint(6, 20);
baseicon.infoWindowAnchor = new GPoint(5, 1);

var iconred = "http://labs.google.com/ridefinder/images/mm_20_red.png"
var iconwhite = "http://labs.google.com/ridefinder/images/mm_20_white.png"
var iconblue = "http://labs.google.com/ridefinder/images/mm_20_blue.png"
var iconorange = "http://labs.google.com/ridefinder/images/mm_20_orange.png"
var iconpurple = "http://labs.google.com/ridefinder/images/mm_20_purple.png"
var iconyellow = "http://labs.google.com/ridefinder/images/mm_20_yellow.png"

//icon.image = "http://labs.google.com/ridefinder/images/mm_20_red.png";

function geo_load_xml(type) {
	GDownloadUrl(base_url+"geo/xml.php?type="+type, function(data, responseCode) {
		var xml = GXml.parse(data);
		var markers = xml.documentElement.getElementsByTagName("marker");
		for (var i = 0; i < markers.length; i++) {
			var point = new GLatLng(parseFloat(markers[i].getAttribute("lat")),
				parseFloat(markers[i].getAttribute("lng")));
			var status = markers[i].getAttribute("status");
			var icon = new GIcon(baseicon);
			if (status == 'published') icon.image = iconred;
			else if (status == 'queued') icon.image = iconyellow;
			else if (status == 'discard') icon.image = iconpurple;
			geo_map.addOverlay(new GMarker(point, icon));
		}
	});
}
</script>
<?

echo '</div>';
echo '</div>';
do_footer();
?>
