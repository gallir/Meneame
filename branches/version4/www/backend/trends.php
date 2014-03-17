<?php
include_once('../config.php');
include(mnminclude.'search.php');

$_REQUEST['q'] = substr(trim(strip_tags($_REQUEST['q'])), 0, 100);
$q = preg_split('/,/', $_REQUEST['q'], 6, PREG_SPLIT_NO_EMPTY);

/* Select the Sphinx indices to use */
switch ($_REQUEST['w']) {
	case 'comments':
		$indices = 'comments';
		$sort = 'karma';
		break;
	case 'posts':
		$indices = 'posts';
		$sort = 'karma';
		break;
	default:
		$indices = 'links';
		$sort = 'votes';
}

header('Content-Type: application/json; charset=utf-8');


$series = array();
$min_yymm = PHP_INT_MAX;
$max_yymm = (int) date('Ym');

$sp = new RGDB('', '', '', $globals['sphinx_server']);
$sp->port = 9306;
$sp->connect();

$cache = new Annotation("sphinx-$indices");
if (false && $cache->read()) {
	/* If totals' cache is valid, just load the array */
	$totals = json_decode($cache->text, true);
} else {
	/* Otherwise, query to Sphinx and fills $totals */
	$totals = array();

	$res = $sp->get_results("select yearmonth(date) as yymm, count(*) as _count from $indices group by yymm limit 2000 option ranker = none");
	if ($res) {
		foreach ($res as $o) {
			$a = (array) $o;
			$totals[$o->yymm] = intval($a["_count"]);
		}
	}
	$cache->text = json_encode($totals);
	$cache->store(time()+86400);
}


$sql = '';
$s = 0;
/* Build de Sphinx SQL query for each word or phrase, each one is a "serie" */
foreach ($q as $words) {
	$words = trim($words);
	$series[$s] = array();
	/* Common attributes for each serie */
	$series[$s]['words'] = $words;
	$series[$s]['objects'] = array();
	$series[$s]['sort'] = $sort;
	$sql .= "select yearmonth(date) as yymm, $sort, date, count(*) as _count from $indices where match('\"$words\"') group by yymm within group order by $sort desc limit 1000;";
	$s++;
}

$s = 0;
if ($sp->multi_query($sql)) {
	do {
		/* For each query/serie */
		if( ($result = $sp->store_result()) ) {
			while (($row = $result->fetch_array())) {
				/* We load the data in objets appended to an array for each serie */
				load_row($series[$s], $row);
			}
		}
		$s++;
	} while($sp->next_result());
}
$sp->close();

/* Sort data and complete with information needed for Flotchart and the tooltip */
$data = array();
foreach ($series as $s) {
	$started = false;
	$o = new stdClass();
	$o->label = $s['words'];
	$o->sort = $s['sort'];
	$o->data = array();
	$o->count = array();
	$o->id = array();
	$o->ts = array();
	$o->yymm = array();
	$suspect = null;
	$yymm = $min_yymm;
	while($yymm <= $max_yymm) {
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
				$suspect->yymm = $d->yymm;
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

function load_row(&$serie, $row) {
	global $min_yymm, $totals;

	if (! $totals[$row['yymm']] > 0) continue;
	$normalized = $row['_count'] / $totals[$row['yymm']];
	$o = new stdClass();
	$o->id = (int) $row['id'];
	$o->date = $row['date'];
	$o->count =  (int)$row['_count'];
	$o->value =  $normalized;
	$o->$serie['sort'] = $row[$serie['sort']];
	$o->ts = (int)$row['date'];
	$o->yymm = (int)$row['yymm'];
	$serie['objects']["$o->yymm"] = $o;
	if ($o->yymm < $min_yymm) $min_yymm = $o->yymm;
}

