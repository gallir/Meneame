#! /usr/bin/env php
<?
// Post to Twitter/Jaiku the "top story"
// Check which hostname server we run for, for example: mnm, emnm

include(dirname(__FILE__).'/../config.php');
include(mnminclude.'external_post.php');

$site_name = $argv[1];
$my_id = SitesMgr::get_id($site_name);

if (! $my_id > 0) {
	syslog(LOG_INFO, "Meneame, ".basename(__FILE__)." site not found $site_name");
	echo "No site id found\n";
	die;
}

SitesMgr::__init($my_id);
$info = SitesMgr::get_info();
$properties = SitesMgr::get_extended_properties();


$a_tops = new Annotation('top-link-'.$site_name);
echo 'top-link-'.$site_name."\n";
if(!$a_tops->read()) {
	exit;
}
$tops = explode(',', $a_tops->text);

$a_history = new Annotation('top-link-history-'.$site_name);
if($a_history->read()) {
	$history = explode(',',$a_history->text);
} else {
	$history = array();
}

if (! in_array($tops[0], $history) ) {
	if( ! ($link = Link::from_db($tops[0])) ) {
		echo "Error reading link ". $tops[0] . "\n";
		exit;
	}
	$url = $link->get_permalink($info->sub);
	if ($globals['url_shortener']) {
		$short_url = $link->get_short_permalink();
	} else {
		$short_url = $url;
	}
	$intro = '#'._('destacada');
	$text = "$intro $link->title";

	// Save the history
	array_push($history, intval($tops[0]));
	while(count($history) > 10) array_shift($history);
	$a_history->text = implode(',',$history);
	$a_history->store();


	twitter_post($properties, $text, $url); 
	facebook_post($properties, $link, $intro);
}

?>
