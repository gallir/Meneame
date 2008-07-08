<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


function geo_latlng($type, $id) {
	global $db;

	if ($type == 'user') $table = 'geo_users';
	elseif ($type == 'link') $table = 'geo_links';
	else return false;

	if (!$id > 0) return false;

	return $db->get_row("SELECT X(geo_pt) as lat, Y(geo_pt) as lng, geo_text as text FROM  $table where geo_id=$id");
}

function geo_distance($from, $to) {
	$er = 6366.707;

	$latFrom = deg2rad($from->lat);
	$latTo   = deg2rad($to->lat);
	$lngFrom = deg2rad($from->lng);
	$lngTo   = deg2rad($to->lng);
	
	$x1 = $er * cos($lngFrom) * sin($latFrom);
	$y1 = $er * sin($lngFrom) * sin($latFrom);
	$z1 = $er * cos($latFrom);
	
	$x2 = $er * cos($lngTo) * sin($latTo);
	$y2 = $er * sin($lngTo) * sin($latTo);
	$z2 = $er * cos($latTo);
	
	$d = acos(sin($latFrom)*sin($latTo) + cos($latFrom)*cos($latTo)*cos($lngTo-$lngFrom)) * $er;
	return $d;
}


function geo_insert($type, $id, $lat, $lng, $text) {
	global $db;

	if ($type == 'user') $table = 'geo_users';
	elseif ($type == 'link') $table = 'geo_links';
	else return false;

	$id = (int) $id;
	$lat = (float) $lat;
	$lng = (float) $lng;
	$text = $db->escape($text);
	return $db->query("REPLACE INTO $table (geo_id, geo_text, geo_pt) VALUES ($id, '$text' , GeomFromText('POINT($lat $lng)'))");
}

function geo_delete($type, $id) {
	global $db;

	if ($type == 'user') $table = 'geo_users';
	elseif ($type == 'link') $table = 'geo_links';
	else return false;

	$id = (int) $id;
	return $db->query("DELETE FROM $table WHERE geo_id=$id");
}

function geo_init($f='geo_basic_load', $latlng = false, $zoom = 7, $icontype = 'user') {
	global $globals;
	if (! $globals['google_maps_api']) return false;
	array_push($globals['post_js'], 'http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$globals['google_maps_api']);
	array_push($globals['post_js'], 'geo.js');
	if ($f) {
		if ($latlng) {
			$globals['extra_js_text'] .= '$(function(){'.$f.'('.$latlng->lat.','.$latlng->lng.', '.$zoom.', \''.$icontype.'\');})';
			$globals['body_args'] = 'onunload="GUnload()"';
		} else {
			$globals['extra_js_text'] .= '$(function(){'.$f.'(false, false, '.$zoom.', \''.$icontype.'\');})';
			$globals['body_args'] = 'onunload="GUnload()"';
		}
	} else {
			$globals['body_args'] = 'onunload="GUnload()"';
	}
	return true;
}

function geo_coder_print_form($type, $id, $latlng, $label, $icontype = 'queued') {
	echo '<form action="#" name="geocoderform" id="geocoderform" onsubmit="return geo_show_address(\''.$icontype.'\')">';
	echo '<label for="address">'.$label. '</label><br/>';
	echo '<input type="text" size="40" maxlength="80" name="address" id="address" value="'.$latlng->text.'" />';
	echo '&nbsp;<input type="button" value="'._('buscar').'" onclick="return geo_show_address(\''.$icontype.'\');"/>';
	echo '&nbsp;<input type="button" id="geosave"  disabled="disabled"  value="'._('grabar').'" onclick="return geo_save_current(\''.$type.'\', '.$id.')"/>';
	echo '&nbsp;<input type="button" id="geodelete" ';
	if (!$latlng) {
		echo 'disabled="disabled" ';
	}
	echo 'value="'._('borrar').'" onclick="return geo_delete(\''.$type.'\', '.$id.')"/>';
	echo '<br/>'._('puedes hacer clic en el mapa o buscar por "país", "ciudad, país" o "calle, ciudad, país"...')."\n";
	echo '<br/>'._('también acepta coordenadas "lat, long", i.e. "1.234, -54.321"')."\n";
	echo '</form>'."\n";
?>
	<script type="text/javascript">
	//<![CDATA[
		// Check if there is a map container, otherwise it creates a container
		if ($('#map').length == 0 ) {
			$('#geocoderform').before('<div id="map" style="width:300px;height:250px">&nbsp;<\/div>');
			$('#geocoderform').after('<br/>');
			if (typeof geo_coder_editor_load == 'function') { // Check if the geo.js has bees loaded
				geo_coder_editor_load(false, false, 3, "<?echo $icontype?>");
			} else {
				$(function() {geo_coder_editor_load(false, false, 3, "<?echo $icontype?>")});
			}
		}
	//]]>
	</script>
<?
}
?>
