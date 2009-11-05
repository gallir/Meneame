<script type="text/javascript"> 
//<!--
$(function () {
	var options = {
		lines: { show: true },
		points: { show: true },
		legend: { position: "nw" },
		xaxis: { mode: "time", minTickSize: [1, "hour"], },
		yaxis: { min: 0 },
		y2axis: { min: 0 },
		grid: { hoverable: true },
	};
	var data = [];
	var placeholder = $("#flot");
	$.getJSON(base_url+"backend/karma-story.json.php?id=<?echo $link->id?>", 
		function (json) {
			for (i=0; i<json.length; i++) {
				data.push(json[i]); 
			}
			$.plot(placeholder, data, options);
		});

	function showTooltip(x, y, contents) {
		$('<div id="tooltip">' + contents + '</div>').css( {
			position: 'absolute',
			display: 'none',
			top: y,
			left: x + 5,
			border: '1px solid #e2d3b0',
			padding: '3px',
			'background-color': '#FFEEC7',
			opacity: 0.85,
			'text-align': 'left',
			'font-size': '85%',
		}).appendTo("body").fadeIn(200);
	}

	var previousPoint = null;
	$("#flot").bind("plothover", function (event, pos, item) {
		if (item) {
			if (previousPoint != item.datapoint) {
				previousPoint = item.datapoint;
				var txt = '<strong>'+item.series.label+':</strong> ' + item.datapoint[1];
				if (item.series.label == 'karma') {
					var ktime = item.datapoint[0]/1000; // to epoch time
					if (k_old[ktime] != 0) {
						if (item.datapoint[1] > k_old[ktime]) txt = txt + '&nbsp;<img src="'+base_static + 'img/common/vote-up01.png"/>';
						else if (item.datapoint[1] < k_old[ktime]) txt = txt + '&nbsp;<img src="'+base_static + 'img/common/vote-down01.png"/>';
						txt = txt + '<br/><strong>previous karma:</strong> '+k_old[ktime];
					}
					if (k_coef[ktime] != 0) txt = txt + '<br/><strong>coefficient:</strong> '+k_coef[ktime];
					if (k_annotation[ktime] != '') txt = txt + '<br><strong>notes</strong><br/>'+k_annotation[ktime];
				}
				$("#tooltip").remove();
				showTooltip(item.pageX, item.pageY, txt);
			}
		} else {
			$("#tooltip").remove();
			previousPoint = null;            
		}
	})


	});
//-->
</script> 
