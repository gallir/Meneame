<?
include('../config.php');
include(mnminclude.'webimages.php');
include(mnminclude.'link.php');

$id = intval($_GET['id']);

if ($id > 0) {
	$sql = "select link_id from links where link_id=$id";
} else {
	$sql = "select link_id from links where link_status='queued' and link_thumb_status = 'unknown' and link_date > date_sub(now(), interval 2 day) order by link_karma desc limit 2";
}

echo "<html><body>";

$res = $db->get_col($sql);
foreach ($res as $l) {
	$link = new Link();
	$link->id = $l;
	$link->read();
	//echo "$l $link->title URL: $link->url<br>";
	echo "<h2><a href='$link->url'>$link->title</a></h2>";
	if ($link->thumb_status == 'unknown' || $id >0) {
		if ($link->get_thumb()) {
			echo "<h3>New</h3>";
		}
			echo "\n<!--\n".$link->image_parser->html."\n-->\n";
	}
	if ($link->thumb) {
		echo "<p><img src='$link->thumb' width='$link->thumb_x' height='$link->thumb_y'></p>";
	}
}

echo "</body></html>";

?>
