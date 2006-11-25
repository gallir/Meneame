<?
include('../config.php');
include(mnminclude.'user.php');

header("Content-Type: text/plain");

$now = time();
$max_date = $now - 900;
$min_date = $now - 86400;
$min = $now - 36000;
$max = $now - 120;

// Delete old bad links
$from = $now - 3600;
echo("delete from links where link_status='discard' and link_date < from_unixtime($from) and link_votes = 0");
$db->query("delete from links where link_status='discard' and link_date < from_unixtime($from) and link_votes = 0");

$negatives = $db->get_results("select SQL_NO_CACHE link_id from links where link_date < FROM_UNIXTIME($max_date) and link_date > FROM_UNIXTIME($min_date) and link_status = 'queued' and exists (select vote_link_id from votes where vote_type='links' and vote_link_id = link_id and vote_date > FROM_UNIXTIME($min) and  vote_date < FROM_UNIXTIME($max) and vote_value < 0) ");

//$db->debug();
if( !$negatives) { 
	echo "no negatives to analyze\n";
	die;
}

foreach ($negatives as $negative) {
	$linkid = $negative->link_id;
	$positive_count = $db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' and vote_link_id = $linkid and vote_value > 0");	
	$positive_users_count = $db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' and vote_link_id = $linkid and vote_user_id > 0 and vote_value > 0");	
	$positive_users = $db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' and vote_link_id = $linkid and vote_user_id > 0 and vote_value > 0");	
	/* take in accoutn only user votes for discarding
	$positive = $db->get_var("select sum(vote_value) from votes where vote_type='links' and vote_link_id = $linkid and vote_value > 0");	
	$negative_annonymous_count = $db->get_var("select count(*) from votes where vote_type='links' and vote_link_id = $linkid and vote_user_id = 0 and vote_value < 0");
	$negative_annonymous = $db->get_var("select sum(vote_value) from votes where vote_type='links' and vote_link_id = $linkid and vote_user_id = 0 and vote_value < 0");
	*/

	$negative_users_count = $db->get_var("select SQL_NO_CACHE count(*) from votes, users where vote_type='links' and vote_link_id = $linkid and vote_user_id > 0 and vote_value < 0 and user_id = vote_user_id");	
	$negative_users = $db->get_var("select SQL_NO_CACHE sum(vote_value-user_karma) from votes, users where vote_type='links' and vote_link_id = $linkid and vote_user_id > 0 and vote_value < 0 and user_id = vote_user_id");	
	
	//if ($negative_users_count > 2 && ($negative_users_count + $negative_annonymous_count) > $positive_count &&
//		$positive < abs($negative_annonymous) + abs($negative_users) ) {
	if ($negative_users_count > 2 && $negative_users_count > $positive_users_count &&
		$positive_users < /*abs($negative_annonymous) +*/ abs($negative_users) ) {
			$status = "DISCARD";
			$db->query("update links set link_status='discard' where link_id = $linkid");
	} else {
			$status = "OK";
	}
	echo  "$linkid: $positive ($positive_count), $negative_annonymous ($negative_annonymous_count), $negative_users ($negative_users_count) $status\n";

}


?>
