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
			var geoXml = new GGeoXml("http://<? echo get_server_name() . $globals['base_url'] ?>rss2.php?rows=100");
			geo_map.addOverlay(geoXml);
		}
	}
</script>

echo '</div>';
echo '</div>';
do_footer();
?>
