<?
include('../config.php');
include(mnminclude.'link.php');

header("Content-Type: text/html");
echo "<html><head><title>promote2.php</title></head><body>";
ob_end_flush();

define(MAX, 1.20);
define (MIN, 1.0);

$now = time();
echo "<b>BEGIN</b>: ".get_date_time($now)."<br>\n";
if(!empty($_GET['period']))
	$period = intval($_GET['period']);
else $period = 200;

echo "Period (h): $period<br>\n";

$from_time = $now - $period*2700;
#$from_where = "FROM votes, links WHERE  


$last_published = $db->get_var("SELECT SQL_NO_CACHE UNIX_TIMESTAMP(max(link_published_date)) from links WHERE link_status='published'");
if (!$last_published) $last_published = $now - 24*3600*30;
//$history_from = $last_published - $period*3600;
$history_from = $last_published - 200*3600;

$diff = $now - $last_published;

$d = min(MAX, MAX - ($diff/3000)*(MAX-MIN) );
$d = max(0.80, $d);
print "Last published at: " . get_date_time($last_published) ."<br>\n";
echo "Decay: $d<br>\n";

$continue = true;
$i=0;

$past_karma = $db->get_var("SELECT SQL_NO_CACHE avg(link_karma) from links WHERE link_published_date > FROM_UNIXTIME($history_from) and link_status='published'");
//$past_karma = $db->get_var("SELECT avg(link_karma) from links WHERE link_status='published' ORDER BY link_published_date DESC LIMIT 30");
echo "Past karma: $past_karma<br>\n";
while ($continue) {
	$continue = false;
//////////////
	$min_karma = round(max($past_karma * $d, 20));
	$min_votes = 5;
/////////////
	
	echo "Current MIN karma: <b>$min_karma</b>    MIN votes: $min_votes<br>\n";
	$where = "vote_type='links' and vote_date > FROM_UNIXTIME($from_time) AND vote_link_id=link_id AND link_status = 'queued' AND link_votes>=$min_votes and user_id = link_author and user_level != 'disabled'";
	$group =  "GROUP BY vote_link_id";
	$sort = "ORDER BY karma DESC";


	//$votes = $db->get_var("SELECT count(*) from links, votes where $where");
	//$karma_total = $db->get_var("SELECT sum(vote_value) from links, votes where $where");

	$links = $db->get_results("SELECT SQL_NO_CACHE link_id, sum(vote_value) as karma, count(*) as votes from links, votes, users where $where $group $sort LIMIT 30");
	$rows = $db->num_rows;
	if (!$rows) {
		echo "There is no articles<br>\n";
		echo "--------------------------<br>\n";
		die;
	}
	
	//$karma_avg = $karma_total / $rows;
	//echo "Votes: $votes Karma_total: $karma_total Media: $karma_avg\n";

	$max_karma_found = 0;
	$best_link = 0;
	$best_karma = 0;
	echo "<table cellpadding=5><br>\n";	
	if ($links) {
//		$dblink = current($links);
		print "<tr><th>id</th><th>votes</th><th>karma</th><th>title</th></tr>\n";
		foreach($links as $dblink) {
			$link = new Link;
			$link->id=$dblink->link_id;
			$link->read();
			//$karma = $dblink->karma/sqrt($period);
			$karma_pos_user = 0;
			$karma_neg_user = 0;
			$karma_pos_ano = 0;
			$karma_neg_ano = 0;

			// Count number of votes
			$votes_pos_user = $db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' and vote_date > FROM_UNIXTIME($from_time) AND vote_link_id=$link->id and vote_user_id > 0 and vote_value > 0");
			$votes_pos_ano = $db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' and vote_date > FROM_UNIXTIME($from_time) AND vote_link_id=$link->id and vote_user_id = 0 and vote_value > 0");

			// Calculate the real karma for the link
			$karma_pos_user = $db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' and vote_date > FROM_UNIXTIME($from_time) AND vote_link_id=$link->id and vote_user_id > 0 and vote_value > 0");
			$karma_neg_user = $db->get_var("select SQL_NO_CACHE sum(vote_value-user_karma/2) from votes, users where vote_type='links' and vote_date > FROM_UNIXTIME($from_time) AND vote_link_id=$link->id and vote_user_id > 0 and vote_value < 0 and user_id=vote_user_id");

			$karma_pos_ano = $db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' and vote_date > FROM_UNIXTIME($from_time) AND vote_link_id=$link->id and vote_user_id = 0 and vote_value > 0");
			$karma_neg_ano = $db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' and vote_date > FROM_UNIXTIME($from_time) AND vote_link_id=$link->id and vote_user_id = 0 and vote_value < 0");

			$karma_new = $karma_pos_user + $karma_neg_user;
			// To void votes spamming
			// Do not allow annonimous users to give more karma than registered users
			if ($karma_new > 0) 
				$karma_new += min($karma_new, $karma_pos_ano + $karma_neg_ano);
			/*
			// Only takes in account if there are more karma from registered users
			if($karma_new > $karma_pos_ano) {
				$karma_new += $karma_pos_ano + $karma_neg_ano;
			}*/

			




			// Aged karma
			$diff = max(0, $now - ($link->date + 18*3600)); // 1 hour without decreasing
			$oldd = 1 - $diff/(3600*144);
			$oldd = max(0.5, $oldd);
			$oldd = min(1, $oldd);
			//echo "Oldness: $oldd ($diff)\n";
			//$aged_karma = $dblink->karma * $oldd;
			$aged_karma =  $karma_new * $oldd;
			$dblink->karma=$aged_karma;

			$max_karma_found = max($max_karma_found, $dblink->karma);
			print "<tr><td>$link->id</td><td>$dblink->votes</td><td>".intval($dblink->karma)."</td><td><a href='".$link->get_permalink()."'>$link->title</a>\n";
			//print "Karmas ($dblink->karma | $karma_new): pos_user: $karma_pos_user pos_anon: $karma_pos_ano neg_user: $karma_neg_user neg_anon: $karma_neg_ano\n";
			if (intval($link->karma) != intval($dblink->karma)) {
				printf ("<br>storing updated karma: %6d -> %-6d\n", $link->karma, $dblink->karma);
				$link->karma = $dblink->karma;
				$link->store();
			}
			//echo "$link->id:  $dblink->votes, $dblink->karma, '" . $link->title; echo "'\n";
			if ($max_karma_found == $dblink->karma)	{
				$best_title = $link->title;
				$best_url = $link->get_permalink();
			}
			
			if ($dblink->votes >= $min_votes && $dblink->karma >= $min_karma &&
				$dblink->karma > ($max_karma_found - 0.1) ) {
				$best_link = $link->id;
				$best_karma = $dblink->karma;
				$best_title = $link->title;
				$best_url = $link->get_permalink();
				echo "<br><b>Best found</b>\n";
			}
			print "</td></tr>\n";
		}
		print "</table>\n";

		//////////
		echo "<br>\n";
		echo "<b>Current best karma:</b> ".intval($max_karma_found) . " <a href='$best_url'>$best_title</a>\n";
		if ($best_link > 0) {
			$i++;
			$link->id = $best_link;
			$link->read();
			$link->karma=$best_karma;
			$link->status='published';
			$link->published_date=time();
			echo "<h4>Published: <a href='".$link->get_permalink()."'>$link->title</a>, ".intval($link->karma)."</h4>\n";
			$link->store();
			//echo "$i Published: $link->title \n";
			//echo "$i -------------\n";
			if ($i < 3 && $d > 1.01) $continue = true;
		} 
		/****else {
			$future = $past_karma * 3600 / $blink->karma;
			echo "Estimated: " . get_date_time(time()+$future) ."\n";
		}
		**********/
	}  
	//echo "--------------------------\n";
}
	echo "</body></html>\n";
?>
