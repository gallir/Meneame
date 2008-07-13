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
	error(_('Usuario incorrecto'). $current_user->user_id . '-'. htmlspecialchars($_REQUEST['user']));
}

if (empty($_REQUEST['value']) || ! is_numeric($_REQUEST['value'])) {
	error(_('Falta valor del voto'));
}

if ($current_user->user_karma < $globals['min_karma_for_post_votes']) {
	error(_('Karma bajo para votar comentarios'));
}

$value = intval($_REQUEST['value']);

if ($value != -1 && $value != 1) {
	error(_('Valor del voto incorrecto'));
}

require_once(mnminclude.'votes.php');
$vote = new Vote;
$vote->user=$current_user->user_id;
$vote->type='posts';
$vote->link=$id;
if ($vote->exists()) {
	error(_('ya has votado antes'));
}



$votes_freq = intval($db->get_var("select count(*) from votes where vote_type='posts' and vote_user_id=$current_user->user_id and vote_date > subtime(now(), '0:0:30') and vote_ip_int = ".$globals['user_ip_int']));

$freq = 6;

if ($votes_freq > $freq) {
	if ($current_user->user_id > 0 && $current_user->user_karma > 4) {
    	// Crazy votes attack, decrease karma
		// she does not deserve it :-)
    	require_once(mnminclude.'user.php');
    	$user = new User;
    	$user->id = $current_user->user_id;
    	$user->read();
    	$user->karma = $user->karma - 0.2;
		$user->store();
		warn(_('¡tranquilo cowboy!, tu karma ha bajado: ') . $user->karma);
	} else  {
		warn(_('¡tranquilo cowboy!'));
	}
}

$vote->value = $value * $current_user->user_karma;
$votes_info = $db->get_row("select post_user_id, post_votes, post_karma, UNIX_TIMESTAMP(post_date) as date from posts where post_id=$id");

if ($votes_info->post_user_id == $current_user->user_id) {
	error(_('no puedes votar a tus comentarios'));
}

if ($votes_info->date < time() - $globals['time_enabled_votes']) {
	error(_('votos cerrados'));
}

if (!$vote->insert()) {
	error(_('ya ha votado antes'));
}


$votes_info->post_votes++;
$votes_info->post_karma += $vote->value;
if ($vote->value > 0) $image = $globals['base_url'].'img/common/vote-up-gy01.png';
else $image = $globals['base_url'].'img/common/vote-down-gy01.png';

echo "$votes_info->post_votes,$votes_info->post_karma,$image";

$db->query("update posts set post_votes=post_votes+1, post_karma=post_karma+$vote->value, post_date=post_date where post_id=$id and post_user_id != $current_user->user_id");

function error($mess) {
	echo "ERROR: $mess";
	die;
}

function warn($mess) {
	echo "WARN: $mess";
	die;
}
?>
