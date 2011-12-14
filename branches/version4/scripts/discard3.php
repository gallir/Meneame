<?
include('../config.php');
include(mnminclude.'external_post.php');

global $globals, $db;

header("Content-Type: text/plain");

$now = time();
$max_date = "date_sub(now(), interval 15 minute)";
$min_date = "date_sub(now(), interval 24 hour)";

// Delete not validated users
$db->query("delete from users where user_date < date_sub(now(), interval 12 hour) and user_date > date_sub(now(), interval 24 hour) and user_validated_date is null");

// Delete old bad links
$minutes = intval($globals['draft_time'] / 60);
$db->query("delete from links where link_status='discard' and link_date > date_sub(now(), interval 24 hour) and link_date < date_sub(now(), interval $minutes minute) and link_votes = 0");

// send back to queue links with too many negatives
$links = $db->get_results("select SQL_NO_CACHE link_id as id from links where link_status = 'published' and link_date > date_sub(now(), interval 6 day) and link_date < date_sub(now(), interval 8 minute) and link_negatives > link_votes / 8");


$l = new Link();
if ($links) {
	foreach ($links as $link) {
		$l->id = $link->id;
		$l->read_basic();
		// Count only those votes with karma > 6 to avoid abuses with new accounts with new accounts
		$negatives = (int) $db->get_var("select SQL_NO_CACHE sum(user_karma) from votes, users where vote_type='links' and vote_link_id=$l->id and vote_date > '$l->date' and vote_date > date_sub(now(), interval 24 hour) and vote_value < 0 and vote_user_id > 0 and user_id = vote_user_id and user_karma > " . $globals['depublish_negative_karma']);
		$positives = (int) $db->get_var("select SQL_NO_CACHE sum(user_karma) from votes, users where vote_type='links' and vote_link_id=$l->id and vote_date > '$l->date' and vote_value > 0 and vote_date > date_sub(now(), interval 24 hour) and vote_user_id > 0 and user_id = vote_user_id and user_karma > " . $globals['depublish_positive_karma']);
		echo "Candidate $l->id ($l->karma) $negatives $positives\n";
		if ($negatives > $l->karma/6 && $l->negatives > $l->votes/6
			&& ($negatives > $positives || ($negatives > $l->karma/2 && $negatives > $positives/2) )) {
			echo "Queued again: $l->id negative karma: $negatives positive karma: $positives\n";
			$karma_old = $l->karma;
			$karma_new = intval($l->karma/ $globals['depublish_karma_divisor'] );

			$db->query("update links set link_status='queued', link_date = link_sent_date, link_karma=$karma_new where link_id = $l->id");
			SitesMgr::deploy($l);

			// Add an annotation to show it in the logs
			$l->karma_old = $karma_old;
			$l->annotation = _('Retirada de portada');
			$l->save_annotation('link-karma');
			Log::insert('link_depublished', $l->id, $l->author);

			// Add the discard to log/event
			$user = new User($l->author);
			if ($user->read) {
				echo "$user->username: $user->karma\n";
				$user->add_karma(-$globals['instant_karma_per_depublished'], _('Retirada de portada'));
			}

			// Increase karma to users that voted negative
			$ids = $db->get_col("select vote_user_id from votes where vote_type = 'links' and vote_link_id = $l->id and vote_user_id > 0 and vote_value < 0");

			foreach ($ids as $id) {
				$u = new User($id);
				if ($u->read) {
					$u->add_karma(0.2, _('Negativo a retirada de portada'));
				}
			}

			if ($globals['twitter_token'] || $globals['jaiku_user']) {
				if ($globals['url_shortener']) {
					$short_url = $l->get_short_permalink();
				} else {
					$short_url = fon_gs($l->get_permalink());
				}
				$text = _('Retirada de portada') . ': ' . $l->title;
				if ($globals['twitter_user'] && $globals['twitter_token']) {
					twitter_post($text, $short_url);
				}
				if ($globals['jaiku_user'] && $globals['jaiku_key']) {
					jaiku_post($text, $short_url);
				}
			}
		}
	}
}

punish_comments();

