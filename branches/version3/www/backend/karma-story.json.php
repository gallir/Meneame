<?
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at gallir dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
include('../config.php');
include(mnminclude.'link.php');

$colors = array('negatives' => '#CB4B4B', 'positives' => '#4DA74D', 'anonymous' => '#AFD8F8', 'karma' => '#FF6400');

header('Content-Type: application/json; charset=utf-8');

if (empty($_GET['id'])) die;
$id = intval($_GET['id']);
$link = new Link;
$link->id=$id;
$link->read();
if(!$link->read) die;
if ( ($array = $link->read_annotation("link-karma")) != false ) {
	$data['anonymous'] = array();
	$data['positives'] = array();
	$data['negatives'] = array();
	$data['karma'] = array();

	$array = array_reverse($array);
	foreach ($array as $log) {
		foreach (array_keys($data) as $key) {
			$item = array($log['time']*1000, $log[$key]);
			array_push($data[$key], $item);
		}
	}
}
// Generate the JSON array
echo "[\n";
foreach (array_keys($data) as $key) {
	echo "{\n";
	echo "label: '$key',\n";
	if ($key == 'karma') echo "yaxis: 2,\n";
	if (!empty($colors[$key])) {
		echo "color: '$colors[$key]',\n";
	}
	echo "data: [";
	foreach ($data[$key] as $d) {
		echo "[$d[0], $d[1]], ";
	}
	echo "]\n";
	echo "},\n";
}
echo "]\n";
?>

