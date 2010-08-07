<?
// Post to Twitter/Jaiku the most voted and commented during last 24 hr
include('../config.php');
include(mnminclude.'external_post.php');

if ($_SERVER['argv'] && intval($_SERVER['argv'][1]) > 0) {
	$hours = intval($_SERVER['argv'][1]);
} else {
	$hours = 24;
}
// Get most voted link
$link_sqls[_('Más votada')] = "select vote_link_id as id, count(*) as n from votes use index (vote_type_4) where vote_type='links' and vote_date > date_sub(now(), interval $hours hour) and vote_user_id > 0 and vote_value > 0 group by vote_link_id order by n desc limit 1";
// Most commented
$link_sqls[_('Más comentada')] = "select comment_link_id as id, count(*) as n from comments use index (comment_date) where comment_date > date_sub(now(), interval $hours hour) group by comment_link_id order by n desc limit 1;";


foreach ($link_sqls as $key => $sql) {
	$res = $db->get_row($sql);
	if (! $res) next;
	$link = new Link;
	$link->id = $res->id;
	if ($link->read()) {
		if ($globals['url_shortener']) {
			$short_url = $link->get_short_permalink();
		} else {
			$short_url = fon_gs($link->get_permalink());
		}
		$text = "$key ${hours}h: $link->title";
		//echo "$short_url $text\n";
		if ($globals['twitter_user'] && $globals['twitter_password']) {
			twitter_post($text, $short_url); 
		}
		if ($globals['jaiku_user'] && $globals['jaiku_key']) {
			jaiku_post($text, $short_url); 
		}
	}
}
?>
