<?
include('../config.php');
include(mnminclude.'user.php');
include(mnminclude.'log.php');

header("Content-Type: text/plain");

$now = time();
$max_date = "date_sub(now(), interval 15 minute)";
$min_date = "date_sub(now(), interval 24 hour)"; 

// Delete not validated users
$db->query("delete from users where user_date < date_sub(now(), interval 12 hour) and user_date > date_sub(now(), interval 24 hour) and user_validated_date is null");

// Delete old bad links
$db->query("delete from links where link_status='discard' and link_date > date_sub(now(), interval 24 hour) and link_date < date_sub(now(), interval 20 minute) and link_votes = 0");


$negatives = $db->get_results("select SQL_NO_CACHE link_id, link_karma, link_votes, link_negatives, link_author from links where link_date < $max_date and link_date > $min_date and link_status = 'queued' and link_karma < link_votes*2 and ( (link_negatives > 20 and link_karma < 0 ) or (link_negatives > 3 and link_negatives > (link_votes+1)) )");

//$db->debug();
if( !$negatives) { 
	echo "no negatives to analyze\n";
	die;
}

foreach ($negatives as $negative) {
	$linkid = $negative->link_id;
	$user = new User();
	$user->id = $negative->link_author;
	if ($user->read()) {
		$user->karma -= 0.20;
		echo "$user->username: $user->karma\n";
		$user->store();
	}
	$db->query("update links set link_status='discard' where link_id = $linkid");
	// Add the discard to log/event
	log_insert('link_discard', $linkid, $negative->link_author);
	echo  "$linkid: $negative->link_karma ($negative->link_votes, $negative->link_negatives)\n";

}

?>
