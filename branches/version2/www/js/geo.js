var geo_map = null;
var geocoder = null;
var geo_mallorca = new GLatLng(39.574998,2.914124);
var geo_last_point;
var geo_last_address;

function geo_basic_load(lat, lng, zoom) {
	if (GBrowserIsCompatible()) {
		geo_map = new GMap2(document.getElementById("map"));
		geo_map.enableDoubleClickZoom();
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
		if (lat || lng) {
			geo_map.addOverlay(new GMarker(point));
			point = new GLatLng(lat, lng);
		}
	}
}

function geo_show_address(form) {
	if (! geocoder) {
		geocoder = new GClientGeocoder();
		//geocoder.setBaseCountryCode('ES')
	}
	if (geocoder && form.address.value) {
		var address = form.address.value;
		geocoder.getLatLng(
			address,
			function(point) {
				if (!point) {
					geo_last_point = false;
					geo_last_address = false;
					form.geosave.disabled = true;
					alert('"'+address+'"' + " not found");
				} else {
					geo_map.clearOverlays();
					geo_last_point = point;
					geo_last_address = form.address.value;
					geo_map.setCenter(point);
					var marker = new GMarker(point);
					geo_map.addOverlay(marker);
					form.geosave.disabled = false;
					//marker.openInfoWindowHtml(address);
				}
			}
		);
	}
	return false;
}

function geo_save_current(type, id, form) {
	if (geo_last_point && geo_last_address) {
		var url = base_url + 'geo/save.php?type='+type+'&id='+id+'&lat='+geo_last_point.lat()+'&lng='+geo_last_point.lng()+'&text='+encodeURIComponent(geo_last_address);
		$.ajax({
			url: url,
			dataType: "html",
			success: function(html) {
				if (/^ERROR:/.test(html)) {
					alert (html);
				} else {
					geo_map.setCenter(geo_last_point, 7);
					form.geodelete.disabled = false;
					form.geosave.disabled = true;
				}
			}
    	});
	} else {
		alert ('No address to save');
	}
}

function geo_delete(type, id, form) {
	var url = base_url + 'geo/delete.php?type='+type+'&id='+id;
	//alert(url);
	$.ajax({
		url: url,
		dataType: "html",
		success: function(html) {
			form.geodelete.disabled = true;
			if (/^ERROR:/.test(html)) {
				alert (html);
			} else {
				geo_map.clearOverlays();
				geo_map.setCenter(geo_mallorca, 7);	
			}
		}
   	});
}
