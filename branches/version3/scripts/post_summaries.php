<?
// Post to Twitter/Jaiku the most voted and commented during last 24 hr
include('../config.php');
include(mnminclude.'external_post.php');
include(mnminclude.'link.php');
include(mnminclude.'comment.php');

// Get most voted link
//$link_sqls[_('Más votada 24h')] = "select link_id from links where link_status='published' and link_date > date_sub(now(), interval 24 hour) order by link_votes desc limit 1";
$link_sqls[_('Más votada 24h')] = "select vote_link_id from votes where vote_type='links' and vote_date > date_sub(now(), interval 24 hour) and vote_user_id > 0 and vote_value > 0 group by vote_link_id order by count(*) desc limit 1";
// Most commented
//$link_sqls[_('Más comentada 24h')] = "select link_id from links where link_date > date_sub(now(), interval 24 hour) order by link_comments desc limit 1";
$link_sqls[_('Más comentada 24h')] = "select comment_link_id from comments where comment_date > date_sub(now(), interval 24 hour) group by comment_link_id order by count(*) desc limit 1;";


foreach ($link_sqls as $key => $sql) {
	$id = (int) $db->get_var($sql);
	$link = new Link;
	$link->id = $id;
	if ($link->read()) {
		if ($globals['url_shortener']) {
			$short_url = $link->get_short_permalink();
		} else {
			$short_url = fon_gs($link->get_permalink());
		}
		$text = "$key: $link->title";
		//echo "$text\n";
		if ($globals['twitter_user'] && $globals['twitter_password']) {
			twitter_post($text, $short_url); 
		}
		if ($globals['jaiku_user'] && $globals['jaiku_key']) {
			jaiku_post($text, $short_url); 
		}
	}
}
?>
