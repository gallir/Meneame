var geo_map = null;
var geocoder = null;
var geo_mallorca = new GLatLng(39.574998,2.914124);
var geo_last_point;
var geo_last_address;

function geo_basic_load(lat, lng, zoom) {
	if (GBrowserIsCompatible()) {
		geo_map = new GMap2(document.getElementById("map"));
		zoom = zoom || 7;
		if (lat || lng) {
			point = new GLatLng(lat, lng);
			geo_map.setCenter(point, zoom);
		} else {
			geo_map.setCenter(geo_mallorca, zoom);
		}
		return true;
	}
	return false;
}

function geo_coder_load(lat, lng, zoom) {
	if(geo_basic_load(lat, lng, zoom)) {
		geo_map.addControl(new GSmallZoomControl());
		if (lat || lng) {
			geo_map.addOverlay(new GMarker(point));
			point = new GLatLng(lat, lng);
		}
		return true;
	}
	return false;
}

function geo_coder_editor_load(lat, lng, zoom) {
	if (geo_coder_load(lat, lng, zoom))
		geo_add_click_listener()
}

function geo_add_click_listener() {
	GEvent.addListener(geo_map, "click", function(overlay, point) {
		geo_last_point = point;
		geo_last_address = point.toString().replace(/[\(\)]/g, '');
		geo_map.clearOverlays();
		geo_map.addOverlay(new GMarker(point));
		document.geocoderform.geosave.disabled = false;
		document.geocoderform.address.value = geo_last_address;
	});
}

function geo_show_address() {
	if (! geocoder) {
		geocoder = new GClientGeocoder();
		geocoder.setBaseCountryCode('ES')
	}
	if (geocoder && document.geocoderform.address.value) {
		var address = document.geocoderform.address.value;
		if (address.match(/^ *-*[0-9\.]+, *-*[0-9\.]+ *$/)) {
			coords = address.split(/[, ]+/);
			geo_found_point(new GLatLng(coords[0], coords[1]));
		} else {
			geocoder.getLatLng(
				address,
				function(point) {
					if (!point) {
						geo_last_point = false;
						geo_last_address = false;
						document.geocoderform.geosave.disabled = true;
						alert('"'+address+'"' + " not found");
					} else {
						geo_found_point(point);
					}
				}
			);
		}
	}
	return false;
}

function geo_found_point(point) {
	geo_map.clearOverlays();
	geo_last_point = point;
	geo_last_address = document.geocoderform.address.value;
	geo_map.panTo(point);
	var marker = new GMarker(point);
	geo_map.addOverlay(marker);
	document.geocoderform.geosave.disabled = false;
	//marker.openInfoWindowHtml(address);
}

function geo_save_current(type, id) {
	if (geo_last_point && geo_last_address) {
		var url = base_url + 'geo/save.php?type='+type+'&id='+id+'&lat='+geo_last_point.lat()+'&lng='+geo_last_point.lng()+'&text='+encodeURIComponent(geo_last_address);
		$.ajax({
			url: url,
			dataType: "html",
			success: function(html) {
				if (/^ERROR:/.test(html)) {
					alert (html);
				} else {
					geo_map.setCenter(geo_last_point);
					document.geocoderform.geodelete.disabled = false;
					document.geocoderform.geosave.disabled = true;
				}
			}
    	});
	} else {
		alert ('No address to save');
	}
}

function geo_delete(type, id) {
	var url = base_url + 'geo/delete.php?type='+type+'&id='+id;
	$.ajax({
		url: url,
		dataType: "html",
		success: function(html) {
			document.geocoderform.geodelete.disabled = true;
			if (/^ERROR:/.test(html)) {
				alert (html);
			} else {
				geo_map.clearOverlays();
				geo_map.setCenter(geo_mallorca, 7);	
			}
		}
   	});
}

var baseicon = new GIcon();
//baseicon.shadow = "http://labs.google.com/ridefinder/images/mm_20_shadow.png";
baseicon.iconSize = new GSize(12, 20);
//baseicon.shadowSize = new GSize(22, 20);
baseicon.iconAnchor = new GPoint(6, 20);
baseicon.infoWindowAnchor = new GPoint(5, 1);

var iconred = "http://labs.google.com/ridefinder/images/mm_20_red.png"
var iconwhite = "http://labs.google.com/ridefinder/images/mm_20_white.png"
var iconblue = "http://labs.google.com/ridefinder/images/mm_20_blue.png"
var iconorange = "http://labs.google.com/ridefinder/images/mm_20_orange.png"
var iconpurple = "http://labs.google.com/ridefinder/images/mm_20_purple.png"
var iconyellow = "http://labs.google.com/ridefinder/images/mm_20_yellow.png"

var geo_marker_mgr = null;

function geo_load_xml(type, status, zoom, iconimage) {
	GDownloadUrl(base_url+"geo/xml.php?type="+type+"&status="+status, function(data, responseCode) {
		var batch = [];
		var xml = GXml.parse(data);
		var markers = xml.documentElement.getElementsByTagName("marker");
		for (var i = 0; i < markers.length; i++) {
			var point = new GLatLng(parseFloat(markers[i].getAttribute("lat")),
				parseFloat(markers[i].getAttribute("lng")));
			var status = markers[i].getAttribute("status");
			var icon = new GIcon(baseicon);
			icon.image = iconimage;
			marker = new GMarker(point, icon);
			marker.myId = parseInt(markers[i].getAttribute("id"));
			marker.myType = type;
			batch.push(marker);
		}
		geo_marker_mgr.addMarkers(batch, zoom);
		geo_marker_mgr.refresh();
	});
}
