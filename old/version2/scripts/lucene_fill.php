<?
include('../config.php');
include(mnminclude.'link.php');

echo  ini_get('memory_limit'). "\n";
ini_set('memory_limit', '64M');
echo  ini_get('memory_limit'). "\n";

$start = 26900;

$link = new Link;

$result = mysql_query("select link_id from links where link_status in ('published', 'queued') and link_id > $start order by link_id asc") or die('Query failed: ' . mysql_error());

while ($res = mysql_fetch_object($result)) {
	$link->id = $res->link_id;
	if($link->read()) {
		echo "$link->id: $link->uri<br>\n";
		$link->lucene_update();
		usleep(100);
		
	}
}


?>

