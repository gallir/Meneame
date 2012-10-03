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
} else {
  $votes_page = intval($_GET['p']);
}

$votes_page_size = 40;
$votes_offset=($votes_page-1)*$votes_page_size;

$votes_users = $db->get_var("SELECT count(*) FROM league_votes WHERE match_id=".$globals['match_id']);

$sql = "SELECT 
    league_votes.*, 
    league_votes.date as vdate,
    user_avatar, 
    score_visitor,
    score_local,
    m.date,
    user_login 
FROM 
    league_votes, users, league_matches m
WHERE 
    m.id = {$globals['match_id']} 
    AND m.id = match_id
    AND users.user_id = league_votes.user_id 
ORDER BY league_votes.date DESC LIMIT $votes_offset, 40";
$votes = $db->get_results($sql);

$globals['vote_values'] = array("Empate", "Local", "Visitante");

if (!$votes) return;
echo '<div class="game-voters-list">';

$win_class = '';
if (!empty($votes[0])) {
    if (strtotime($votes[0]->date) < time()) {
        $local = $votes[0]->score_local;
        $vis   = $votes[0]->score_visitor;
        $win_class = $local == $vis ? 0 : ($local > $vis ? 1 : 2);
    }
}

foreach ( $votes as $vote) {
	echo '<div class="item' . ($vote->value == $win_class ? ' winner' : '') . '">';
	$vote_detail = _('el').get_date_time(strtotime($vote->vdate));
	$vote_detail .= ' '._('votó')."&nbsp;" . $globals['vote_values'][$vote->value];
	echo '<a href="'.get_user_uri($vote->user_login).'" title="'.$vote->user_login.': '.$vote_detail.'">';
	echo '<img class="avatar" src="'.get_avatar_url($vote->user_id, $vote->user_avatar, 20).'" width="20" height="20" alt=""/>';
	echo $vote->user_login.'</a>';
	echo '</div>';
}
echo "</div>\n";
do_contained_pages($globals['match_id'], $votes_users, $votes_page, $votes_page_size, 'league_meneos.php', 'voters', 'voters-container-' . $globals['match_id']);
