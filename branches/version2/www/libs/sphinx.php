<?

require_once (mnminclude.'sphinxapi.php');
$globals['sphinx_server'] = 'db2private.meneame.net';
$globals['sphinx_port'] = 3312;



function sphinx_get_search_link($by_date = false, $start = 0, $count = 50) {
	global $globals;

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

	$words = $_REQUEST['q'] = trim(substr(strip_tags($_REQUEST['q']), 0, 250));
	if (empty($words)) return $response;

	if(preg_match('/^ *(\w+): *(.*)/', $words, $matches)) {
		$prefix = $matches[1];
		$words = $matches[2];
	}
	if (preg_match('/^http[s]*/', $prefix)) { // It's an url search
		$words = "$prefix:$words";
		$prefix = false;
		$field = 'url';
	}
	if ($prefix) {
		switch ($prefix) {
			case 'date':
				$by_date = true;
				break;
			case 'url';
				$field = 'url';
				break;
			case 'title';
				$field = 'title';
				break;
			case 'tag':
			case 'tags':
				$field = 'tags';
				break;
		}
	}

	$words_count = count(explode(" ", $words));


	if ($field) {
		$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'date');
		$cl->SetMatchMode (SPH_MATCH_EXTENDED);
		$q = $cl->AddQuery ( "@$field \"$words\"", 'main delta' );
		array_push($queries, $q);
	} elseif ($words_count < 2 || $by_date ) {
		$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'date');
		$cl->SetMatchMode (SPH_MATCH_ALL);
		$q = $cl->AddQuery ( $words, 'main delta' );
		array_push($queries, $q);
	} else  {
		if ($words_count > 2) {
			$cl->SetMatchMode (SPH_MATCH_PHRASE);
			//$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'date');
			$cl->SetSortMode (SPH_SORT_RELEVANCE);
			$q = $cl->AddQuery ( $words, 'main delta' );
			array_push($queries, $q);
		}
		$cl->SetMatchMode (SPH_MATCH_ALL);
		$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'date');
		//$cl->SetSortMode (SPH_SORT_RELEVANCE);
		$q = $cl->AddQuery ( $words, 'main delta' );
		array_push($queries, $q);
		$cl->SetMatchMode (SPH_MATCH_ANY);
		$cl->SetSortMode (SPH_SORT_RELEVANCE);
		$q = $cl->AddQuery ( $words, 'main delta' );
		array_push($queries, $q);
	}


	$results = $cl->RunQueries();

	$n = 0;
	$response['error'] = $results['error'];
	foreach ($queries as $q) {
		$res = $results[$q];
		if ( is_array($res["matches"]) ) {
			$response['rows'] += $res["total_found"];
			$response['time'] += $res["time"];
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
	return $response;
}



