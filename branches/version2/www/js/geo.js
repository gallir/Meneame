var geo_map = null;
var geocoder = null;
var geo_mallorca = new GLatLng(39.574998,2.914124);
var geo_last_point;
var geo_last_address;

function geo_load(lat, lng) {
	if (GBrowserIsCompatible()) {
		geo_map = new GMap2(document.getElementById("map"));
		geo_map.enableDoubleClickZoom();
		if (lat || lng) {
			point = new GLatLng(lat, lng);
			geo_map.setCenter(point, 7);
			geo_map.addOverlay(new GMarker(point));
		} else {
			geo_map.setCenter(geo_mallorca, 7);
		}
		geocoder = new GClientGeocoder();
		geocoder.setBaseCountryCode('ES')
	}
}

function geo_show_address(address) {
	if (geocoder) {
		geocoder.getLatLng(
			address,
			function(point) {
				if (!point) {
					geo_last_point = false;
					geo_last_address = false;
					alert('"'+address+'"' + " not found");
				} else {
					geo_map.clearOverlays();
					geo_last_point = point;
					geo_last_address = address;
					geo_map.setCenter(point, 7);
					var marker = new GMarker(point);
					geo_map.addOverlay(marker);
					//marker.openInfoWindowHtml(address);
				}
			}
		);
	}
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
					geo_map.setCenter(geo_last_point, 7);
				}
			}
    	});
	} else {
		alert ('No address to save');
	}
}

function geo_delete(type, id) {
	var url = base_url + 'geo/delete.php?type='+type+'&id='+id;
	//alert(url);
	$.ajax({
		url: url,
		dataType: "html",
		success: function(html) {
			if (/^ERROR:/.test(html)) {
				alert (html);
			} else {
				geo_map.clearOverlays();
				geo_map.setCenter(geo_mallorca, 7);	
			}
		}
   	});
}