// Discard links
$negatives = $db->get_results("select SQL_NO_CACHE link_id from links where link_date > $min_date and link_status = 'queued' and link_karma < 0 and (link_date < $max_date or link_karma < -100) and (link_karma < -link_votes*2 or (link_negatives > 20 and link_negatives > link_votes/2)) and (link_negatives > 20 or (link_negatives > 4 and link_negatives > link_votes) )");

//$db->debug();
if( !$negatives) {
	echo "no negatives to analyze\n";
	die;
}

foreach ($negatives as $negative) {
	$l->id = $negative->link_id;
	$l->read_basic();

	$user = new User($l->author);
	if ($user->read) {
		$user->add_karma(-$globals['instant_karma_per_discard'], _('Noticia descartada'));
		echo "$user->username: $user->karma\n";
	}

	$l->status = 'discard';
	$db->query("update links set link_status='discard' where link_id = $l->id");
	SitesMgr::deploy($l);

	// Add the discard to log/event
	Log::insert('link_discard', $l->id, $l->author);
	echo  "$l->id: $l->karma ($l->votes, $l->negatives)\n";

}


function punish_comments($hours = 2) {
	global $globals, $db;


	$log = new Annotation('punish-comment');
	if ($log->read() && $log->time > time() - 3600*$hours) {
		echo "Comments already verified at: " . get_date_time($log->time) . "\n";
		return false;
	}

	if ($globals['min_karma_for_comments'] > 0) $min_karma =  $globals['min_karma_for_comments'];
	else $min_karma =  4.5;

	$votes_from = time() - $hours * 3600; // 'date_sub(now(), interval 6 hour)';
	$comments_from =  time() - 2 * $hours * 3600; //'date_sub(now(), interval 12 hour)';


	echo "Starting karma_comments...\n";

	$users = "SELECT SQL_NO_CACHE distinct comment_user_id as user_id from comments, users where comment_date > from_unixtime($comments_from) and comment_karma < -70 and comment_user_id = user_id and user_level != 'disabled' and user_karma >= $min_karma";
	$result = $db->get_results($users);

	$log->store();
	if (! $result) return;

	foreach ($result as $dbuser) {
		$user = new User($dbuser->user_id);
		printf ("%07d  %s\n", $user->id, $user->username);
		$punish = 0;

		$comment_votes_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) from votes, comments where comment_user_id = $user->id and comment_date > from_unixtime($comments_from) and vote_type='comments' and vote_link_id = comment_id and  vote_date > from_unixtime($votes_from) and vote_user_id != $user->id");
		if ($comment_votes_count > 5)  {
			$votes_karma = (int) $db->get_var("SELECT SQL_NO_CACHE sum(vote_value) from votes, comments where comment_user_id = $user->id and comment_date > from_unixtime($comments_from) and vote_type='comments' and vote_link_id = comment_id and vote_date > from_unixtime($votes_from) and vote_user_id != $user->id");
			if ($votes_karma < 50) {
			 	$distinct_votes_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(distinct comment_id) from votes, comments where comment_user_id = $user->id and comment_date > from_unixtime($comments_from) and vote_type='comments' and vote_link_id = comment_id and  vote_date > from_unixtime($votes_from) and vote_user_id != $user->id");
				$comments_count = (int) $db->get_var("SELECT SQL_NO_CACHE count(*) from comments where comment_user_id = $user->id and comment_date > from_unixtime($comments_from)");
				$comment_coeff =  min($comments_count/10, 1) * min($distinct_votes_count/($comments_count*0.75), 1);
				$punish = max(-2, round($votes_karma * $comment_coeff * 1/1000,2));
			}
		}
		if ($punish < -0.1) {
			echo "comments: $comments_count votes distinct: $distinct_votes_count karma: $votes_karma coef: $comment_coeff -> $punish\n";
			$user->add_karma($punish, _('Penalización por comentarios'));
			echo(_('Penalización por negativos en comentarios').": $punish, nuevo karma: $user->karma\n");
			$log->append(_('Penalización')." $user->username: $punish, nuevo karma: $user->karma\n");
		}
		$db->barrier();
	}
}
?>
