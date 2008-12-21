<?
include('../config.php');
include(mnminclude.'webimages.php');
include(mnminclude.'link.php');

echo "<html><body>";

$res = $db->get_col("select link_id from links where link_status='published' order by link_date desc limit 15");
foreach ($res as $l) {
	$link = new Link();
	$link->id = $l;
	$link->read();
	//echo "$l $link->title URL: $link->url<br>";
	$link->get_thumb();
	echo "<h2>$link->title</h2>";
	if ($link->thumb_url) {
		echo "<p><img src='$link->thumb_url' width='$link->thumb_x' height='$link->thumb_y'></p>";
	}
}

echo "</body></html>";

?>
