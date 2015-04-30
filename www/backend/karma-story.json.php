<?php
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at gallir dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
include('../config.php');

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
	$data['clicks'] = array();

	$array = array_reverse($array);
	foreach ($array as $log) {
		foreach (array_keys($data) as $key) {
			$item = array($log['time']*1000, $log[$key]);
			array_push($data[$key], $item);
		}
	}

	$objects = array();
	foreach (array_keys($data) as $key) {
		$obj = array();
		$obj['label'] = $key;
		if ($key == 'karma' || $key == 'clicks') $obj['yaxis'] = 2;
		if (!empty($colors[$key])) $obj['color'] = $colors[$key];
		$obj['data'] = $data[$key];
		array_push($objects, $obj);
	}
	echo json_encode($objects);
}
