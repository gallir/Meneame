<?php
include_once('../config.php');
include(mnminclude.'search.php');

$_REQUEST['q'] = substr(trim(strip_tags($_REQUEST['q'])), 0, 100);
$q = preg_split('/,/', $_REQUEST['q'], 6, PREG_SPLIT_NO_EMPTY);

header('Content-Type: application/json; charset=utf-8');


$indices = 'links';
$queries = array();
$series = array();
$min_yymm = PHP_INT_MAX;
$max_yymm = (int) date('Ym');

$cache = new Annotation("sphinx-links");
if ($cache->read()) {
	$totals = json_decode($cache->text, true);
} else {
	$totals = array();
	$sp = new RGDB('', '', '', $globals['sphinx_server']);
	$sp->port = 9306;
	$sp->connect();

	$res = $sp->get_results("select yearmonth(date) as yymm, @count from links group by yymm limit 2000 option ranker = none");
	if ($res) {
		foreach ($res as $o) {
			$a = (array) $o;
			$totals[$o->yymm] = intval($a["@count"]);
		}
	}
	$cache->text = json_encode($totals);
	$cache->store(time()+86400);
}


$cl = sphinx_client();
if (!$cl) die;

$cl->SetMatchMode(SPH_MATCH_PHRASE);
$cl->SetRankingMode(SPH_RANK_NONE);
$cl->SetLimits(0, 100000, 1000);

$cl->SetSortMode(SPH_SORT_ATTR_DESC, 'votes');
$cl->SetGroupBy('date', SPH_GROUPBY_MONTH);

foreach ($q as $words) {
	$words = trim($words);
	$q = $cl->AddQuery($words, $indices);
	$queries[] = $q;
	$series[$q] = array();
	$series[$q]['words'] = $words;
	$series[$q]['objects'] = array();
}

$results = $cl->RunQueries();

foreach ($queries as $q) {
	$res = $results[$q];
	if (is_array($res["matches"])) {
		foreach ( $res["matches"] as $id => $info ) {
			$normalized = $info['attrs']['@count'] / $totals[$info['attrs']['@groupby']];
			$o = new stdClass();
			$o->id = $id;
			$o->date = $info['attrs']['date'];
			$o->count =  $info['attrs']['@count'];
			$o->value =  $normalized;
			$o->votes = $info['attrs']['votes'];
			$o->ts = $info['attrs']['date'];
			$o->yymm =  $info['attrs']['@groupby'];
			$series[$q]['objects']["$o->yymm"] = $o;
			if ($o->yymm < $min_yymm) $min_yymm = $o->yymm;
		}
	}
}

$data = array();
foreach ($series as $s) {
	$started = false;
	$o = new stdClass();
	$o->label = $s['words'];
	$o->data = array();
	$o->count = array();
	$o->id = array();
	$o->ts = array();
	$o->yymm = array();
	$suspect = null;
	$yymm = $min_yymm;
	while($yymm < $max_yymm) {
		$yy = floor($yymm / 100);
		$mm = $yymm - $yy*100;
		$miliseconds = mktime(0, 0, 0, $mm, 1, $yy) * 1000;
		if (array_key_exists("$yymm", $s['objects'])) {
			$d = $s['objects'][$yymm];
			if (! $started && $d->count < 3) {
				$suspect = new stdClass();
				$suspect->data = array($miliseconds, $d->value);
				$suspect->count = $d->count;
				$suspect->id = $d->id;
				$suspect->ts = $d->ts;
				$suspect->ts = $d->yymm;
			} else {
				if ($suspect) {
					$o->data[] = $suspect->data;
					$o->count[] = $suspect->count;
					$o->id[] = $suspect->id;
					$o->ts[] = $suspect->ts;
					$o->yymm[] = $suspect->yymm;
					$suspect = null;
				}
				$o->data[] = array($miliseconds, $d->value);
				$o->count[] = $d->count;
				$o->id[] = $d->id;
				$o->ts[] = $d->ts;
				$o->yymm[] = $d->yymm;
				$started = true;
			}
		} elseif ($started) {
			$suspect =  null;
			//$o->data[] = array($miliseconds, 0);
		}
		if ($mm == 12) {
			$mm = 1;
			$yy++;
		} else {
			$mm++;
		}
		$yymm = $yy*100+$mm;
	}
	$data[] = $o;
}

echo json_encode($data);

