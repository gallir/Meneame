<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'link.php');
include(mnminclude.'ban.php');

header('Content-Type: application/json; charset=UTF-8');
array_push($globals['cache-control'], 'no-cache');
http_cache();

if(check_ban_proxy()) {
	error(_('IP no permitida'));
}

$link = new Link;
$id=intval($_REQUEST['id']);
$user_id=intval($_REQUEST['user']);



$value = round($_REQUEST['value']);
if ($value < -count($globals['negative_votes_values']) || $value > -1)
	error(_('Voto incorrecto') . " $value");

$link->id=$id;
$link->read_basic();

if(!$link->is_votable()) {
	error(_('votos cerrados'));
}

if($current_user->user_id != $user_id) {
	error(_('Usuario incorrecto, recargue la página para poder votar'));
}

if (!check_security_key($_REQUEST['key'])) {
	error(_('clave de control incorrecta'));
}

if(! $link->negatives_allowed(true)) {
	error(_('ya no se puede votar negativo'));
}

$votes_freq = $db->get_var("select count(*) from votes where vote_type='links' and vote_user_id=$current_user->user_id and vote_date > subtime(now(), '0:0:30')");


if ($current_user->user_id > 0) {
	if ($current_user->admin) $freq = 5;
	else $freq = 2;
} else $freq = 2;

if ($votes_freq > $freq && $current_user->user_karma > 4) {
	// Typical "negative votes" attack, decrease karma
	require_once(mnminclude.'user.php');
	require_once(mnminclude.'annotation.php');
	$user = new User;
	$user->id = $current_user->user_id;
	$user->read();
	$user->karma = $user->karma - 1.0;
	$user->store();
	$annotation = new Annotation("karma-$user->id");
	$annotation->append(_('Voto cowboy negativo').": -1, karma: $user->karma\n");
	error(_('¡tranquilo cowboy!, tu karma ha bajado: ') . $user->karma);
}

// Check the user is not a clon by cookie of others that voted the same link
if ($current_user->user_id > 0) {
    if (check_clon_votes($current_user->user_id, $link->id, 5, 'links') > 0) {
        error(_('no se puede votar con clones'));
    }
}

if (!$link->insert_vote($value)) {
	error(_('ya se votó antes con el mismo usuario o IP'));
}

echo $link->json_votes_info(intval($value));


function error($mess) {
	$dict['error'] = $mess;
	echo json_encode($dict);
	die;
}
?>
