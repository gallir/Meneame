<?php
require_once (mnminclude.'sphinxapi.php');

function do_search($by_date = false, $start = 0, $count = 50, $proximity = true) {
	search_parse_query();
	if ($_REQUEST['w'] == 'links' && ($_REQUEST['p'] == 'site' || $_REQUEST['p'] == 'url_db')) {
		return db_get_search_links($by_date, $start, $count);
	} else {
		return sphinx_do_search($by_date, $start, $count);
	}

}

function sphinx_client() {
	global $globals, $db;

	static $cl = false;
	if (!$cl) {
		$cl = new SphinxClient ();
		$cl->SetServer ($globals['sphinx_server'], $globals['sphinx_port']);

		// Request for status values, it's used in other sites
		$globals['status_values'] = $db->get_enum_values('links', 'link_status');
	}
	return $cl;
}

function sphinx_do_search($by_date = false, $start = 0, $count = 10, $proximity = true) {
	global $globals;

	$response = array();
	$queries = array();
	$recorded = array();


	$start_time = microtime(true);

	$indices = $_REQUEST['w'].' '.$_REQUEST['w'].'_delta';

	$cl = sphinx_client();
	if (!$cl) return $response;

	$cl->SetLimits ( $start, $count );
	if ($_REQUEST['w'] == 'links') {
		$cl->SetFieldWeights(array('title' => 3, 'tags' => 3, 'url' => 1, 'content' => 1));
	} else {
		$cl->SetFieldWeights(array('content' => 1));
	}

	// Function to filter by sub site
	if ($_REQUEST['w'] == 'links' && $globals['submnm']) {
		$subs = array();
		$subs[] = SitesMgr::my_id();
		$subs = array_merge($subs, SitesMgr::get_sub_subs_ids());
		$cl->SetFilter('sub', $subs);
	}

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

	if ($_REQUEST['yymm']) {
		$yymm = intval($_REQUEST['yymm']);
		$yy = intval($yymm / 100);
		$mm = $yymm - $yy*100;
        $min_date = mktime(0, 0, 0, $mm, 1, $yy);
        if ($mm == 12) {
            $mm = 1;
            $yy++;
        } else {
            $mm++;
        }
        $max_date = mktime(0, 0, 0, $mm, 1, $yy);
		$cl->SetFilterRange('date', $min_date, $max_date);
	}


	if ($_REQUEST['w'] == 'links' && $_REQUEST['s']) {
		if (preg_match('/^ *! */', $_REQUEST['s'])) {
			// Allows to reject a status
			$_REQUEST['s'] = preg_replace('/^ *! */', '', $_REQUEST['s']);
			$s_reject = true;
		} else {
			$s_reject = false;
		}

		// Allow multiple statuses
		$statuses = preg_split('/\s+/', $_REQUEST['s'], -1, PREG_SPLIT_NO_EMPTY);

		$s_id = array();
		foreach ($statuses as $s) {
			if (isset($globals['status_values'][$s])) {
				array_push($s_id, $globals['status_values'][$s]);
			}
		}
		if (count($s_id) > 0) {
			$cl->SetFilter('status', $s_id, $s_reject);
		}
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

	if ($by_date || $_REQUEST['o'] == 'date' || $_REQUEST['p'] == 'url') {
		$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'date');
	} elseif ($_REQUEST['o'] == 'pure') {
		$cl->SetSortMode (SPH_SORT_RELEVANCE);
	} else {
		// If "root_time", it will center the search on that timestamp
		if ($_REQUEST['root_time'] > 0) {
			$now = intval($_REQUEST['root_time']);
		} else {
			$now = time();
		}

		// expressions to decrease weights logarimically
		if ($_REQUEST['w'] == 'links') {
			$p = $globals['status_values']['published'];
			$q = $globals['status_values']['queued'];

			$b = log(0.9)/720;
			$fp = "@weight * max(0.4, exp($b*abs($now-date)/3600))";
			$b = log(0.5)/720;
			$fq = "@weight * max(0.25, exp($b*abs($now-date)/3600))";
			$b = log(0.2)/720;
			$fo = "@weight * max(0.1, exp($b*abs($now-date)/3600))";
			$exp = "if (status-$p = 0, $fp , if (status-$q = 0, $fq, $fo))";
		} else {
			$b = log(0.95)/720;
			$exp = "@weight * max(0.5, exp($b*abs($now-date)/3600))";
		}
		$cl->SetSortMode(SPH_SORT_EXPR, $exp);
	}

	$cl->SetMatchMode (SPH_MATCH_EXTENDED2);

	if ($words_count == 1 || $_REQUEST['p'] == 'url' ) $cl->SetRankingMode(SPH_RANK_NONE); // Don't use rank ofr one word
	elseif ($proximity) $cl->SetRankingMode(SPH_RANK_PROXIMITY_BM25); // Default: freq and proximity
	else $cl->SetRankingMode(SPH_RANK_BM25); // Used for related links

	if ($_REQUEST['p'] == 'url') {
		// It allows to search for several domains/url
		for ($i = 0; $i < count($words_array); $i++) {
			$words_array[$i] = '="'.$cl->EscapeString($words_array[$i]).'"';
		}
		$query = implode(" | ", $words_array); // Add the "OR" for several domain
		$q = $cl->AddQuery ( "$f $query", $indices );
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
			if ($quotes > 0 && preg_match('/["\'](\~{0,1}\d+){0,1}$/', $w)) $quotes--;
			$words .= " $w";
			$c++;
		}
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

function sphinx_doc_hits($q, $index = 'links') {
	$cl = sphinx_client();
	if (!$cl) return 0;

	$hits = PHP_INT_MAX;
	$keys = $cl->BuildKeywords ($q, $index, true);
	if (! is_array($keys)) return $hits;
	foreach ($keys as $k) {
		if ($k['docs'] >= 0 && $k['docs'] < $hits) {
			$hits = $k['docs'];
		}
	}
	if (count($keys) >1) {
		// Heuristic to reduce hits for a phrase
		// because BuildKeywords always parses and splits words
		$hits = $hits / (4 * count($keys));
	}
	//echo "<!-- $q: $hits -->\n";
	return $hits;
}


function db_get_search_links($by_date = false, $start = 0, $count = 50) {
	global $db, $globals;
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
				$_REQUEST['p'] = 'url';
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
}

?>
