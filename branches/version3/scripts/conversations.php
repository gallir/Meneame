<?
include('../config.php');
include(mnminclude.'comment.php');

header("Content-Type: text/plain");

$db->connect();
$sql = "select comment_id from comments where comment_date > date_sub(now(), interval 7 day) and comment_content like '%#%'";
$result = mysql_query($sql, $db->dbh) or die('Query failed: ' . mysql_error());
while ($res = mysql_fetch_object($result)) {
	$comment = new Comment;
	$comment->id = $res->comment_id;
	$comment->read();
	echo "Updating $comment->id\n";
	$comment->update_conversation();
	usleep(100);
}


?>
