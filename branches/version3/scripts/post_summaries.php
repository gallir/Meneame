<?
include('../config.php');
include(mnminclude.'external_post.php');
include(mnminclude.'link.php');
include(mnminclude.'comment.php');


$id = (int) $db->get_var("select link_id from links where link_status='published' and link_date > date_sub(now(), interval 24 hour) order by link_votes desc limit 1");
$link = new Link;
$link->id = $id;
if ($link->read()) {
	if ($globals['url_shortener']) {
		$short_url = $link->get_short_permalink();
	} else {
		$short_url = fon_gs($link->get_permalink());
	}
	echo "$link->title $short_url\n";
	/*****
	if ($globals['twitter_user'] && $globals['twitter_password']) {
		twitter_post($link->title, $short_url); 
	}
	if ($globals['jaiku_user'] && $globals['jaiku_key']) {
		jaiku_post($link->title, $short_url); 
	}
	********/
}

?>
