<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'comment.php');
include(mnminclude.'ban.php');

header('Content-Type: text/plain; charset=UTF-8');

if(check_ban_proxy()) {
	error(_('IP no permitida'));
}

if(!($id=check_integer('id'))) {
	error(_('Falta el ID del comentario'));
}

if(empty($_REQUEST['user'])) {
	error(_('Falta el código de usuario'));
}

if($current_user->user_id != $_REQUEST['user']) {
	error(_('Usuario incorrecto, recargue la página para poder votar'));
}

if (!check_security_key($_REQUEST['key'])) {
	error(_('clave de control incorrecta'));
}

if (empty($_REQUEST['value']) || ! is_numeric($_REQUEST['value'])) {
	error(_('Falta valor del voto'));
}

if ($current_user->user_karma < $globals['min_karma_for_comment_votes']) {
	error(_('Karma bajo para votar comentarios'));
}

$value = intval($_REQUEST['value']);

if ($value != -1 && $value != 1) {
	error(_('Valor del voto incorrecto'));
}

if ($value < 0 && $current_user->user_id == (int) $db->get_var("select link_author from links, comments where comment_id = $id and link_id = comment_link_id")) {
	error(_('no votes negativo a comentarios de tus envíos'));
}

require_once(mnminclude.'votes.php');
$vote = new Vote;
$vote->user=$current_user->user_id;
$vote->type='comments';
$vote->link=$id;
if ($vote->exists(true)) {
	error(_('ya se votó antes con el mismo usuario o IP'));
}

// Check the user is not a clon by cookie of others that voted the same cooemnt
if (check_clon_votes($current_user->user_id, $id, 5, 'comments') > 0) {
	error(_('no se puede votar con clones'));
}

if ($value > 0) {
	$votes_freq = intval($db->get_var("select count(*) from votes where vote_type='comments' and vote_user_id=$current_user->user_id and vote_date > subtime(now(), '0:0:30') and vote_value > 0 and vote_ip_int = ".$globals['user_ip_int']));
	$freq = 10;
} else {
	$votes_freq = intval($db->get_var("select count(*) from votes where vote_type='comments' and vote_user_id=$current_user->user_id and vote_date > subtime(now(), '0:0:30') and vote_value <= 0 and vote_ip_int = ".$globals['user_ip_int']));
	$freq = 5;
}

if ($votes_freq > $freq) {
	if ($current_user->user_id > 0 && $current_user->user_karma > 4) {
    	// Crazy votes attack, decrease karma
		// she does not deserve it :-)
    	require_once(mnminclude.'user.php');
    	require_once(mnminclude.'annotation.php');
    	$user = new User;
    	$user->id = $current_user->user_id;
    	$user->read();
    	$user->karma = $user->karma - 0.2;
		$user->store();
		$annotation = new Annotation("karma-$user->id");
		$annotation->append(_('Voto cowboy a comentarios').": -0.2, karma: $user->karma\n");
		error(_('¡tranquilo cowboy!, tu karma ha bajado: ') . $user->karma);
	} else  {
		error(_('¡tranquilo cowboy!'));
	}
}

$vote->value = $value * $current_user->user_karma;
$votes_info = $db->get_row("select comment_user_id, comment_votes, comment_karma, UNIX_TIMESTAMP(comment_date) as date from comments where comment_id=$id");

if ($votes_info->comment_user_id == $current_user->user_id) {
	error(_('no puedes votar a tus comentarios'));
}

if ($votes_info->date < time() - $globals['time_enabled_comments']) {
	error(_('votos cerrados'));
}

if (!$vote->insert()) {
	error(_('ya ha votado antes'));
}


$votes_info->comment_votes++;
$votes_info->comment_karma += $vote->value;
if ($vote->value > 0) $dict['image'] = $globals['base_static'].'img/common/vote-up-gy01.png';
else $dict['image'] = $globals['base_static'].'img/common/vote-down-gy01.png';

$dict['id'] = $id;
$dict['votes'] = $votes_info->comment_votes;
$dict['value'] = $vote->value;
$dict['karma'] = $votes_info->comment_karma;

echo json_encode_single($dict);

$db->query("update comments set comment_votes=comment_votes+1, comment_karma=comment_karma+$vote->value, comment_date=comment_date where comment_id=$id and comment_user_id != $current_user->user_id");

function error($mess) {
	$dict['error'] = $mess;
	echo json_encode_single($dict);
	die;
}

?>
