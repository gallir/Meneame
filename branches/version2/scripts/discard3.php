<?
include('../config.php');
include(mnminclude.'user.php');
include(mnminclude.'log.php');

header("Content-Type: text/plain");

$now = time();
$max_date = "date_sub(now(), interval 15 minute)";
$min_date = "date_sub(now(), interval 20 hour)"; 

// Delete old bad links
$from = $now - 1200;
$db->query("delete from links where link_status='discard' and link_date < from_unixtime($from) and link_votes = 0");


$negatives = $db->get_results("select SQL_NO_CACHE link_id, link_karma, link_votes, link_negatives, link_author from links where link_date < $max_date and link_date > $min_date and link_status = 'queued' and link_karma < link_votes*2 and link_negatives > 3 order by link_negatives desc limit 10 ");

//$db->debug();
if( !$negatives) { 
	echo "no negatives to analyze\n";
	die;
}

foreach ($negatives as $negative) {
	$linkid = $negative->link_id;

	$positive_users_count = $db->get_var("select SQL_NO_CACHE count(*) from votes, users where vote_type='links' and vote_link_id = $linkid and vote_user_id > 0 and vote_value > 0 and user_id = vote_user_id and user_level != 'disabled'");	
	$positive_users = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes, users where vote_type='links' and vote_link_id = $linkid and vote_user_id > 0 and vote_value > 0 and user_id = vote_user_id and user_level != 'disabled'"));	

	$negative_users_count = $db->get_var("select SQL_NO_CACHE count(*) from votes, users where vote_type='links' and vote_link_id = $linkid and vote_user_id > 0 and vote_value < 0 and user_id = vote_user_id and user_level != 'disabled' and user_karma > 6");	
	$negative_users = intval($db->get_var("select SQL_NO_CACHE sum(vote_value-user_karma/2) from votes, users where vote_type='links' and vote_link_id = $linkid and vote_user_id > 0 and vote_value < 0 and user_id = vote_user_id and user_level != 'disabled' and user_karma > 6"));	
	
	//if ($negative_users_count > 2 && ($negative_users_count + $negative_annonymous_count) > $positive_count &&
//		$positive < abs($negative_annonymous) + abs($negative_users) ) {
	if ($negative_users_count > 3 && $negative_users_count > $positive_users_count &&
		$positive_users < /*abs($negative_annonymous) +*/ abs($negative_users) ) {
			$status = "DISCARD";
			$db->query("update links set link_status='discard' where link_id = $linkid");
			// Add the discard to log/event
			log_insert('link_discard', $linkid, $negative->link_author);
	} else {
			$status = "OK";
	}
	echo  "$linkid: $negative->link_karma ($negative->link_votes, $negative->link_negatives),  $negative_users ($negative_users_count) $status\n";

}

?>
