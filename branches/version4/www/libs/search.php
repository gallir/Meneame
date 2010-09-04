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

function sphinx_do_search($by_date = false, $start = 0, $count = 10) {
	global $globals;

	$start_time = microtime(true);

	$indices = $_REQUEST['w'].' '.$_REQUEST['w'].'_delta';

	$cl = new SphinxClient ();
	$cl->SetServer ($globals['sphinx_server'], $globals['sphinx_port']);
	$cl->SetLimits ( $start, $count );
	// status, title, tags, url,  content
	//$cl->SetWeights ( array ( 0, 4, 2, 1, 1 ) );
	if ($_REQUEST['w'] == 'links') {
		$cl->SetFieldWeights(array('title' => 4, 'tags' => 2, 'url' => 1, 'content' => 1));
	} else {
		$cl->SetFieldWeights(array('content' => 1));
	}

	$response = array();
	$queries = array();
	$recorded = array();

	$response['rows'] = 0;
	$response['time'] = 0;

	if (empty($_REQUEST['words'])) return $response;


	$words_array = preg_split('/\s+/', $_REQUEST['words'], -1, PREG_SPLIT_NO_EMPTY);
	$words_count = count($words_array);
	$words = $_REQUEST['words'];


	if ($_REQUEST['t']) {
		$max_date = time();
		$min_date = intval($_REQUEST['t']);
		$cl->SetFilterRange('date', $min_date, $max_date);
	}

	if ($_REQUEST['h']) {
		$max_date = time();
		$min_date = $max_date - intval($_REQUEST['h']) * 3600;
		$cl->SetFilterRange('date', $min_date, $max_date);
	}

	if ($_REQUEST['w'] == 'links' && $_REQUEST['s']) {
		$cl->SetFilter('status', array($_REQUEST['s_id']));
	}

	if ($_REQUEST['u']) {
		$u = new User();
		$u->username = $_REQUEST['u'];
		$u->read();
		$cl->SetFilterRange('user', $u->id, $u->id);
	}

	if ($_REQUEST['w'] == 'links' && $_REQUEST['p']) {
		$f = '@'.$_REQUEST['p'];
	} else {
		$f = '@*';
	}

	if ($by_date || $_REQUEST['o'] == 'date') {
		$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'date');
	} elseif ($_REQUEST['o'] == 'pure') {
			$cl->SetSortMode (SPH_SORT_RELEVANCE);
	} else {
			//$cl->SetSortMode (SPH_SORT_TIME_SEGMENTS, 'date');
			//$cl->SetSortMode(SPH_SORT_EXTENDED, "@weight DESC, id DESC");
			$cl->SetSortMode(SPH_SORT_EXPR, "@weight - (NOW() - date)/20000");
			//$cl->SetSortMode(SPH_SORT_EXPR, "@weight");
	}

	$cl->SetMatchMode (SPH_MATCH_EXTENDED2);

	if ($_REQUEST['p'] == 'url') {
		$q = $cl->AddQuery ( "$f \"$words\"", $indices );
		array_push($queries, $q);
	} else {
		if ($words_count < 5) {
			$q = $cl->AddQuery ( "$f $words", $indices );
			array_push($queries, $q);
		}
	}


	// If there are no boolean opertions, add a new search for ANY of the terms
	// Take in account phrases in between " and '
	if (!preg_match('/( and | or | [\-\+\&\|])/i', $words) && $words_count > 1 && $_REQUEST['p'] != 'url') {
		$words = '';
		$quotes = 0;
		$c = 0;
		foreach ($words_array as $w) {
			if ($c > 0 && $quotes == 0) {
				$words .= ' | ';
			}
			if ($quotes == 0 && preg_match('/^["\']/', $w)) $quotes++;
			if ($quotes > 0 && preg_match('/["\']$/', $w)) $quotes--;
			$words .= " $w";
			$c++;
		}
		//echo "$f $words" . "<br/>";
		$q = $cl->AddQuery ( "$f $words", $indices );
		array_push($queries, $q);
	}

	$results = $cl->RunQueries();


	$n = 0;
	$response['error'] = $results['error'];
	foreach ($queries as $q) {
		$res = $results[$q];
		if ( is_array($res["matches"]) ) {
			$response['rows'] += $res["total_found"];
			foreach ( $res["matches"] as $doc => $docinfo ) {
				if (!$recorded[$doc]) {
					$response['ids'][$n] = $doc;
					$response['weights']["$doc"] = $docinfo['weight'];
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


	$_REQUEST['words'] = $_REQUEST['q'] = trim(substr(strip_tags(stripslashes($_REQUEST['q'])), 0, 500));

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
	if (isset($_REQUEST['o']) && ! preg_match('/^(date|relevance|pure)$/', $_REQUEST['o'])) unset($_REQUEST['o']);

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
