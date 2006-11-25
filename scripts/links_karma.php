<?
// This files checks and fixes the vote, negative and karma values

include('../config.php');
include(mnminclude.'link.php');

header("Content-Type: text/plain");
ob_end_flush();

$now = time();
echo "BEGIN: ".get_date_time($now)."\n";
if(!empty($_GET['period']))
	$period = intval($_GET['period']);
else $period = 200;

echo "Period (h): $period\n";

$from_time = "date_sub(now(), interval 2 day)";
#$from_where = "FROM votes, links WHERE  


$links = $db->get_results("SELECT SQL_NO_CACHE link_id from links where link_date > $from_time AND link_status != 'published' and link_karma < 150");
$rows = $db->num_rows;
if (!$rows) {
	echo "There is no articles\n";
	echo "--------------------------\n";
	die;
}
if ($links) {
	foreach($links as $dblink) {
		$link = new Link;
		$link->id=$dblink->link_id;
		$link->read();
		$karma_pos_user = 0;
		$karma_neg_user = 0;
		$karma_pos_ano = 0;
		$karma_neg_ano = 0;

		// Count number of votes
		$votes_pos = $db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' AND vote_link_id=$link->id and vote_value >= 0");
		$votes_neg = $db->get_var("select SQL_NO_CACHE count(*) from votes where vote_type='links' AND vote_link_id=$link->id and  vote_value < 0");

		// Calculate the real karma for the link
		$karma_pos_user = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value > 0"));
		$karma_neg_user = intval($db->get_var("select SQL_NO_CACHE sum(vote_value-user_karma/2) from votes, users where vote_type='links' AND vote_link_id=$link->id and vote_user_id > 0 and vote_value < 0 and user_id=vote_user_id"));

		$karma_pos_ano = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' AND vote_link_id=$link->id and vote_user_id = 0 and vote_value > 0"));
		$karma_neg_ano = intval($db->get_var("select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' AND vote_link_id=$link->id and vote_user_id = 0 and vote_value < 0"));


		if ($link->votes != $votes_pos || $link->negatives != $votes_neg 
		|| round($link->karma) != round($karma_pos_user + $karma_pos_ano + $karma_neg_user + $karma_neg_ano)
			) {
			echo "Previous $link->id, $link->votes, $link->negatives, $link->karma \n";
			$link->votes = $votes_pos;
			$link->negatives = $votes_neg;
			$link->karma = round($karma_pos_user + $karma_pos_ano + $karma_neg_user + $karma_neg_ano);
			echo "Storing $link->id, $link->votes, $link->negatives, $link->karma \n";
			$link->store();
		}
	}  
}
?>
