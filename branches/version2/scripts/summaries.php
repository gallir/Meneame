<?
include('../config.php');

header("Content-Type: text/plain");

if (!empty($_GET['month']) && !empty($_GET['year']) && ($month = (int) $_GET['month']) > 0 && ($year = (int) $_GET['year'])) {
	if ($month < 12) {
		$maxmonth = $month+1;
		$maxyear = $year;
	} else {
		$maxmonth = 1;
		$maxyear = $year+1;
	}
	$maxday=1;
	$maxhour=0;
	$maxmin=0;
	$maxsec=0;
} else {
	$h = localtime(time()-86400*120, TRUE);
	$month=$h["tm_mon"] + 1; $maxmonth=$month;
	$year=$h["tm_year"] + 1900; $maxyear=$year;
	$maxday=$h["tm_mday"];
	$maxhour=$h["tm_hour"];
	$maxmin=$h["tm_min"];
	$maxsec=$h["tm_sec"];
}

if ($month > 1) {
	$previous_year = $year;
	$previous_month = $month-1;
} else {
	$previous_year = $year-1;
	$previous_month = 12;
}

$absolute_previous_maxid = 0;
$absolute_maxid = 0;
$maxdate = sprintf("%04d%02d%02d%02d%02d%02d", $maxyear, $maxmonth, $maxday, $maxhour, $maxmin, $maxsec);

print "Month: $month, Year: $year ($maxdate)\n";
flush();


// Votes summary
$types = $db->get_col("select distinct(vote_type) from votes");
foreach ($types as $type) {
	$previous_count = (int) $db->get_var("select votes_count from votes_summary where votes_year = $year and votes_month=$month and votes_type='$type'");
	$previous_maxid = (int) $db->get_var("select votes_maxid from votes_summary where votes_year = $year and votes_month=$month and votes_type='$type'");
	$last_month_maxid = (int) $db->get_var("select votes_maxid from votes_summary where votes_year = $previous_year and votes_month=$previous_month and votes_type='$type'");

	echo "Type: $type $previous_count $previous_maxid\n";
	flush();

	$count = (int) $db->get_var("select count(*) from votes where vote_type='$type' and vote_date < '$maxdate' and vote_id > $previous_maxid");
	$maxid = (int) $db->get_var("select vote_id from votes where vote_type='$type' and vote_date < '$maxdate' and vote_id > $previous_maxid order by vote_id desc limit 1");
	$total_count = $previous_count + $count;
	if ($count > 0 && $maxid > 0) {
		echo "Maxid/count: $maxid, $count\n";
		$db->query("REPLACE INTO votes_summary (votes_year, votes_month, votes_type, votes_maxid, votes_count) VALUES ($year, $month, '$type', $maxid, $total_count)");
		//$db->query("delete LOW_PRIORITY from votes where vote_type='$type' and vote_date < '$maxdate' and vote_id <= $maxid");
		$absolute_maxid = max($absolute_maxid, $maxid);
		if ($previous_maxid > 0 || $last_month_maxid > 0) {
			if ($absolute_previous_maxid == 0) $absolute_previous_maxid = max($previous_maxid, $last_month_maxid);
			else $absolute_previous_maxid = min(max($previous_maxid, $last_month_maxid), $absolute_previous_maxid);
			echo "Previous max id: $previous_maxid, $last_month_maxid -> $absolute_previous_maxid\n";
		}

	}
	flush();
}

if (!$absolute_maxid > 0) {
	echo "Nothing to delete, bye\n";
	die;
}
echo "Have to delete up to $absolute_maxid\n";
flush();
if ($absolute_previous_maxid > 0) {
	for ($i = $absolute_previous_maxid + 5000; $i < $absolute_maxid; $i = $i+=5000) {
		echo "Deleting up to $i\n";
		flush();
		$db->query("delete LOW_PRIORITY from votes where vote_id <= $i");
		usleep(1000);
	}
}
$db->query("delete LOW_PRIORITY from votes where vote_id <= $absolute_maxid");

?>
