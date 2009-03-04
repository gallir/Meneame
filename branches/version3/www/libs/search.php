<?
require_once (mnminclude.'sphinxapi.php');

function get_search_links($by_date = false, $start = 0, $count = 50) {
	$response = db_get_search_links($by_date, $start, $count);
	if ($response) return $response;
	return sphinx_get_search_links($by_date, $start, $count);

}

function sphinx_get_search_links($by_date = false, $start = 0, $count = 50) {
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

	if (!empty($_REQUEST['p'])) {
		$prefix = clean_input_url($_REQUEST['p']);
	} elseif (preg_match('/^ *(\w+): *(.*)/', $words, $matches)) {
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
		$cl->SetMatchMode (SPH_MATCH_EXTENDED2);
		$q = $cl->AddQuery ( "@$field \"$words\"", '*' );
		array_push($queries, $q);
	} elseif ($words_count < 2 || $by_date ) {
		$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'date');
		$cl->SetMatchMode (SPH_MATCH_ALL);
		$q = $cl->AddQuery ( $words, '*' );
		array_push($queries, $q);
	} else  {
		if ($words_count > 2) {
			$cl->SetMatchMode (SPH_MATCH_PHRASE);
			//$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'date');
			$cl->SetSortMode (SPH_SORT_RELEVANCE);
			$q = $cl->AddQuery ( $words, '*' );
			array_push($queries, $q);
		}
		$cl->SetMatchMode (SPH_MATCH_ALL);
		$cl->SetSortMode (SPH_SORT_ATTR_DESC, 'date');
		//$cl->SetSortMode (SPH_SORT_RELEVANCE);
		$q = $cl->AddQuery ( $words, '*' );
		array_push($queries, $q);
		$cl->SetMatchMode (SPH_MATCH_ANY);
		$cl->SetSortMode (SPH_SORT_RELEVANCE);
		$q = $cl->AddQuery ( $words, '*' );
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


function db_get_search_links($by_date = false, $start = 0, $count = 50) {
	global $db;
	// For now it serves for search specific blogs (used from most voted sites)
	if (!preg_match('/^site: *(http[^ ]+)/i', $_REQUEST['q'])) return false;

	$response = array();
	$start_time = microtime(true);
	if (preg_match('/^site: *(http[^ ]+)/i', $_REQUEST['q'], $match)) {
		$url = $db->escape($match[1]);
		$site_ids = $db->get_col("select blog_id from blogs where blog_url like '$url%'");
		if ($site_ids) {
			$list = implode(',', $site_ids);
			$from = "links";
			$where = "link_blog in ($list)";
		}
	}

	if (preg_match('/ status: *(\w+)/i', $_REQUEST['q'], $match)) {
		$status = $db->escape($match[1]);
		$where .= " and link_status = '$status'";
	}

	if (preg_match('/ period: *(\d+)/i', $_REQUEST['q'], $match)) {
		$hours = intval($match[1]);
		$where .= " and link_date > date_sub(now(), interval $hours hour)";
	}
	if ($where && $from) { 
		$sql = "select link_id from $from where $where order by link_status, link_date desc limit $start,$count";
		$response['rows'] = $db->get_var("select count(*)  from $from where $where");
		if ($response['rows'] > 0) {
			$response['ids'] = array();
			$ids = $db->get_col($sql);
			foreach ($ids as $id) $response['ids'][] = $id;
		}
	}
	$response['time'] = microtime(true) - $start_time;
	return $response;
}

?>
