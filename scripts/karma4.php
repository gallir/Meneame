<?
include('../config.php');
include(mnminclude.'user.php');

header("Content-Type: text/plain");

// Delete not validated users
$db->query("delete from users where user_date < date_sub(now(), interval 24 hour) and user_validated_date is null");

// Lower karma of disabled users
$db->query("update users set user_karma=6 where user_date < date_sub(now(), interval 24 hour) and user_level='disabled' and user_karma>6");


$karma_base=6;
$min_karma=1;
$max_karma=20;
$history_from = time() - 3600*24*2;
$ignored_nonpublished = time() - 3600*24;
$points_received = 20;
$points_given = 15;
$comment_votes = 5;

// Following lines are for negative points given to links
// It takes in account just votes during 24 hours
$points_discarded = 0.10;
$discarded_history_from = time() - 30 * 3600;
$ignored_nondiscarded = time() - 6 * 3600;

$sum=0; $i=0;
// It does an average of the top 10 voted
foreach ($db->get_col("SELECT SQL_NO_CACHE count(*) as votes from links, votes  where vote_type='links' and  vote_date > FROM_UNIXTIME($history_from) and vote_user_id > 0 and vote_value>0 and vote_link_id = link_id group by link_author order by votes desc limit 10") as $positives) {
	$sum += $positives; $i++;
}

$max_positive_received = $sum/$i;

$max_positive_received = intval($max_positive_received * 0.75 );
if($max_positive_received == 0) $max_positive_received = 1;

//$max_negative_received = $db->get_var("SELECT count(*) as votes from links, votes  where vote_date > FROM_UNIXTIME($history_from) and vote_value<0 and vote_link_id = link_id group by link_author order by votes desc limit 1");

$max_published_given = $db->get_var("SELECT SQL_NO_CACHE count(*) as votes from links, votes  where vote_type='links' and  vote_date > FROM_UNIXTIME($history_from) and vote_user_id > 0 and vote_value>0 and vote_link_id = link_id and link_status='published' and vote_date < date_sub(link_published_date, INTERVAL 30 minute) group by vote_user_id order by votes desc limit 1");
//$max_published_given = intval($max_published_given * 0.75 );
if($max_published_given <= 0) $max_published_given = 1;

$max_nopublished_given = $db->get_var("SELECT SQL_NO_CACHE count(*) as votes from links, votes  where vote_type='links' and  vote_date > FROM_UNIXTIME($history_from) and vote_date < FROM_UNIXTIME($ignored_nonpublished) and vote_user_id > 0 and vote_value>0 and vote_link_id = link_id and link_status!='published' group by vote_user_id order by votes desc limit 1");


print "Pos (top 10 average): $max_positive_received, Neg: $max_negative_received, Published: $max_published_given No: $max_nopublished_given\n";



/////////////////////////



