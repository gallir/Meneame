<?
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

function chart_link_karma_history($link_id) {
	global $globals;

	echo '<div id="flot" style="width:500px;height:250px;"></div>'."\n";

	echo '<script src="'.$globals['base_static'].'js/jquery.flot.min.js" type="text/javascript"></script>'."\n";
	echo '<script type="text/javascript">';
	echo '$(function () {'."\n";
	echo 'var options = {'."\n";
	echo 'lines: { show: true },'."\n";
	echo 'points: { show: true },'."\n";
	echo 'legend: { position: "nw" },'."\n";
	echo 'xaxis: { mode: "time", minTickSize: [1, "hour"], }'."\n";
	//echo 'xaxis: {tickDecimals: 0, tickSize: 1 }'."\n";
	echo '};'."\n";
	echo 'var data = [];'."\n";
	echo 'var placeholder = $("#flot");'."\n";
	echo '$.plot(placeholder, data, options);'."\n";

	echo '$.getJSON("'.$globals['base_url'].'backend/karma-story.json?id='.$link_id.'", function (json) {';
	echo 'for (i=0; i<json.length; i++) { data.push(json[i]); }';
	echo '$.plot(placeholder, data, options);';
	echo '});';


	echo '});'."\n";
	echo '</script>'."\n";
}
?>
