<?php
// This file checks the rss of a blog against its url.
// If they don't agrre, store a new url
include('../config.php');
include(mnminclude.'blog.php');
include(mnminclude.'link.php');

header("Content-Type: text/plain");

$blog = new Blog;
//$count = $db->get_var("SELECT count(*) from blogs where blog_url regexp 'http://.+/.+'");
$count = $db->get_var("SELECT count(*) from blogs where blog_url regexp 'http://.+'");
echo "$count <br>\n";
flush();
$ids = $db->get_col("SELECT blog_id from blogs where blog_url regexp 'http://.+' order by blog_id asc");
foreach($ids as $dbid) {
	$blog->id = $dbid;
	if( !$blog->read())
		continue;
	$url = $db->get_var("select link_url from links where link_blog = $dbid limit 1");
	if (!empty($url)) {
		$old_url = $blog->url;
		$blog->find_base_url($url);
		$old_key = $blog->key;
		$blog->calculate_key();
		if ($blog->url != $old_url || $old_key != $blog->key) {
			echo "NEW: $new_url ($old_url)<br>\n";
			$old_id = $db->get_var("select blog_id from blogs where blog_key = '$blog->key' and blog_id != $blog->id");
			if ($old_id > 0) {
				echo "REPE: $old_id -> $blog->id<br>\n";
				$db->query("update links set link_blog=$blog->id where link_blog=$old_id");
				$db->query("delete from blogs where blog_id = $old_id");
			}
			$blog->store();
		}
	} else {
		echo "Deleting $dbid\n";
		$db->query("delete from blogs where blog_id = $dbid");
	}

}