$users = $db->get_results("SELECT SQL_NO_CACHE user_id from users where user_level != 'disabled' order by user_login");
$no_calculated = 0;
$calculated = 0;
foreach($users as $dbuser) {
	$user = new User;
	$user->id=$dbuser->user_id;
	$user->read();

	$n = $db->get_var("SELECT SQL_NO_CACHE count(*) FROM  votes  WHERE (vote_type='links' or vote_type='comments') and vote_user_id = $user->id and vote_date > FROM_UNIXTIME($history_from)");
	$n_events = $db->get_var("select SQL_NO_CACHE count(*) from logs where log_date > FROM_UNIXTIME($history_from) and log_user_id=$user->id");
	if ($n > 3 || $n_events > 0) {
		$calculated++;
		$positive_votes_received=$db->get_var("SELECT SQL_NO_CACHE count(*) FROM links, votes WHERE link_author = $user->id and vote_type='links' and vote_link_id = link_id and vote_date > FROM_UNIXTIME($history_from) and vote_user_id > 0 and vote_value > 0");
		$negative_votes_received=$db->get_var("SELECT SQL_NO_CACHE count(*) FROM links, votes WHERE link_author = $user->id and vote_type='links' and vote_link_id = link_id and vote_date > FROM_UNIXTIME($history_from) and vote_user_id > 0 and vote_value < 0");

		$karma1 = max(min($points_received * ($positive_votes_received/$max_positive_received) - $points_received * ($negative_votes_received/$max_positive_received) * 3, $points_received), -$points_received);
		print "$user->username (votes received) ($positive_votes_received, $negative_votes_received): $karma1\n";

/////

		$published_given = $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > FROM_UNIXTIME($history_from)  and vote_value > 0 AND link_id = vote_link_id AND link_status = 'published' AND vote_date < date_sub(link_published_date, interval 30 minute)");
		$nopublished_given = $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > FROM_UNIXTIME($history_from) and vote_date < FROM_UNIXTIME($ignored_nonpublished)  and vote_value > 0 AND link_id = vote_link_id AND link_status != 'published'");

		$karma_per_vote = $published_given/$max_published_given;
	
		$karma2 = min($points_given, $points_given * $karma_per_vote - $points_given * ($nopublished_given/$max_nopublished_given) / 4);
		print "$user->username (votes to links) ($published_given, $nopublished_given): $karma2\n";


		$negative_discarded = $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > FROM_UNIXTIME($discarded_history_from)  and vote_value < 0 AND link_id = vote_link_id AND link_status = 'discard' and TIMESTAMPDIFF(MINUTE, link_date, vote_date) < 25 ");
		// TIMESTAMPDIFF(MINUTE, link_date, vote_date) < 60: only early votes

		$negative_no_discarded = $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > FROM_UNIXTIME($discarded_history_from) and vote_date < FROM_UNIXTIME($ignored_nondiscarded) and vote_value < 0 AND link_id = vote_link_id AND link_status != 'discard'");

		$karma3 = $points_discarded * ($negative_discarded - $negative_no_discarded);
		print "$user->username (negative votes) ($negative_discarded, $negative_no_discarded): $karma3\n";



		$comment_votes_count = $db->get_var("SELECT SQL_NO_CACHE count(*) from votes, comments where comment_user_id = $user->id and comment_date > FROM_UNIXTIME($history_from) and vote_type='comments' and vote_link_id = comment_id and  vote_date > FROM_UNIXTIME($history_from)");
		if ($comment_votes_count > 3)  {
			$comment_votes_sum = $db->get_var("SELECT SQL_NO_CACHE sum(vote_value) from votes, comments where comment_user_id = $user->id and comment_date > FROM_UNIXTIME($history_from) and vote_type='comments' and vote_link_id = comment_id and vote_date > FROM_UNIXTIME($history_from)");
			$karma4 = max(-$comment_votes, min($comment_votes_sum / ($comment_votes_count*10) * $comment_votes, $comment_votes));
			print "$user->username (comment votes) ($comment_votes_count, $comment_votes_sum): $karma4\n";	
		} else $karma4 = 0;

	
		$karma = max($karma_base+$karma1+$karma2+$karma3+$karma4, $min_karma);
		$karma = min($karma, $max_karma);
	} else {
		$no_calculated++;
		if ($user->karma > 7) {
			$karma = max($karma_base, $user->karma - 0.2);
		} elseif ($user->karma < 5.9) {
			$karma = min($karma_base, $user->karma + 0.1);
		} else {
			$karma = $user->karma;
		}
	}

	if ($user->karma == $karma) {
		echo $user->username . " (karma): $user->karma == $karma ($user->level)\n";
	} else {
		if ($user->karma > $karma) {
			// Decrease slowly
			$user->karma = 0.9*$user->karma + 0.1*$karma;
			echo $user->username . " (karma): $user->karma << $karma ($user->level)\n";
		} else {
			// Increase faster
			$user->karma = 0.8*$user->karma + 0.2*$karma;
			echo $user->username . " (karma): $user->karma >> $karma ($user->level)\n";
		}
		if ($user->karma > $max_karma * 0.8 && $user->level == 'normal') {
			$user->level = 'special';
		} else {
			if ($user->level == 'special' && $user->karma < $max_karma * 0.7) {
				$user->level = 'normal';
			}
		}
		$user->store();
	}
}
echo "Calculated: $calculated, Ignored: $no_calculated\n";
?>
