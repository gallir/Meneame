var geo_map = null;
var geocoder = null;
var geo_mallorca = new GLatLng(39.574998,2.914124);
var geo_last_point;
var geo_last_address;

function geo_get_marker(point, icon) {
	var baseicon = new GIcon();
	baseicon.iconSize = new GSize(20, 25);
	baseicon.iconAnchor = new GPoint(10, 25);
	baseicon.infoWindowAnchor = new GPoint(10, 12);
	switch (icon) {
		case 'geo':
		case 'geo_edit':
			baseicon.image = base_url+"img/geo/common/geo-geo01.png";
			break;
		case 'queued':
			baseicon.image = base_url+"img/geo/common/geo-new01.png";
			break;
		case 'published':
			baseicon.image = base_url+"img/geo/common/geo-published01.png";
			break;
		case 'comment':
			baseicon.image = base_url+"img/geo/common/geo-comment01.png";
			break;
		case 'post':
			baseicon.image = base_url+"img/geo/common/geo-newnotame01.png";
			break;
		case 'user':
		default:
			baseicon.image = base_url+"img/geo/common/geo-user01.png";
			break;
	}
	return new GMarker(point, baseicon);
}

function geo_basic_load(lat, lng, zoom) {
	var map;
	if (GBrowserIsCompatible() && (map = document.getElementById("map"))) {
		geo_map = new GMap2(map);
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

function geo_coder_load(lat, lng, zoom, icontype) {
	if(geo_basic_load(lat, lng, zoom)) {
		geo_map.addControl(new GSmallZoomControl());
		if (lat || lng) {
			point = new GLatLng(lat, lng);
			geo_map.addOverlay(geo_get_marker(point, icontype));
		}
		return true;
	}
	return false;
}

function geo_coder_editor_load(lat, lng, zoom, icontype) {
	if (geo_coder_load(lat, lng, zoom, icontype)) {
		geo_add_click_listener(icontype);
	}
}

function geo_add_click_listener(icontype) {
	GEvent.addListener(geo_map, "click", function(overlay, point) {
		if (overlay) return;
		geo_last_point = point;
		geo_last_address = point.toString().replace(/[\(\)]/g, '');
		geo_map.clearOverlays();
		geo_map.addOverlay(geo_get_marker(point, icontype));
		document.geocoderform.geosave.disabled = false;
		document.geocoderform.address.value = geo_last_address;
	});
}

function geo_show_address(icontype) {
	if (! geocoder) {
		geocoder = new GClientGeocoder();
		geocoder.setBaseCountryCode('ES')
	}
	if (geocoder && document.geocoderform.address.value) {
		var address = document.geocoderform.address.value;
		if (address.match(/^ *-*[0-9\.]+, *-*[0-9\.]+ *$/)) {
			coords = address.split(/[, ]+/);
			geo_found_point(new GLatLng(coords[0], coords[1]), icontype);
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
						geo_found_point(point, icontype);
					}
				}
			);
		}
	}
	return false;
}

function geo_found_point(point, icontype) {
	geo_map.clearOverlays();
	geo_last_point = point;
	geo_last_address = document.geocoderform.address.value;
	geo_map.panTo(point);
	geo_map.addOverlay(geo_get_marker(point, icontype));
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
		reportAjaxStats('geo', 'save');
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
	reportAjaxStats('geo', 'delete');
}

function geo_load_xml(type, req_status, zoom) {
	GDownloadUrl(base_url+"geo/xml.php?type="+type+"&status="+req_status, function(data, responseCode) {
		var batch = [];
		var xml = GXml.parse(data);
		var markers = xml.documentElement.getElementsByTagName("marker");
		for (var i = 0; i < markers.length; i++) {
			var point = new GLatLng(parseFloat(markers[i].getAttribute("lat")),
				parseFloat(markers[i].getAttribute("lng")));
			var status = markers[i].getAttribute("status");
			marker = geo_get_marker(point, status);
			marker.myId = parseInt(markers[i].getAttribute("id"));
			marker.myType = type;
			batch.push(marker);
		}
		geo_marker_mgr.addMarkers(batch, zoom);
		geo_marker_mgr.refresh();
	});
}
