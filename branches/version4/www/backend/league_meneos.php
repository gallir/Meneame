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

if (!isset($globals['match_id']) && !empty($_GET['id'])) {
  $globals['match_id'] = intval($_GET['id']);
} 

if (! $globals['match_id'] > 0 ) die;

if (!isset($_GET['p']))  {
$votes_page = 1;
} else $votes_page = intval($_GET['p']);

$votes_page_size = 40;
$votes_offset=($votes_page-1)*$votes_page_size;

$votes_users = $db->get_var("SELECT count(*) FROM league_votes WHERE league_id=".$globals['match_id']);

$sql = "SELECT
	v.*, u.user_avatar,
	u.user_login
FROM " . Match::VOTES . " v
INNER JOIN users u ON (u.user_id = v.user_id)
WHERE match_id = {$globals['match_id']} LIMIT $votes_offset, $votes_page_size";
$votes = $db->get_results($sql);

if (!$votes) return;
echo '<div class="game-voters-list">';
foreach ( $votes as $vote ){
	echo '<div class="item">';
	$vote_detail = get_date_time(strtotime($vote->date));
	$vote_detail .= ' '._('valor').":&nbsp;" . $globals['vote_values'][$vote->value];
	echo '<a href="'.get_user_uri($vote->user_login).'" title="'.$vote->user_login.': '.$vote_detail.'">';
	echo '<img class="avatar" src="'.get_avatar_url($vote->user_id, $vote->user_avatar, 20).'" width="20" height="20" alt=""/>';
	echo $vote->user_login.'</a>';
	echo '</div>';
}
echo "</div>\n";
do_contained_pages($globals['match_id'], $votes_users, $votes_page, $votes_page_size, 'league_meneos.php', 'voters', 'voters-container-' . $globals['match_id']);
