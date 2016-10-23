<?php
// The source code packaged with this file is Free Software, Copyright (C) 2009 by
// Ricardo Galli <gallir at uib dot es> and
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>

include_once(__DIR__.'/../config.php');
require(mnminclude.'search.php');

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache');

$id = intval($_GET['id']);

$link = Link::from_db($id);
if (!$link) {
	echo "Link not found\n";
	die;
}

$results = array();
$words = array();
$results['str'] = $link->tags;
$results['docs'] = $db->get_var("select max(link_id) from links");
$results['phrases'] = 0;
$results['in_title'] = 0;
$results['min_freq'] = 100;
$results['max_freq'] = 0;
$results['highs'] = 0;


$a = preg_split('/,+/', $link->tags, -1, PREG_SPLIT_NO_EMPTY);
$results['tags'] = count($a);

foreach ($a as $w) {
	$w = trim($w);
	$r = array();
	$r['w'] = $w;
	$r['len'] = mb_strlen($w);
	$r['hits'] = $h = min($results['docs'], intval(sphinx_doc_hits($w)));
	$r['freq'] = round(100*$h/$results['docs'],1);
	if ($r['freq'] < $results['min_freq']) {
		$results['min_freq'] = $r['freq'];
	}
	if ($r['freq'] > $results['max_freq']) {
		$results['max_freq'] = $r['freq'];
	}

	if ($r['freq'] > 1) {
		$results['highs'] += 1;
	}
	if (preg_match('/ /', $w)) {
		$results['phrases'] += 1;
		$r['phrase'] = true;
	} else {
		$r['phrase'] = false;
	}

	if (preg_match('/'.preg_quote($w, '/').'/ui', $link->title)) {
		$results['in_title'] += 1;
		$r['in_title'] = true;
	}
	$words[$w] = $r;
}

Haanga::Load('backend/tags_analysis.html', compact('results', 'words'));

