<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and 
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>
if (! defined('mnmpath')) {
	include_once('../config.php');
	header('Content-Type: text/html; charset=utf-8');
}

include_once('pager.php');

global $db, $globals;

if (!isset($globals['link_id']) && !empty($_GET['id'])) {
	$globals['link_id'] = intval($_GET['id']);
} 
/** Show voters always
	else {
	// Don't show all voters if it's called from story.php
	$no_show_voters = true;
}
***/

if (! $globals['link_id'] > 0 ) die;

if (!isset($_GET['p']))  {
	$votes_page = 1;
} else $votes_page = intval($_GET['p']);

$votes_page_size = 20;
$votes_offset=($votes_page-1)*$votes_page_size;


$db->query("LOCK TABLES votes READ");
$votes_users = $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_link_id=".$globals['link_id']." AND vote_user_id!=0");
$votes_users_positive = $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_link_id=".$globals['link_id']." AND vote_user_id!=0 and vote_value > 0");
$votes_anon = $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_link_id=".$globals['link_id']." AND vote_user_id=0");

$negatives = $db->get_results("select vote_value, count(vote_value) as count from votes where vote_type='links' and vote_link_id=".$globals['link_id']." and vote_value < 0 group by vote_value order by count desc");
$db->query("UNLOCK TABLES");

$total_negatives = 0;

echo '<div class="news-details">';
if ($negatives) {
	echo '<strong>'._('votos negativos').':</strong>&nbsp;&nbsp;';
	foreach ($negatives as $negative) {
		echo get_negative_vote($negative->vote_value) . ':&nbsp;' . $negative->count;
		echo '&nbsp;&nbsp;';
		$total_negatives += $negative->count;
	}
}
echo '</div>';

// Check for consistency of votes' counters
if ($globals['link'] && $globals['link']->votes > 0 &&  //Make sure we have read the link object
	($globals['link']->votes !=  $votes_users_positive || $globals['link']->negatives != $total_negatives || $globals['link']->anonymous != $votes_anon)) {
	$globals['link']->votes =  $votes_users_positive;
	$globals['link']->negatives = $total_negatives;
	$globals['link']->anonymous = $votes_anon;
	$globals['link']->store_basic();
}


if ($no_show_voters) {
	// don't show voters if the user votes the link
	echo '<br /><br />&#187;&nbsp;' . '<a href="javascript:get_votes(\'meneos.php\',\'voters\',\'voters-container\',1,'.$globals['link_id'].')" title="'._('quiénes han votado').'">'._('ver quiénes han votado').'</a>';
} else {
	$votes = $db->get_results("SELECT vote_user_id, vote_value, user_avatar, user_login, UNIX_TIMESTAMP(vote_date) as ts,inet_ntoa(vote_ip_int) as ip FROM votes, users WHERE vote_type='links' and vote_link_id=".$globals['link_id']." AND vote_user_id > 0 AND user_id = vote_user_id ORDER BY vote_date DESC LIMIT $votes_offset,$votes_page_size");
	if (!$votes) die;
	echo '<div class="voters-list">';
	foreach ( $votes as $vote ){
		echo '<div class="item">';
		$vote_detail = get_date_time($vote->ts);
		// If current users is a god, show the first IP addresses
		if ($current_user->user_level == 'god') $vote_detail .= ' ('.preg_replace('/\.[0-9]+$/', '', $vote->ip).')';
		if ($vote->vote_value>0) {
			$vote_detail .= ' '._('valor').":&nbsp;$vote->vote_value";
			echo '<a href="'.get_user_uri($vote->user_login).'" title="'.$vote->user_login.': '.$vote_detail.'">';
			echo '<img src="'.get_avatar_url($vote->vote_user_id, $vote->user_avatar, 20).'" width="20" height="20" alt=""/>';
			echo $vote->user_login.'</a>';
		} else {
			if($globals['show_negatives'] > 0 && $vote->ts > $globals['show_negatives']) {
				echo '<a href="'.get_user_uri($vote->user_login).'" title="'.$vote->user_login.': '.$vote_detail.'">';
				echo '<img src="'.get_avatar_url($vote->vote_user_id, $vote->user_avatar, 20).'" width="20" height="20" alt=""/></a>';
				echo '<span>'.get_negative_vote($vote->vote_value).'</span>';
			} else {
				echo '<span>';
				echo '<img src="'.$globals['base_url'].'img/mnm/mnm-anonym-vote-01.png" width="20" height="20" alt="'._('anónimo').'" title="'.$vote_detail.'"/>';
				echo get_negative_vote($vote->vote_value).'</span>';
			}
		}
		echo '</div>';
	}
	echo "</div>\n";
	do_contained_pages($globals['link_id'], $votes_users, $votes_page, $votes_page_size, 'meneos.php', 'voters', 'voters-container');
}

?>
