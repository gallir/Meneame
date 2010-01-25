<?
require_once (mnminclude.'sphinxapi.php');

function do_search($by_date = false, $start = 0, $count = 50) {
	search_parse_query();
	if ($_REQUEST['w'] == 'links' && ($_REQUEST['p'] == 'site' || $_REQUEST['p'] == 'url_db')) {
		return db_get_search_links($by_date, $start, $count);
	} else {
		return sphinx_do_search($by_date, $start, $count);
	}

}

function sphinx_do_search($by_date = false, $start = 0, $count = 50) {
	global $globals;

	$start_time = microtime(true);

	$indices = $_REQUEST['w'].' '.$_REQUEST['w'].'_delta';

	$cl = new SphinxClient ();
	$cl->SetServer ($globals['sphinx_server'], $globals['sphinx_port']);
	$cl->SetLimits ( $start, $count );
	// status, title, tags, url,  content
	$cl->SetWeights ( array ( 0, 4, 2, 1, 1 ) );

	$response = array();
	$queries = array();
	$recorded = array();

	$response['rows'] = 0;
	$response['time'] = 0;

	if (empty($_REQUEST['words'])) return $response;


	$words_array = explode(" ", $_REQUEST['words']);
	$words_count = count($words_array);
	$words = $_REQUEST['words'];


	if ($_REQUEST['h']) {
		$max_date = time();
		$min_date = $max_date - $_REQUEST['h'] * 3600;
		$cl->SetFilterRange('date', $min_date, $max_date);
	}

	if ($_REQUEST['w'] == 'links' && $_REQUEST['s']) {
		$cl->SetFilter('status', array($_REQUEST['s_id']));
	}

	if ($_REQUEST['w'] == 'links' && $_REQUEST['p']) {
		$f = '@'.$_REQUEST['p'];
	} else {
		$f = '@*';
	}

	if ($by_date || $_REQUEST['o'] == 'date') {
		$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'date');
	} else {
		$cl->SetSortMode (SPH_SORT_TIME_SEGMENTS, 'date');
		//$cl->SetSortMode (SPH_SORT_RELEVANCE);
	}

	$cl->SetMatchMode (SPH_MATCH_EXTENDED2);
	if ($_REQUEST['p'] == 'url') {
		$q = $cl->AddQuery ( "$f \"$words\"", $indices );
	} else {
		$q = $cl->AddQuery ( "$f $words", $indices );
	}
	array_push($queries, $q);

	// If there are no boolean opertions, add a new search for ANY of the terms
	if (!preg_match('/( and | or | [\-\+\&\|])/i', $words) && $words_count > 1) {
		$n = 0;
		foreach ($words_array as $w) {
			if ($n > 0) $f .= ' |';
			$f .= " $w";
			$n++;
		}
		$q = $cl->AddQuery ( $f, $indices );
		array_push($queries, $q);
	}


	$results = $cl->RunQueries();


	$n = 0;
	$response['error'] = $results['error'];
	foreach ($queries as $q) {
		$res = $results[$q];
		if ( is_array($res["matches"]) ) {
			$response['rows'] += $res["total_found"];
			// $response['time'] += $res["time"];
			foreach ( $res["matches"] as $doc => $docinfo ) {
				if (!$recorded[$doc]) {
					$response['ids'][$n] = $doc;
					$recorded[$doc] = true;
					$n++;
				} else {
					$response['rows']--;
				}
			}
		}
	}
	$response['time'] = microtime(true) - $start_time;
	return $response;
}


function db_get_search_links($by_date = false, $start = 0, $count = 50) {
	global $db;
	// For now it serves for search specific blogs (used from most voted sites)

	$response = array();
	$start_time = microtime(true);
	$url = $db->escape($_REQUEST['q']);
	if ($_REQUEST['p'] == 'site') {
		$site_ids = $db->get_col("select blog_id from blogs where blog_url like '$url%'");
		if ($site_ids) {
			$list = implode(',', $site_ids);
			$from = "links";
			$where = "link_blog in ($list)";
			$order = "order by link_date desc";
		}
	} else {
		$from = "links";
		$where = "link_url like '$url%'";
		$order = "order by link_id desc";
	}

	if ($_REQUEST['s']) {
		$status = $db->escape($_REQUEST['s']);
		$where .= " and link_status = '$status'";
	}

	if ($_REQUEST['h']) {
		$hours = intval($_REQUEST['h']);
		$where .= " and link_date > date_sub(now(), interval $hours hour)";
	}
	if ($where && $from) { 
		$sql = "select link_id from $from where $where $order limit $start,$count";
		$response['rows'] = $db->get_var("select count(*) from $from where $where");
		if ($response['rows'] > 0) {
			$response['ids'] = array();
			$ids = $db->get_col($sql);
			foreach ($ids as $id) $response['ids'][] = $id;
		}
	}
	$response['time'] = microtime(true) - $start_time;
	return $response;
}

function search_parse_query() {
	global $db;

	// Check what should be searched
	switch ($_REQUEST['w']) {
		case 'posts':
		case 'comments':
		case 'links':
			break;
		default:
			$_REQUEST['w'] = 'links';
	}


	$_REQUEST['words'] = $_REQUEST['q'] = trim(substr(strip_tags(stripslashes($_REQUEST['q'])), 0, 250));

	if (!empty($_REQUEST['p'])) {
		$_REQUEST['p'] = clean_input_url($_REQUEST['p']);
	} elseif (preg_match('/^ *(\w+): *(.*)/', $_REQUEST['q'], $matches)) {
		$_REQUEST['words'] = $matches[2];
		switch ($matches[1]) {
			case 'http':
			case 'https':
				$_REQUEST['words'] = $_REQUEST['q'];
				$_REQUEST['o'] = 'date';
				$_REQUEST['p'] = 'url_db';
				break;
			case 'date':
				$_REQUEST['o'] = 'date';
				break;
			case 'url';
				$_REQUEST['p'] = 'url';
				break;
			case 'title';
				$_REQUEST['p'] = 'title';
				break;
			case 'tag':
			case 'tags':
				$_REQUEST['p'] = 'tags';
				break;
		}
	} 


	// Check filters and clean
	if (isset($_REQUEST['h'])) $_REQUEST['h'] = intval($_REQUEST['h']);
	if (isset($_REQUEST['p']) && ! preg_match('/^(url|tags|title|site|url_db)$/', $_REQUEST['p'])) unset($_REQUEST['p']);
	if (isset($_REQUEST['o']) && ! preg_match('/^(date|relevance)$/', $_REQUEST['o'])) unset($_REQUEST['o']);

	if ($_REQUEST['w'] == 'links' && isset($_REQUEST['s'])) {
		// Retrieve available status values
		$row = $db->get_row("SHOW COLUMNS FROM links like 'link_status'");
		preg_match_all("/'(.*?)'/", $row->Type, $matches);
		$i = array_search($_REQUEST['s'], $matches[1]);
		if ($i !== false) {
			$_REQUEST['s_id'] = $i+1;
		} else {
			unset($_REQUEST['s']);
		}
	}
}

?>
