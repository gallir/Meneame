<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'ban.php');

header('Content-Type: application/json; charset=UTF-8');
array_push($globals['cache-control'], 'no-cache');
http_cache();

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

$comment = new Comment();
$comment->id = $id;
if (!$comment->read_basic()) {
	error(_('comentario inexistente'));
}

if ($comment->author == $current_user->user_id) {
	error(_('no puedes votar a tus comentarios'));
}

if ($comment->date < time() - $globals['time_enabled_comments']) {
	error(_('votos cerrados'));
}

// Check the user is not a clon by cookie of others that voted the same cooemnt
if (UserAuth::check_clon_votes($current_user->user_id, $id, 5, 'comments') > 0) {
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

$value = round($value * $current_user->user_karma);
if (!$comment->insert_vote($value)) {
	error(_('ya se votó antes con el mismo usuario o IP'));
}

$comment->votes++;
$comment->karma += $value;
if ($value > 0) $dict['image'] = $globals['base_static'].'img/common/vote-up-gy01.png';
else $dict['image'] = $globals['base_static'].'img/common/vote-down-gy01.png';

$dict['id'] = $id;
$dict['votes'] = $comment->votes;
$dict['value'] = $value;
$dict['karma'] = $comment->karma;

echo json_encode($dict);

function error($mess) {
	$dict['error'] = $mess;
	echo json_encode($dict);
	die;
}

?>
