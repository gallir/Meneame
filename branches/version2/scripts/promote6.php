<?
include('../config.php');
include(mnminclude.'link.php');
include_once(mnminclude.'log.php');

header("Content-Type: text/html");
echo '<html><head><title>promote6.php</title></head><body>';
ob_end_flush();
?>
<style type="text/css">
body {
	font-family: Bitstream Vera Sans, Arial, Helvetica, sans-serif;
	font-size: 80%;
	margin: 0px;
	padding: 20px;
}
table {
	width: 90%;
	font-size: 110%;
	margin: 0px;
	padding: 4px;
}
td {
	margin: 0px;
	padding: 4px;
}
.thead {
	font-size: 115%;
	text-transform: uppercase;
	color: #FFFFFF;
	background-color: #FF6600;
	padding: 6px;
}
.tdata0 {
	background-color: #FFF;
}
.tdata1 {
	background-color: #FFF3E8;
}
.tnumber0 {
	text-align: center;
}
.tnumber1 {
	text-align: center;
	background-color: #FFF3E8;
}
</style>
<?

$min_karma_coef = 0.85;
define(MAX, 1.20);
define (MIN, 1.0);
define (PUB_MIN, 25);
define (PUB_MAX, 55);


$links_queue = $db->get_var("SELECT SQL_NO_CACHE count(*) from links WHERE link_date > date_sub(now(), interval 24 hour) and link_status !='discard'");
$links_queue_all = $db->get_var("SELECT SQL_NO_CACHE count(*) from links WHERE link_date > date_sub(now(), interval 24 hour) and link_votes > 0");

$pub_estimation = intval(max(min($links_queue * 0.18, PUB_MAX), PUB_MIN));
$interval = intval(86400 / $pub_estimation);

$now = time();
echo "<p><b>BEGIN</b>: ".get_date_time($now)."<br>\n";

$from_time = "date_sub(now(), interval 4 day)";
#$from_where = "FROM votes, links WHERE  


$last_published = $db->get_var("SELECT SQL_NO_CACHE UNIX_TIMESTAMP(max(link_published_date)) from links WHERE link_status='published'");
if (!$last_published) $last_published = $now - 24*3600*30;

$diff = $now - $last_published;

$decay = min(MAX, MAX - ($diff/$interval)*(MAX-MIN) );
$decay = max($min_karma_coef, $decay);
print "Last published at: " . get_date_time($last_published) ."<br>\n";
echo "24hs queue: $links_queue/$links_queue_all, Published goal: $pub_estimation, Interval: $interval secs, Decay: $decay<br>\n";

$continue = true;
$published=0;

$past_karma_long = intval($db->get_var("SELECT SQL_NO_CACHE avg(link_karma) from links WHERE link_published_date >= date_sub(now(), interval 7 day) and link_status='published'"));
$past_karma_short = intval($past_karma = $db->get_var("SELECT SQL_NO_CACHE avg(link_karma) from links WHERE link_published_date >= date_sub(now(), interval 8 hour) and link_status='published'"));

$past_karma = 0.5 * max(40, $past_karma_long) + 0.5 * max($past_karma_long*0.8, $past_karma_short);

echo "Past karma. Long term: $past_karma_long, Short term: $past_karma_short, Average: <b>$past_karma</b><br>\n";
//////////////
$min_karma = round(max($past_karma * $decay, 20));

if ($decay >= 1) $max_to_publish = 3;
else $max_to_publish = 1;

$min_votes = 5;
/////////////

echo "Current MIN karma: <b>$min_karma</b>    MIN votes: $min_votes<br></p>\n";
$limit_karma = round(min($past_karma,$min_karma) * 0.70);
$where = "link_date > $from_time AND link_status = 'queued' AND link_votes>=$min_votes  AND (link_karma > $limit_karma or (link_date > date_sub(now(), interval 2 hour) and link_karma > $limit_karma/2)) and user_id = link_author";
$sort = "ORDER BY link_karma DESC, link_votes DESC";

$links = $db->get_results("SELECT SQL_NO_CACHE link_id, link_karma as karma from links, users where $where $sort LIMIT 20");
$rows = $db->num_rows;
if (!$rows) {
	echo "There are no articles<br>\n";
	echo "--------------------------<br>\n";
	die;
}
	
