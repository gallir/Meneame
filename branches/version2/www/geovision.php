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
geo_init('onLoad', false, 3);

do_header(_('geo visión'));
do_banner_top();
echo '<div id="container">'."\n";
do_sidebar();
echo '<div id="contents">';
do_tabs('main', _('geo visión'), true);
echo '<div class="topheading"><h2>actividad de los usuarios geolocalizados</h2></div>';

echo '<div id="map" style="width: 100%; height: 500px;margin:0 0 0 20px"></div></div>'
?>

<script type="text/javascript">
var baseicon = new GIcon();
baseicon.iconSize = new GSize(20, 25);
baseicon.iconAnchor = new GPoint(10, 25);
baseicon.infoWindowAnchor = new GPoint(10, 12);

var timestamp = 0;
var period = 10000;
var persistency = 300000;

function add_marker(item, delay) {
	var myicon;
	var point = new GLatLng(item.lat, item.lng);
	switch (item.type) {
		case 'comment':
			myicon = "img/geo/common/geo-comment01.png";
			break;
		case 'post':
			myicon = "img/geo/common/geo-newnotame01.png";
			break;
		case 'link':
			if (item.evt == 'geo_edit') myicon = "img/geo/common/geo-geo01.png";
			else if (item.status == 'queued') myicon = "img/geo/common/geo-new01.png";
			else myicon = "img/geo/common/geo-published01.png";
		break;
	}
	var icon = new GIcon(baseicon);
	icon.image = myicon;
	var marker = new GMarker(point, icon);
	marker.myId = item.id;
	marker.myType = item.type;
	setTimeout(function () {
				geo_map.addOverlay(marker);
				GDownloadUrl(base_url+"geo/"+marker.myType+".php?id="+marker.myId, function(data, responseCode) {
					marker.openInfoWindowHtml(data);
				});
			}, delay);
	setTimeout(function () {geo_map.removeOverlay(marker)}, persistency);
}

function get_json() {
	$.getJSON('geo/sneaker.php', {"time": timestamp}, function (json) {
			var items = json.items.length;
			timestamp = json.ts;
			var delay_time;
			var item;
			for (i=items-1; i>=0; i--) {
				delay_time = parseInt(period - (period/items) * (i+1));
				add_marker(json.items[i], delay_time);
			}
		});
	setTimeout(get_json, period);
}

function onLoad() {
	if (geo_basic_load(18, 15, 3)) {
		geo_map.addControl(new GLargeMapControl());
		geo_map.addControl(new GMapTypeControl());
		GEvent.addListener(geo_map, 'click', function (overlay, point) {
			if (overlay && overlay.myId > 0) {
				GDownloadUrl(base_url+"geo/"+overlay.myType+".php?id="+overlay.myId, function(data, responseCode) {
					overlay.openInfoWindowHtml(data);
				});
			} 
		});
		get_json();
	}
}

</script>
<?

do_footer();
?>
