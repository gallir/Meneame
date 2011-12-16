<?
// Post to Twitter/Jaiku the "top story"

include('../config.php');
include(mnminclude.'external_post.php');

$my_name = $globals['site_shortname'];
$a_tops = new Annotation('top-link-'.$my_name);
echo 'top-link-'.$my_name."\n";
if(!$a_tops->read()) {
	exit;
}
$tops = explode(',', $a_tops->text);

$a_history = new Annotation('top-link-post-history-'.$my_id);
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
	if ($globals['url_shortener']) {
		$short_url = $link->get_short_permalink();
	} else {
		$short_url = fon_gs($link->get_permalink());
	}
	$intro = '#'._('destacada');
	$text = "$intro $link->title";

	// Save the history
	array_push($history, intval($tops[0]));
	while(count($history) > 10) array_shift($history);
	$a_history->text = implode(',',$history);
	$a_history->store();


	// Post to Twitter, Jaiku and Facebook
	if ($globals['twitter_token']) {
		twitter_post($text, $short_url); 
	}
	if ($globals['jaiku_user'] && $globals['jaiku_key']) {
		jaiku_post($text, $short_url); 
	}
	if ($globals['facebook_token']) {
		facebook_post($link, $intro);
	}
}

?>
