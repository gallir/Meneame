<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'geo.php');

geo_init('onLoad', false, 3);

do_header(_('geovisión'));
do_tabs('main', _('geovisión'), true);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_vertical_tags();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

echo '<div class="topheading"><h2>actividad de los usuarios geolocalizados</h2></div>';

echo '<div id="map" style="width: 95%; height: 500px;margin:0 0 0 20px"></div></div>'
?>

<script type="text/javascript">
//<![CDATA[
var timestamp = 0;
var period = 10000;
var persistency = 300000;
var counter=0;

function add_marker(item, delay) {
	var myicon;
	var point = new GLatLng(item.lat, item.lng);
	var marker;
	switch (item.type) {
		case 'link':
			if (item.evt == 'geo_edit') marker = geo_get_marker(point, 'geo');
			else  marker = geo_get_marker(point, item.status);
			break;
		default:
			marker = geo_get_marker(point, item.type);
	}
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
				if (typeof (json.items[i]) != "undefined") { // IE return a undefined, sometimes :-O
					delay_time = parseInt(period - (period/items) * (i+1));
					add_marker(json.items[i], delay_time);
				}
			}
		});
	counter++;
	if (counter > 700) {
		if ( !confirm('<? echo _('¿desea continuar conectado?');?>') ) {
			return;
		}
		counter = 0;
	}
	setTimeout(get_json, period);
}

function onLoad(foo_lat, foo_lng, foo_zoom, foo_icontype) {
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
//]]>
</script>
<?

do_footer_menu();
do_footer();
?>
