<?
include('../config.php');
include(mnminclude.'user.php');
include(mnminclude.'annotation.php');

header("Content-Type: text/plain");

// Delete old logs
$db->query("delete from logs where log_type in ('comment_new','login_failed') and log_date < date_sub(now(), interval 24 hour)");
$db->query("delete from logs where log_date < date_sub(now(), interval 60 day)");

// Delete not validated users
$db->query("delete from users where user_date < date_sub(now(), interval 24 hour) and user_date > date_sub(now(), interval 1 week) and user_validated_date is null");

// Delete old bad links
$minutes = intval($globals['draft_time'] / 60);
$db->query("delete from links where link_status='discard' and link_date < date_sub(now(), interval $minutes minute) and link_date  > date_sub(now(), interval 1 week)and link_votes = 0");

// Delete old conversations
$db->query("delete from conversations where conversation_time < date_sub(now(), interval 180 day)");

// Delete old annotations
$db->query("delete from annotations where annotation_time  < date_sub(now(), interval 90 day)");


$db->barrier();

// Delete email, names and url of autodisabled users in the last hours
$dbusers = $db->get_col("select SQL_NO_CACHE user_id from users where user_email not like '%@disabled' && user_level in ('autodisabled') and user_modification > date_sub(now(), interval 48 hour)");
if ($dbusers) {
	foreach ($dbusers as $id) {
		$user = new User;
		$user->id = $id;
		$user->read();
		if ($user->level == 'autodisabled') { // Double check
			$user->disable(true);
			echo "Executing auto disabling: $id - $user->username\n";
		}
	}
}

// Delete email, names and url of invalidated users after three months
$dbusers = $db->get_col("select SQL_NO_CACHE user_id from users where user_email not like '%@disabled' && user_level in ('disabled', 'autodisabled') and user_modification < date_sub(now(), interval 3 month)");
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

// Lower karma of disabled users
$db->query("update users set user_karma = 6 where user_level in ('disabled', 'autodisabled') and user_karma > 6 ");

$karma_base=$globals['karma_base'];
$karma_base_max=$globals['karma_base_max']; // If not penalised, older users can get up to this value as base for the calculus
$min_karma=$globals['min_karma'];
$max_karma=$globals['max_karma'];
$special_karma_gain=$globals['special_karma_gain'];
$special_karma_loss=$globals['special_karma_loss'];
$now = "'".$db->get_var("select now()")."'";
$history_from = "date_sub($now, interval 48 hour)";
$ignored_nonpublished = "date_sub($now, interval 12 hour)";
$points_per_published = $globals['karma_points_per_published'];
$points_per_published_max = $globals['karma_points_per_published_max'];
$points_given = 3;
$comment_votes = $globals['comment_votes_multiplier'];
$post_votes = $globals['post_votes_multiplier'];

// Following lines are for negative points given to links
// It takes in account just votes during 24 hours
$points_discarded = 0.10;
$discarded_history_from = "date_sub($now, interval 24 hour)";
$ignored_nondiscarded = "date_sub($now, interval 6 hour)";

// The formula to calculate the decreasing vote points
$sql_points_calc = 'sum((unix_timestamp(link_date) - unix_timestamp(vote_date))/(unix_timestamp(link_date) - unix_timestamp(link_sent_date))) as points';



$db->barrier();
$published_links = intval($db->get_var("SELECT SQL_NO_CACHE count(*) from links where link_status = 'published' and link_date > $history_from"));

$sum=0; $i=0;

$max_avg_positive_received = (int) $db->get_var("select avg(link_karma) from links where link_status='published' and link_date > $history_from");
$max_avg_positive_received = max(intval($max_avg_positive_received * 0.75), 1);

$max_avg_negative_received = (int) $db->get_var("select avg(link_karma) from links where link_karma < -20 and link_date > $history_from");
$max_avg_negative_received = min(intval($max_avg_negative_received), -20);



// "Unfair" negative votes max
//$max_negative_comment_votes = (int) $db->get_var("select SQL_NO_CACHE count(*) as count from votes, comments where vote_type='comments' and vote_date > date_sub(now(), interval 30 hour) and vote_value < 0 and comment_id = vote_link_id and ((comment_karma-vote_value)/(comment_votes-1)) > 0 group by vote_user_id order by count desc limit 1");
//$max_negative_comment_votes  = max($max_negative_comment_votes, 40);