$max_karma_found = 0;
$best_link = 0;
$best_karma = 0;
echo "<table>\n";	
if ($links) {
	print "<tr class='thead'><th>id</th><th>votes</th><th>neg.</th><th>bonus</th><th>karma</th><th>title</th><th>changes</th></tr>\n";
	$i=0;
	foreach($links as $dblink) {
		$link = new Link;
		$link->id=$dblink->link_id;
		$link->read();
		$karma_pos_user = 0;
		$karma_neg_user = 0;
		$karma_pos_ano = 0;
		$karma_neg_ano = 0;

		// Count number of votes
		$votes_pos = intval($db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' AND vote_link_id=$link->id and vote_value > 0"));
		$votes_neg = intval($db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' AND vote_link_id=$link->id and vote_value < 0"));

		// Calculate the real karma for the link
		$karma_pos_user = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' and vote_date > $from_time AND vote_link_id=$link->id and vote_user_id > 0 and vote_value > 0"));
		$karma_neg_user = intval($db->get_var("select SQL_NO_CACHE sum(vote_value-user_karma/2) from votes, users where vote_type='links' and vote_date > $from_time AND vote_link_id=$link->id and vote_user_id > 0 and vote_value < 0 and user_id=vote_user_id"));

		$karma_pos_ano = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' and vote_date > $from_time AND vote_link_id=$link->id and vote_user_id = 0 and vote_value > 0"));
		$karma_neg_ano = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' and vote_date > $from_time AND vote_link_id=$link->id and vote_user_id = 0 and vote_value < 0"));

		$karma_new = $karma_pos_user + $karma_neg_user;
		// To void votes spamming
		// Do not allow annonimous users to give more karma than registered users
		// The ratio up to 40% anonymous
		if ($karma_new > 0) 
			$karma_new += min($karma_new*0.4, $karma_pos_ano + $karma_neg_ano);


		// Aged karma
		$diff = max(0, $now - ($link->date + 18*3600)); // 1 hour without decreasing
		$oldd = 1 - $diff/(3600*144);
		$oldd = max(0.5, $oldd);
		$oldd = min(1, $oldd);

		// BONUS
		// Give more karma to news voted very fast during the first two hours (ish)
		if ($now - $link->date < 7200 && $now - $link->date > 900) {
			$new_coef = 2 - ($now-$link->date)/7200;
			// if it's has bonus and therefore time-related, use the base min_karma
			if ($decay > 1) 
				$karma_threshold = $past_karma;
			else
				$karma_threshold = $min_karma;

		} else {
			// Otherwise use normal decayed min_karma
			$karma_threshold = $min_karma;
			$new_coef = 1;
		}

		$aged_karma =  $karma_new * $oldd * $new_coef;
		$dblink->karma=$aged_karma;

		$imod = $i%2;
		$changes = 0;
		if (abs($link->karma - $dblink->karma) > 4 ||
			$link->votes != $votes_pos || $link->negatives != $votes_neg ) {
			$karma_mess = sprintf ("<br>updated karma: %6d (%d, %d) -> %-6d (%d, %d)\n", $link->karma, $link->votes, $link->negatives, round($dblink->karma), $votes_pos, $votes_neg);
			if ($link->karma > $dblink->karma) 
				$changes = 1; // to show a "decrease" later	
			else $changes = 2; // increase
			$link->karma = round($dblink->karma);
			$link->votes = $votes_pos;
			$link->negatives = $votes_neg;
			$link->store_basic();
		} else $karma_mess = '';
		print "<tr><td class='tnumber$imod'>$link->id</td><td class='tnumber$imod'>".$link->votes."</td><td class='tnumber$imod'>".$link->negatives."</td><td class='tnumber$imod'>" . sprintf("%0.2f", $new_coef). "</td><td class='tnumber$imod'>".intval($link->karma)."</td>";
		echo "<td class='tdata$imod'><a href='".$link->get_permalink()."'>$link->title</a>\n";
		echo "$karma_mess</td>\n";
			
		if ($link->user_level != 'disabled' && $link->votes >= $min_votes && $dblink->karma >= $karma_threshold && $published < $max_to_publish) {
			$published++;
			$link->karma = round($dblink->karma);
			$link->status = 'published';
			$link->published_date=time();
			$link->store_basic();
			// Add the publish event/log
			log_insert('link_publish', $link->id, $link->author);
			$changes = 3; // to show a "published" later	
		}
		echo "<td class='tnumber$imod'>";
		switch ($changes) {
			case 1:
				echo '<img src="../img/common/sneak-problem01.png" width="20" height="16" alt="'. _('descenso') .'"/>';
				break;
			case 2:
				echo '<img src="../img/common/sneak-vote01.png" width="20" height="16" alt="'. _('ascenso') .'"/>';
				break;
			case 3:
				echo '<img src="../img/common/sneak-published01.png" width="20" height="16" alt="'. _('publicada') .'"/>';
				break;
		}
		echo "</td>";
		echo "</tr>\n";
		$i++;
	}
	print "</table>\n";
	//////////
}  
echo "</body></html>\n";
?>
