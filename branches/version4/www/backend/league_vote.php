<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
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
	error(_('falta el ID del partido'));
}

$vote = check_integer('vote');
if(!in_array($vote, array(0, 1, 2))) {
	error(_('falta el valor del voto'));
}

if(empty($_REQUEST['user']) && $_REQUEST['user'] !== '0' ) {
	error(_('falta el código de usuario'));
}

if (!check_security_key($_REQUEST['key'])) {
	error(_('clave de control incorrecta'));
}

$match = new Match($id);
if (!$match->read_basic()) {
	error(_('partido inexistente'));
}

if(!$match->is_votable()) {
	error(_('votos cerrados'));
}

if ($current_user->user_id == 0) {
	error(_('Los votos anónimos están deshabilitados'));
}

if($current_user->user_id != $_REQUEST['user']) {
	error(_('usuario incorrecto'));
}

// Check the user is not a clon by cookie of others that voted the same link
if ($current_user->user_id > 0 && $match->status != 'published') {
	if (UserAuth::check_clon_votes($current_user->user_id, $match->id, 5, 'links') > 0) {
		error(_('no se puede votar con clones'));
	}
}

try {
    $match->insert_vote($vote);
} catch (Exception $e) {
	error($e->getMessage());
}

echo $match->json_votes_info(intval($vote));

function error($mess) {
	$dict['error'] = $mess;
	echo json_encode($dict);
	die;
}
?>