print "Number of published links in period: $published_links\n";
print "Pos (top 10 average): $max_avg_positive_received, Neg: $max_avg_negative_received\n";
//print "Max unfair comment votes: $max_negative_comment_votes\n";


/////////////////////////



echo "Starting...\n";
$no_calculated = 0;
$calculated = 0;

// We use mysql functions directly because  EZDB cannot hold all IDs in memory and the select fails miserably with about 40.000 users.

$users = "SELECT SQL_NO_CACHE user_id from users where user_level not in ('disabled', 'autodisabled') order by user_modification desc";
//$users = "SELECT SQL_NO_CACHE user_id from users where user_id in (7966, 98342, 21061, 8147)";
//$users = "SELECT SQL_NO_CACHE distinct user_id from users, votes where vote_type in ('comments', 'links') and vote_date > $history_from and vote_user_id = user_id and user_level != 'disabled' order by user_id desc";
//$users = "SELECT SQL_NO_CACHE distinct user_id from users, links where user_level != 'disabled' and link_author=user_id and link_date > date_sub(now(), interval 36 hour) order by user_id desc";
$result = mysql_query($users, $db->dbh) or die('Query failed: ' . mysql_error());
while ($dbuser = mysql_fetch_object($result)) {
	$user = new User;
	$user->id=$dbuser->user_id;
	$user->read();
	printf ("%07d  %s\n", $user->id, $user->username);

	$total_comments = $sent_links = $karma0 = $karma1 = $karma2 = $karma3 = $karma4 = $karma5 = $karma6 = $karma7 = $penalized = 0;
	$output = '';

	//Base karma for the user
	$first_published = $db->get_var("select SQL_NO_CACHE UNIX_TIMESTAMP(min(link_date)) from links where link_author = $user->id and link_status='published';");
	if ($first_published > 0) {
		$karma_base_user = min($karma_base_max, $karma_base + ($karma_base_max - $karma_base) * (time()-$first_published)/(86400*365*2));
		$karma_base_user = round($karma_base_user, 2);
	} else {
		$karma_base_user = $karma_base;
	}

	$n = $db->get_var("SELECT SQL_NO_CACHE count(*) FROM  votes  WHERE vote_type in ('links', 'comments', 'posts') and vote_user_id = $user->id and vote_date > $history_from");
	$n_events = $db->get_var("select SQL_NO_CACHE count(*) from logs where log_date > $history_from and log_user_id=$user->id");
	if ($n > 3 || $n_events > 0) {
		$output .= _('Eventos').': '._('votos').': '. "$n, logs: $n_events\n";

		// Count the number of published links during the last period
		//$n_published = (int) $db->get_var("select SQL_NO_CACHE count(*) from links where link_author = $user->id and link_date > $history_from and link_status = 'published'");

		// Test with published during last three days
		$n_published = (int) $db->get_var("select SQL_NO_CACHE count(*) from links where link_author = $user->id and link_date > date_sub($now, interval 3 day) and link_status = 'published'");

		$karma0 = $points_per_published * $n_published;
		// Max: 4 published
		$karma0 = min($points_per_published * $points_per_published_max, $karma0);
		if ($karma0 > 0) {
			$output .= _('Publicadas').": $n_published karma: $karma0\n";
		}
		$calculated++;

		/////////////////////
		////// Calculates karma received from votes to links
		/////////////////////

		$total_user_links=intval($db->get_var("SELECT SQL_NO_CACHE count(distinct link_id) FROM links, votes WHERE link_author = $user->id and vote_type='links' and vote_link_id = link_id and vote_date > date_sub(now(), interval 1 year)"));
		
		if ($total_user_links > 0) {
			$positive_karma_received = $negative_karma_received = 0;
			//$karmas = $db->get_col("SELECT SQL_NO_CACHE link_karma FROM links WHERE link_author = $user->id and link_date > $history_from and link_karma > 0 and link_status in ('published', 'queued')");
			$karmas = $db->get_col("SELECT SQL_NO_CACHE link_karma*(1-(link_negatives/link_votes*4)) FROM links WHERE link_author = $user->id and link_date > $history_from and link_karma > 0 and link_votes>link_negatives*4 and link_status in ('published', 'queued')");
			if ($karmas) {
				foreach ($karmas as $k) {
					$positive_karma_received += pow(min(1,$k/$max_avg_positive_received), 2) * 4;
				}
			}
			$karmas = $db->get_col("SELECT SQL_NO_CACHE link_karma FROM links WHERE link_author = $user->id and link_date > $history_from and link_karma < 0");
			if ($karmas && $total_user_links > 1) { // don't penalize to users who sent just one link
				foreach ($karmas as $k) {
					$negative_karma_received += pow(min(1,$k/$max_avg_negative_received), 2) * 2;
				}
			}
			$karma_received = $positive_karma_received - $negative_karma_received;
			$karma1 = min(12, $karma_received);
			$karma1 = max(-12, $karma1);
			
			// Check if the user has links tagged as abuse
			$link_abuse = (int) $db->get_var("select SQL_NO_CACHE count(*) from links where link_author = $user->id and link_date > $history_from and link_status = 'abuse'");
			if ($link_abuse > 0) {
				$pun =  3 * $link_abuse;
				$karma1 = max(-12, $karma1 - $pun);
				$output .= _('Penalizado por enlaces que violan las reglas')." ($link_abuse): $pun\n";
				$penalized += 4;
			}


			$output .= _('Karma recibido en envíos propios').",  karma1: ";
			$output .= sprintf("%4.2f\n", $karma1);
		} 

		///////////////////////////////
		/////// Karma for votes to links
		//////////////////////////////

		$user_votes = $db->get_row("SELECT SQL_NO_CACHE count(*) as count, $sql_points_calc FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and link_date > $history_from  and vote_value > 0 AND link_id = vote_link_id AND link_status = 'published' and vote_date < link_date and link_author != $user->id");
		$published_points = (int) $user_votes->points;
		$published_given = (int) $user_votes->count;
		if ($user_votes->points > 0) 
			$published_average = $published_points/$published_given;
		else 
			$published_average = 0;

		$nopublished_given = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > $history_from and vote_date < $ignored_nonpublished and vote_value > 0 AND link_id = vote_link_id AND link_status != 'published' and link_author != $user->id");

		$discarded_given = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > $discarded_history_from  and vote_value > 0 AND link_id = vote_link_id AND link_status in ('discard', 'autodiscard') and link_author != $user->id");

		$abuse_given = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > $history_from  and vote_value > 0 AND link_id = vote_link_id AND link_status in ('abuse') and link_author != $user->id");

		$karma2 = min($points_given, $points_given * pow($published_average, 2) * ($published_points/($published_links/5) - ($nopublished_given/$published_links)/10) - 0.1 * $discarded_given);

		if ($abuse_given > 0) {
			$pun = $abuse_given * 1;
			$karma2 -= $pun;
			$output .= _('Descuento por votar a enlaces que violan las reglas')." ($abuse_given):  $pun\n";
			$penalized += 2;
		}

		if ($karma2 > 0) {
			// Count the  comments of the users during the analised period
			$total_comments = intval($db->get_var("select SQL_NO_CACHE count(*) from comments where comment_user_id = $user->id and comment_date > $history_from"));
			// Count the numbers of link sent by the user in the last 60 days
			$sent_links = intval($db->get_var("select SQL_NO_CACHE count(*) from links where link_author = $user->id and link_date > date_sub(now(), interval 30 day) and link_status != 'discard' and link_status != 'abuse' and link_karma > 50 "));

		}

		//echo "Published giveN: $published_given Published links: $published_links No published: $nopublished_given Comments: $total_comments Links: $sent_links Average: $published_average\n";
		// Bot and karmawhoring warning!!!
		if ($karma2 > 0 && $published_given > $published_links/10 && $published_given > $nopublished_given*1.5 &&
				($published_average < 0.50 || 
				($total_comments < $published_given/2 && $sent_links == 0)) 
			) {
			$penalized += 1;
			if ($total_comments == 0 && $sent_links == 0) {
				$output .= _('Coeficiente de votos muy bajos, posible bot, penalizado');
				$punish_coef = 2;
			} else {
				$output .= _('Coeficiente de votos muy bajos, ¿"karmawhore"?, penalizado');
				$punish_coef = 1;
			}
			$punishment = -$published_average * $punish_coef;
			$output .= sprintf(" karma2 = %4.2f -> %4.2f\n", $karma2, $punishment);
			$karma2 = $punishment;
		} elseif ($karma2 > 0 && ($sent_links == 0 || ($published_given > $nopublished_given && $published_points > $published_links/3 && $published_given > $published_links/5))) {
		// Limit karma to users that does not send any link
		// or "moderated" karma whores
			$karma2 = $karma2 * 0.5;
		}


		if ($karma2 != 0) {
			$output .= _('Votos: a publicadas').": $published_given, "._('no publicadas').": $nopublished_given, "._('descartadas').": $discarded_given\n";
			$output .= sprintf(_('Karma por votos').", karma2: %4.2f\n", $karma2);
		}


		$negative_discarded = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > $discarded_history_from  and vote_value < 0 AND link_id = vote_link_id AND link_status in ('discard', 'autodiscard', 'abuse') and TIMESTAMPDIFF(MINUTE, link_date, vote_date) < 15 ");

		$negative_no_discarded = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) FROM votes,links WHERE vote_type='links' and vote_user_id = $user->id and vote_date > $discarded_history_from and vote_date < $ignored_nondiscarded and vote_value < 0 AND link_id = vote_link_id AND link_status not in ('discard', 'autodiscard', 'abuse') and link_negatives < link_votes/15");

		if ($negative_no_discarded > $negative_discarded/4) { // To fight against karma whores and bots
			$karma3 = $points_discarded * ($negative_discarded - $negative_no_discarded);
		} 
		
		if ($karma3 != 0) {
			$output .= _('Votos negativos a descartadas').": $negative_discarded, "._('no descartadas').": $negative_no_discarded, karma3: ";
			$output .= sprintf("%4.2f\n", $karma3);
		}

		// Check the user don't abuse voting only negative
		$max_allowed_negatives = round(($nopublished_given + $published_given + $negative_discarded) * $user->karma / 10);
		if($negative_no_discarded > 10 && $negative_no_discarded > $max_allowed_negatives) {
			$punishment = min(1+$negative_no_discarded/$max_allowed_negatives, 4);
			$karma3 -= $punishment;
			$penalized += 1;
			$output .= _('Exceso de votos negativos a enlaces')." ($negative_no_discarded > $max_allowed_negatives), "._('penalización').": $punishment, karma3: ";
			$output .= sprintf("%4.2f\n", $karma3);
		}

		//////////////////////////////////////
		/////// Karma for comments' votes
		//////////////////////////////////////
		$comments_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) from comments where comment_user_id = $user->id and comment_date > $history_from and comment_votes > 1");
		if ($comments_count > 0)  {
			$comments_total = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) from comments where comment_user_id = $user->id and comment_date > $history_from");
			$comment_votes_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) from votes, comments where comment_user_id = $user->id and comment_date > $history_from and comment_votes > 1 and vote_type='comments' and vote_link_id = comment_id and  vote_date > $history_from and vote_user_id != $user->id");
			// It calculates a coefficient for the karma, 
			// if number of distinct votes comments >= 10 -> coef = 1, if comments = 1 -> coef = 0.1
			$distinct_votes_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(distinct comment_id) from votes, comments where comment_user_id = $user->id and comment_date > $history_from and comment_votes > 1 and vote_type='comments' and vote_link_id = comment_id and vote_user_id != $user->id");
			$distinct_user_votes_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(distinct vote_user_id) from votes, comments where comment_user_id = $user->id and comment_date > $history_from and comment_votes > 1 and vote_type='comments' and vote_link_id = comment_id and vote_user_id != $user->id");

			$comment_coeff =  min(max(0.2, $comments_count/$comments_total), 0.5) * min($distinct_votes_count/$comments_count, 1) * $distinct_user_votes_count/$comment_votes_count;

			$comment_votes_sum = (int) $db->get_var("SELECT SQL_NO_CACHE sum(vote_value) from votes, comments where comment_user_id = $user->id and comment_date > $history_from and comment_votes > 1 and vote_type='comments' and vote_link_id = comment_id and vote_date > $history_from and vote_user_id != $user->id");
			//echo "Comment new coef: $comment_coeff ($distinct_votes_count,  $distinct_user_votes_count, $comments_count, $comment_votes_count, $comment_votes_sum)\n";
			$karma4 = max(-$comment_votes, min($comment_votes_sum / ($comment_votes_count*10) * $comment_votes, $comment_votes)) * $comment_coeff ;
		}
		
		// Limit karma to users that does not send links and does not vote
		if ( $karma4 > 0 && $karma1 == 0 && $karma2 == 0 && $karma3 == 0 ) $karma4 = $karma4 * 0.5;
		if ($karma4 != 0) {
			$output .= _('Votos a comentarios contabilizados').": $comment_votes_count (karma: $comment_votes_sum), karma4: ";
			$output .= sprintf("%4.2f\n", $karma4);	
		}

		// Penalize to unfair negative comments' votes
		$negative_abused_comment_votes_count = (int) $db->get_var("select SQL_NO_CACHE count(*) from votes, comments where vote_type='comments' and vote_user_id = $user->id and vote_date > $history_from and vote_value < 0 and comment_id = vote_link_id and comment_votes < 10 and ((comment_karma-vote_value)/(comment_votes-1)) > 0 and (comment_votes < 5 or comment_karma >= 5 * comment_votes)");
		if ($negative_abused_comment_votes_count > 5) {
			$karma5 = max(-$comment_votes/2, -$comment_votes * $negative_abused_comment_votes_count/20);
			if ($negative_abused_comment_votes_count > 10 ) {
				$karma5 -= $karma0 / 2 ; // Take away half of karma0
				if ($karma4 > 0) {
					$karma5 -= $karma4 / 2; // Take away half karma4
				}
			}
		}
		if ($karma5 != 0) {
			$penalized = +1;
			$output .= _('Exceso de votos negativos injustos a comentarios').": $negative_abused_comment_votes_count, karma5: ";
			$output .= sprintf("%4.2f\n", $karma5);	
		}


		////////////////////////////////////
		// Karma for posts votes
		////////////////////////////////////

		$posts_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) from posts where post_user_id = $user->id and post_date > $history_from and post_votes > 1");
		if ($posts_count > 0)  {
			$posts_total = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) from posts where post_user_id = $user->id and post_date > $history_from");
			$post_votes_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) from votes, posts where post_user_id = $user->id and post_date > $history_from and post_votes > 1 and vote_type='posts' and vote_link_id = post_id and vote_date > $history_from and vote_user_id != $user->id");
			// It calculates a coefficient for the karma, 
			// if number of distinct votes comments >= 10 -> coef = 1, if comments = 1 -> coef = 0.1
			$distinct_votes_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(distinct post_id) from votes, posts where post_user_id = $user->id and post_date > $history_from and post_votes > 1 and vote_type='posts' and vote_link_id = post_id and vote_user_id != $user->id");
			$distinct_user_votes_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(distinct vote_user_id) from votes, posts where post_user_id = $user->id and post_date > $history_from and post_votes > 1 and vote_type='posts' and vote_link_id = post_id and vote_user_id != $user->id");
			$post_coeff =  min(max(0.2, $posts_count/$posts_total), 1) * min($distinct_votes_count/$posts_count, 1) * $distinct_user_votes_count/$post_votes_count;

			$post_votes_sum = (int) $db->get_var("SELECT SQL_NO_CACHE sum(vote_value) from votes, posts where post_user_id = $user->id and post_date > $history_from and post_votes > 1 and vote_type='posts' and vote_link_id = post_id and vote_date > $history_from and vote_user_id != $user->id");
			$karma6 = max(-$post_votes, min($post_votes_sum / $post_votes_count * $post_votes, $post_votes)) * $post_coeff ;
            //echo "Post new coef: $karma6 $post_coeff ($distinct_votes_count, $distinct_user_votes_count, $posts_count)\n";
		}

        // Limit karma to users that do not have other activity
		if ($karma6 > 0 && ($karma0+$karma1+$karma2+$karma3+$karma4+$karma5) < 1 ) $karma6 = 0;
		if ($karma6 != 0) {
			$output .= _('Votos a notas contabilizados').": $post_votes_count (karma: $post_votes_sum), karma6: ";
			$output .= sprintf("%4.2f\n", $karma6);
		}

        // Penalize to unfair negative comments' votes
		$negative_abused_post_votes_count = (int) $db->get_var("select SQL_NO_CACHE count(*) from votes, posts where vote_type='posts' and vote_user_id = $user->id and vote_date > $history_from and vote_value < 0 and post_id = vote_link_id and ((post_karma-vote_value)/(post_votes-1)) > 0 and (post_votes < 5 or post_karma >= 5 * post_votes)");
		if ($negative_abused_post_votes_count > 5) {
			$karma7 = max(-$post_votes/2, -$post_votes * $negative_abused_post_votes_count / 20);
		}
		if ($karma7 != 0) {
			// Commented out to avoid further penalization, yet
            // $penalized = +1;
			$output .= _('Exceso de votos negativos injustos a notas').": $negative_abused_post_votes_count, karma7: ";
			$output .= sprintf("%4.2f\n", $karma7);
		}



		///////////////////////////////////////////
		// Summary
		///////////////////////////////////////////
		$karma_extra = $karma0+$karma1+$karma2+$karma3+$karma4+$karma5+$karma6+$karma7;
		// If the new value is negative or the user is penalized do not use the highest calculated karma base
		if (($karma_extra < 0 && $user->karma <= $karma_base) || $penalized > 1) {
			$karma_base_user = $karma_base;
			if ($penalized > 2) {
				$karma_extra = min($karma_extra, 1);
				$output .= _('Karma extra máximo por penalizaciones o abusos').": 1\n"; 
			}
		}
		$karma = max($karma_base_user+$karma_extra, $min_karma);
		$karma = min($karma, $max_karma);
	} else {
		$no_calculated++;
		$output = '';
		if (abs($user->karma - $karma_base) < 0.1) {
			$karma = $karma_base;
		} elseif ($user->karma > $karma_base) {
			$karma = max($karma_base, $user->karma - 0.5);
		} elseif ($user->karma < $karma_base) {
			$karma = min($karma_base, $user->karma + 0.1);
		} else {
			$karma = $user->karma;
		}
	}

	$output .= sprintf("Karma base: %4.2f\n", $karma_base_user);
	$karma = round($karma, 2);

	if ($user->karma != $karma) {
		$old_karma = $user->karma;
		if ($user->karma > $karma) {
			if ($karma < $karma_base || $penalized > 1) {
				$user->karma = 0.7*$user->karma + 0.3*$karma; // In case of very low karma, penalized more
			} else {
				// Decrease very slowly
				$user->karma = 0.95*$user->karma + 0.05*$karma;
			}
		} else {
			// Increase/decrease faster
			$user->karma = 0.8*$user->karma + 0.2*$karma;
		}
		if ($user->karma > $special_karma_gain && $user->level == 'normal') {
			$user->level = 'special';
		} else {
			if ($user->level == 'special' && $user->karma < $special_karma_loss) {
				$user->level = 'normal';
			}
		}
		$output .= sprintf(_('Karma final').": %4.2f,  ".('cálculo actual').": %4.2f, ".('karma anterior').": %4.2f\n", 
					$user->karma, $karma, $old_karma);
		$user->store();
		// If we run in the same server as the database master, wait few milliseconds
		if (!$db->dbmaster) {
			usleep(5000); // wait 1/200 seconds
		}
	}
	if (!empty($output)) {
		$annotation = new Annotation("karma-$user->id");
		$annotation->text = $output;
		$annotation->store();
	}
	$db->barrier();
	echo $output;
}
mysql_free_result($result);
if ($annotation) $annotation->optimize();
echo "Calculated: $calculated, Ignored: $no_calculated\n";
?>
