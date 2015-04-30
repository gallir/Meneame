#! /usr/bin/php
<?
include('../config.php');

$hours = intval($argv[1]);
if (! $hours) $hours = 4;

$uids = $db->get_col("select distinct(comment_user_id) from votes, comments where vote_type='comments' and vote_date > date_sub(now(), interval $hours hour) and comment_id = vote_link_id");

echo "Total: " . count($uids) . "\n";

foreach ($uids as $id) {
	//$affinity = User::get_affinity($id);
	$affinity = User::calculate_affinity($id, 100);
	if ($affinity) {
		echo "Calculated for $id, length: " . count($affinity) . "\n";
		usleep(100);
	}
}
?>
