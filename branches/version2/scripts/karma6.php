<?
include('../config.php');
include(mnminclude.'user.php');

header("Content-Type: text/plain");

// Delete old logs
$db->query("delete from logs where log_type in ('comment_new','login_failed') and log_date < date_sub(now(), interval 24 hour)");
$db->query("delete from logs where log_date < date_sub(now(), interval 15 day)");

// Delete not validated users
$db->query("delete from users where user_date < date_sub(now(), interval 24 hour) and user_validated_date is null");

// Delete email, names and url of invalidated users after three months
$dbusers = $db->get_col("select user_id from users where user_email not like '%@disabled' && user_level = 'disabled' and user_modification < date_sub(now(), interval 3 month)");
if ($dbusers) {
	foreach ($dbusers as $id) {
		$user = new User;
		$user->id = $id;
		$user->read();
		if ($user->level == 'disabled') { // Double check
			$user->disable();
			echo "Disabling: $id - $user->username\n";
		}
	}
}


$karma_base=6;
$karma_base_max=8; // Older users can get up to this value
$min_karma=1;
$max_karma=20;
$now = "'".$db->get_var("select now()")."'";
$history_from = "date_sub($now, interval 36 hour)";
$ignored_nonpublished = "date_sub($now, interval 12 hour)";
$points_received = 16;
$points_given = 12;
$comment_votes = 5;

// Following lines are for negative points given to links
// It takes in account just votes during 24 hours
$points_discarded = 0.10;
$discarded_history_from = "date_sub($now, interval 24 hour)";
$ignored_nondiscarded = "date_sub($now, interval 6 hour)";

// The formula to calculate the decreasing vote points
$sql_points_calc = 'sum((unix_timestamp(link_published_date) - unix_timestamp(vote_date))/(unix_timestamp(link_published_date) - unix_timestamp(link_date))) as points';
// Define the period until vote to published links are considered for karma
//$sql_points_date_ignored = 'date_sub(link_published_date, INTERVAL 30 minute)';
$sql_points_date_ignored = 'link_published_date';



$sum=0; $i=0;
// It does an average of the top 10 voted

$published_links = intval($db->get_var("SELECT SQL_NO_CACHE count(*) from links where link_status = 'published' and link_published_date > $history_from"));

foreach ($db->get_col("SELECT SQL_NO_CACHE sum(vote_value) as votes from links, votes  where vote_type='links' and  vote_date > $history_from and vote_user_id > 0 and vote_value>0 and vote_link_id = link_id group by link_author order by votes desc limit 10") as $positives) {
	$sum += $positives; $i++;
}
$max_positive_received = $sum/$i;
$max_positive_received = intval($max_positive_received * 0.75 );
if($max_positive_received == 0) $max_positive_received = 1;

/*
$sum=0;
foreach ($db->get_col("SELECT SQL_NO_CACHE sum(user_karma) as votes from links, votes, users  where vote_type='links' and  vote_date > $history_from and vote_user_id > 0 and vote_value>0 and vote_link_id = link_id and vote_user_id=user_id group by link_author order by votes desc limit 10") as $negatives) {
	$sum += $negatives; $i++;
}
$max_negative_received = $sum/$i;
$max_negative_received = intval($max_negative_received * 0.75 );
if($max_negative_received == 0) $max_negative_received = 1;
*/

$max_negative_received = $max_positive_received * 1.5;



$max_published_given = (int) $db->get_var("SELECT  SQL_NO_CACHE $sql_points_calc from links, votes, users  where vote_type='links' and  vote_date > $history_from and vote_user_id > 0 and vote_value>0 and vote_link_id = link_id and link_status='published' and link_author != vote_user_id and vote_date < $sql_points_date_ignored and user_id = vote_user_id and user_karma > 8 group by vote_user_id order by points desc limit 1");

//$max_published_given = intval($max_published_given * 0.75 );
if($max_published_given <= 0) $max_published_given = max(1, $published_links/3);

$max_nopublished_given = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) as votes from links, votes  where vote_type='links' and  vote_date > $history_from and vote_date < $ignored_nonpublished and vote_user_id > 0 and vote_value>0 and vote_link_id = link_id and link_status!='published' and link_author != vote_user_id group by vote_user_id order by votes desc limit 1");

// "Unfair" negative votes max
$max_negative_comment_votes = (int) $db->get_var("select SQL_NO_CACHE count(*) as count from votes, comments where vote_type='comments' and vote_date > date_sub(now(), interval 30 hour) and vote_value < 0 and comment_id = vote_link_id and ((comment_karma-vote_value)/(comment_votes-1)) > 5 group by vote_user_id order by count desc limit 1");
$max_negative_comment_votes  = max($max_negative_comment_votes, 40);

print "Number of published links in period: $published_links\n";
print "Pos (top 10 average): $max_positive_received, Neg: $max_negative_received, Published: $max_published_given No: $max_nopublished_given\n";
print "Max unfair comment votes: $max_negative_comment_votes\n";


/////////////////////////



