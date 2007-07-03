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

do_header(_('mapa de publicadas'));
do_banner_top();
echo '<div id="container">'."\n";
do_sidebar();
echo '<div id="contents">';
do_tabs('main', 'map');
echo '<div id="map" style="width: 100%; height: 500px;margin:20px 0 0 20px"></div></div>'
?>
<script type="text/javascript">
	function onLoad() {
		if (geo_basic_load(18, 15, 2)) {
			geo_map.addControl(new GLargeMapControl());
			geo_marker_mgr = new GMarkerManager(geo_map);

			geo_load_xml('link', 'published', 0);
			geo_load_xml('link', 'queued', 2);
			GEvent.addListener(geo_map, 'click', function (overlay, point) {
				if (overlay && overlay.myId > 0) {
					GDownloadUrl(base_url+"geo/"+overlay.myType+".php?id="+overlay.myId, function(data, responseCode) {
						overlay.openInfoWindowHtml(data);
					});
				}
			});
		}
	}
</script>
<?

do_footer();
?>
