<?
// This file checks the rss of a blog against its url.
// If they don't agrre, store a new url
include('../config.php');
include(mnminclude.'blog.php');
include(mnminclude.'link.php');

header("Content-Type: text/plain");

$blog = new Blog;
$count = $db->get_var("SELECT count(*) from blogs where blog_url regexp 'http://.+/.+'");
echo "$count <br>\n";
flush();
$ids = $db->get_col("SELECT blog_id from blogs where blog_url regexp 'http://.+/.+'");
foreach($ids as $dbid) {
	$canditates = Array();
	$blog->id = $dbid;
	$blog->read();
	$url = $db->get_var("select link_url from links where link_blog = $dbid limit 1");
	$rss_a = array($blog->rss, $blog->rss2, $blog->atom);
	foreach ($rss_a as $r) {
		if (strlen($r) > 0) {
			array_push($canditates, $r);
		}
	}
	$r = $blog->shortest_text($canditates);
	$url_url = parse_url($url);
	$rss_url = parse_url($r);
	if (!empty($r) && !empty($url) && $url_url['host'] == $rss_url['host']) {
        // Try to find the base url
		$path='';
		$url_url['path'] = preg_replace('/\/$/', '', $url_url['path']);
		$rss_url['path'] = preg_replace('/\/$/', '', $rss_url['path']);
		if($url_url['host'] == $rss_url['host']) {
			$len = min(strlen($url_url['path']), strlen($rss_url['path']));
			for($i=1;$i<=$len;$i++) {
				if(substr($url_url['path'], 0, $i) != substr($rss_url['path'], 0, $i) ) {
					break;
				}
			$path = substr($url_url['path'], 0, $i);
			}
		}
		$path = preg_replace('/\/$/', '', $path);
		if(empty($url_url['scheme'])) $scheme="http";
		else $scheme=$url_url['scheme'];
		$new_url=$scheme.'://'.$url_url['host'].$path;
		if ($new_url != $blog->url) {
			echo "NEW: $new_url ($blog->url)<br>\n";
			$blog->store();
		}
	}

}
?>