//$users = $db->get_results("SELECT SQL_NO_CACHE user_id from users where user_level != 'disabled'");
//$users = $db->get_results("select distinct vote_user_id as user_id from votes where vote_type in ('links', 'comments', 'posts') and vote_date > date_sub(now(), interval 15 day) and vote_user_id > 0; ");
echo "Starting...\n";
$no_calculated = 0;
$calculated = 0;

// We use mysql functions directly because  EZDB cannot hold all IDs in memory and the select faila miserably with abpout 40.000 users.

$users = "SELECT SQL_NO_CACHE user_id from users where user_level != 'disabled' order by user_id desc";
$result = mysql_query($users) or die('Query failed: ' . mysql_error());
while ($dbuser = mysql_fetch_object($result)) {
	$user = new User;
	$user->id=$dbuser->user_id;
	$user->read();

	//Base karma for the user
	$first_published = $db->get_var("select SQL_NO_CACHE UNIX_TIMESTAMP(min(link_date)) from links where link_author = $user->id and link_status='published';");
	if ($user->karma >= $karma_base && $first_published > 0) {
		$karma_base_user = min($karma_base_max, $karma_base + ($karma_base_max - $karma_base) * (time()-$first_published)/(86400*365));
	} else {
		$karma_base_user = $karma_base;
	}
	printf ("%07d ", $user->id); echo "$user->username Karma base: $karma_base_user\n";

	$n = $db->get_var("SELECT SQL_NO_CACHE count(*) FROM  votes  WHERE vote_type in ('links', 'comments') and vote_user_id = $user->id and vote_date > $history_from");
	$n_events = $db->get_var("select SQL_NO_CACHE count(*) from logs where log_date > $history_from and log_user_id=$user->id");
	if ($n > 3 || $n_events > 0) {
		printf ("%07d ", $user->id);
		print "events: votes: $n, logs: $n_events\n";

		// Count the numbers of link sent by the user in the last 30 days
		$sent_links = intval($db->get_var("select SQL_NO_CACHE count(*) from links where link_author = $user->id and link_date > date_sub(now(), interval 30 day) and link_status != 'discard' "));

		// Count the  comments of the users during the analised period
		$total_comments = intval($db->get_var("select SQL_NO_CACHE count(*) from comments where comment_user_id = $user->id and comment_date > $history_from"));

		$calculated++;
		$positive_votes_received=intval($db->get_var("SELECT SQL_NO_CACHE sum(vote_value) FROM links, votes WHERE link_author = $user->id and vote_type='links' and vote_link_id = link_id and vote_date > $history_from and vote_user_id > 0 and vote_value > 0"));
		$negative_votes_received=intval($db->get_var("SELECT SQL_NO_CACHE sum(user_karma) FROM links, votes, users WHERE link_author = $user->id and vote_type='links' and vote_link_id = link_id and vote_date > $history_from and vote_user_id > 0 and vote_value < 0 and user_id=vote_user_id"));

		$karma1 = max(min($points_received * (($positive_votes_received-$negative_votes_received*4)/$max_positive_received), $points_received), -$points_received);
		if ($karma1 != 0) {
			printf ("%07d ", $user->id);
			print "Votes received:  karma received: $positive_votes_received, negative: $negative_votes_received, karma1: $karma1\n";
		}

/////

		$user_votes = $db->get_row("SELECT SQL_NO_CACHE count(*) as count, $sql_points_calc FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > $history_from  and vote_value > 0 AND link_id = vote_link_id AND link_status = 'published' and vote_date < $sql_points_date_ignored and link_author != $user->id");
		$published_points = (int) $user_votes->points;
		$published_given = (int) $user_votes->count;
		if ($user_votes->points > 0) 
			$published_average = $published_points/$published_given;
		else 
			$published_average = 0;

		$nopublished_given = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > $history_from and vote_date < $ignored_nonpublished and vote_value > 0 AND link_id = vote_link_id AND link_status != 'published' and link_author != $user->id");

		$discarded_given = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > $discarded_history_from  and vote_value > 0 AND link_id = vote_link_id AND link_status = 'discard' and link_author != $user->id");

		$karma2 = min($points_given, $points_given * $published_points/$max_published_given - $points_given * ($nopublished_given/$max_nopublished_given)/10 - 0.1 * $discarded_given);

		// Limit karma to users that does not send any link
		// or "moderated" karma whores
		if ($sent_links == 0 || $published_given > $nopublished_given * 1.5) {
			$karma2 = min($karma2, $points_given * 0.3);
		}

		// Bot and karmawhoring warning!!!
		if ($karma2 > 0 && $published_links > 6 && $published_given > $published_links/3 && $published_average < 0.5 &&
			($published_given > $nopublished_given * 5 || ($published_given > $nopublished_given * 2 && $total_comments == 0 && $sent_links == 0) )) {
			$punishment = -(0.5 - $published_average)/0.5 * 5;
			printf ("%07d ", $user->id);
			print "$user->username bot or karmawhore suspected, karma2 = $karma2 -> $punishment\n";
			$karma2 = $punishment;
		}

		if ($karma2 != 0) {
			printf ("%07d ", $user->id);
			print "Votes to links: votes to published: $published_given, to non published: $nopublished_given to discarded: $discarded_given\n";
			printf ("%07d ", $user->id);
			print "                points to published: $published_points, point average: $published_average, karma2: $karma2\n";
		}


		$negative_discarded = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > $discarded_history_from  and vote_value < 0 AND link_id = vote_link_id AND link_status = 'discard' and TIMESTAMPDIFF(MINUTE, link_date, vote_date) < 15 ");

		$negative_no_discarded = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > $discarded_history_from and vote_date < $ignored_nondiscarded and vote_value < 0 AND link_id = vote_link_id AND link_status != 'discard' and link_negatives < link_votes/10");

		if ($negative_no_discarded > $negative_discarded/4) { // To fight against karma whores and bots
			$karma3 = $points_discarded * ($negative_discarded - $negative_no_discarded);
		} else {
			$karma3 = 0;
		}
		if ($karma3 != 0) {
			printf ("%07d ", $user->id);
			print "Negative votes: negatives to discarded: $negative_discarded, no discarded: $negative_no_discarded, karma3: $karma3\n";
		}

		// Check the user don't abuse voting only negative
		$max_allowed_negatives = round(($nopublished_given + $published_given + $negative_discarded) * $user->karma / 10);
		if($negative_no_discarded > 2 && $negative_no_discarded > $max_allowed_negatives) {
			$punishment = min(1 + $negative_no_discarded/$max_allowed_negatives, 6);
			printf ("%07d ", $user->id);
			$karma3 -= $punishment;
			print "$user->username Unfair negative votes to non discarded ($negative_no_discarded > $max_allowed_negatives), punishment: $punishment, karma3 = $karma3\n";
		}

		$comment_votes_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) from votes, comments where comment_user_id = $user->id and comment_date > $history_from and vote_type='comments' and vote_link_id = comment_id and  vote_date > $history_from and vote_user_id != $user->id");
		if ($comment_votes_count > 10)  {
			$comment_votes_sum = (int) $db->get_var("SELECT SQL_NO_CACHE sum(vote_value) from votes, comments where comment_user_id = $user->id and comment_date > $history_from and vote_type='comments' and vote_link_id = comment_id and vote_date > $history_from and vote_user_id != $user->id");
			$karma4 = max(-$comment_votes, min($comment_votes_sum / ($comment_votes_count*10) * $comment_votes, $comment_votes));
		} else {
			$karma4 = 0;
			$comment_votes_sum = 0;
		}

		// Limit karma to users that does not send any link
		if ($sent_links == 0 &&  $karma4 > 0 ) $karma4 = $karma4 * 0.6;
		if ($karma4 != 0) {
			printf ("%07d ", $user->id);
			print "Comment votes received: votes: $comment_votes_count, votes karma: $comment_votes_sum, karma4: $karma4\n";	
		}

		// Penalize to unfair negative comments' votes
		$karma5 = 0;
		$negative_abused_comment_votes_count = (int) $db->get_var("select SQL_NO_CACHE count(*) from votes, comments where vote_type='comments' and vote_user_id = $user->id and vote_date > $history_from and vote_value < 0 and comment_id = vote_link_id and ((comment_karma-vote_value)/(comment_votes-1)) >= 6");
		if ($negative_abused_comment_votes_count > 3) {
			$karma5 = max(-$comment_votes, -$comment_votes * 2 * $negative_abused_comment_votes_count / $max_negative_comment_votes);
		}
		if ($karma5 != 0) {
			printf ("%07d ", $user->id);
			print "$user->username Unfair negative comments votes: $negative_abused_comment_votes_count, karma5: $karma5\n";	
		}

	
		$karma = max($karma_base_user+$karma1+$karma2+$karma3+$karma4+$karma5, $min_karma);
		$karma = min($karma, $max_karma);
	} else {
		$no_calculated++;
		if ($user->karma > 7) {
			$karma = max($karma_base_user, $user->karma - 0.2);
		} elseif ($user->karma < $karma_base) {
			$karma = min($karma_base, $user->karma + 0.1);
		} else {
			$karma = $user->karma;
		}
	}

	if ($user->karma == $karma) {
		printf ("%07d ", $user->id);
		print "$user->username: Karma not modified $karma ($user->level)\n";
	} else {
		if ($user->karma > $karma) {
			// Decrease slowly
			$user->karma = 0.9*$user->karma + 0.1*$karma;
			printf ("%07d ", $user->id);
			print "Final karma: average: $user->karma,  calculated karma: $karma, decreasing (status: $user->level)\n";
		} else {
			// Increase faster
			$user->karma = 0.8*$user->karma + 0.2*$karma;
			printf ("%07d ", $user->id);
			print "Final karma: average: $user->karma,  calculated karma: $karma, increasing (status: $user->level)\n";
		}
		if ($user->karma > $max_karma * 0.8 && $user->level == 'normal') {
			$user->level = 'special';
		} else {
			if ($user->level == 'special' && $user->karma < $max_karma * 0.7) {
				$user->level = 'normal';
			}
		}
		$user->store();
		usleep(5000); // wait 1/200 seconds
	}
}
mysql_free_result($result);
echo "Calculated: $calculated, Ignored: $no_calculated\n";
?>
