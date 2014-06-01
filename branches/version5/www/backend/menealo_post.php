<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include_once(mnminclude.'ban.php');

header('Content-Type: application/json; charset=UTF-8');

if(check_ban_proxy()) {
	error(_('IP no permitida'));
}

if(!($id=check_integer('id'))) {
	error(_('falta el ID del comentario'));
}

if(empty($_REQUEST['user'])) {
	error(_('falta el código de usuario'));
}

if($current_user->user_id != $_REQUEST['user']) {
	error(_('usuario incorrecto'). $current_user->user_id . '-'. htmlspecialchars($_REQUEST['user']));
}

if (!check_security_key($_REQUEST['key'])) {
	error(_('clave de control incorrecta'));
}


if (empty($_REQUEST['value']) || ! is_numeric($_REQUEST['value'])) {
	error(_('falta valor del voto'));
}

if ($current_user->user_karma < $globals['min_karma_for_post_votes']) {
	error(_('karma bajo para votar comentarios'));
}

$value = intval($_REQUEST['value']);

if ($value != -1 && $value != 1) {
	error(_('valor del voto incorrecto'));
}

$vote = new Vote('posts', $id, $current_user->user_id);
$vote->link=$id;
if ($vote->exists()) {
	error(_('ya se votó antes con el mismo usuario o IP'));
}


$votes_freq = intval($db->get_var("select count(*) from votes where vote_type='posts' and vote_user_id=$current_user->user_id and vote_date > subtime(now(), '0:0:30') and vote_ip_int = ".$globals['user_ip_int']));

$freq = 6;

if ($votes_freq > $freq) {
	if ($current_user->user_id > 0 && $current_user->user_karma > 4) {
		// Crazy votes attack, decrease karma
		// she does not deserve it :-)
		$user = new User;
		$user->id = $current_user->user_id;
		$user->read();
		$user->karma = $user->karma - 0.1;
		$user->store();
		error(_('¡tranquilo cowboy!, tu karma ha bajado: ') . $user->karma);
	} else	{
		error(_('¡tranquilo cowboy!'));
	}
}

$vote->value = $value * $current_user->user_karma;

$post = Post::from_db($id);

if (! $post) {
	error(_('nota no existente'));
}

if ($post->author == $current_user->user_id) {
	error(_('no puedes votar a tus comentarios'));
}

if ($post->date < time() - $globals['time_enabled_votes']) {
	error(_('votos cerrados'));
}

if (! $post->insert_vote()) {
	error(_('ya ha votado antes'));
}


$dict = array();
$dict['id'] = $id;
$dict['votes'] = $post->votes + 1;
$dict['value'] = round($vote->value);
$dict['karma'] = round($post->karma + $vote->value);

echo json_encode($dict);

function error($mess) {
	$dict['error'] = $mess;
	echo json_encode($dict);
	die;
}

