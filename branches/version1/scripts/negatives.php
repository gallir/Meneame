<?
include('../config.php');
include(mnminclude.'uri.php');
include(mnminclude.'link.php');

header("Content-Type: text/plain");

$link = new Link;
$ids = $db->get_results("SELECT link_id from links where link_negatives=0");
foreach($ids as $dbid) {
	$link->id = $dbid->link_id;
	$link->read();
	echo "$link->id\n";
	$link->negatives = $db->get_var("select count(*) from votes where vote_type='links' and vote_link_id=$link->id and vote_value < 0");
	$link->store();
	usleep(1000);
}
?>
