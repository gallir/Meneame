<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include_once(mnminclude.'ban.php');

header('Content-Type: application/json; charset=UTF-8');
array_push($globals['cache-control'], 'no-cache');
http_cache();

if(check_ban_proxy()) {
	error(_('IP no permitida'));
}

if(!($id=check_integer('id'))) {
	error(_('falta el ID del artículo'));
}

if(empty($_REQUEST['user']) && $_REQUEST['user'] !== '0' ) {
	error(_('falta el código de usuario'));
}

if (!check_security_key($_REQUEST['key'])) {
	error(_('clave de control incorrecta'));
}

$link = Link::from_db($id, null, false);
if(!$link) {
	error(_('artículo inexistente'));
}

if(!$link->is_votable() || $link->total_votes == 0) {
	error(_('votos cerrados'));
}

// Only if the link has been not published, let them play
if ($current_user->user_id == 0 && $link->status != 'published') {
	if (! $anonnymous_vote) {
		error(_('Los votos anónimos están temporalmente deshabilitados'));
	} else {
		// Check that there are not too much annonymous votes
		if ($link->status == 'published') $anon_to_user_votes = max(10, $globals['anon_to_user_votes']); // Allow more ano votes if published. 
		if ($link->anonymous >	$link->votes * $globals['anon_to_user_votes']) {
			error(_('Demasiados votos anónimos para esta noticia, regístrese como usuario o inténtelo más tarde'));
		}
	}
}

if($current_user->user_id != $_REQUEST['user']) {
	error(_('usuario incorrecto'));
}

if ($current_user->user_id == 0) $ip_check = 'and vote_ip_int = '.$globals['user_ip_int'];
else $ip_check = '';
$votes_freq = $db->get_var("select count(*) from votes where vote_type='links' and vote_user_id=$current_user->user_id and vote_date > subtime(now(), '0:0:30') $ip_check");

// Check the user is not a clon by cookie of others that voted the same link
if ($current_user->user_id > 0 && $link->status != 'published') {
	if (UserAuth::check_clon_votes($current_user->user_id, $link->id, 5, 'links') > 0) {
		error(_('no se puede votar con clones'));
	}
}

if ($current_user->user_id > 0) $freq = 3;
else $freq = 2;


if ($link->status == 'published')  $freq *= 2; // Allow to play a little more if published

// Check for clicks vs votes
// to avoid "cowboy votes" without reading the article
if (!empty($link->url) && $globals['click_counter'] 
	&& ! $link->user_clicked()) {
	if ($link->votes > 3 && $link->negatives  > 2 && $current_user->user_id > 0 && $link->votes/10 < $link->negatives && $link->get_clicks() < $link->total_votes * 1.5) {
		error(_('enlace no leído, con muchos negativos'));
	} elseif ( (empty($_GET['l']) || $_GET['l'] != $link->id) 
		// Check is not in "story" page 
		&& $link->total_votes > $link->get_clicks()) {
		// Don't allow to vote if it has less clicks than votes
		error(_('no leído, y con más votos que lecturas').' ('.$link->get_clicks().' < '.$link->total_votes.')');
	}
}

if ($votes_freq > $freq) {
	if ($current_user->user_id > 0 && $current_user->user_karma > 4 && $link->status != 'published') {
		// Crazy votes attack, decrease karma
		// she does not deserve it :-)
		$user = new User($current_user->user_id);
		$user->add_karma(-0.2, _('voto cowboy'));
		error(_('¡tranquilo cowboy!'). ', ' . _('tu karma ha bajado: ') . $user->karma);
	} else	{
		error(_('¡tranquilo cowboy!'));
	}
}

if($current_user->user_id>0) {
	$value = $current_user->user_karma;
} else {
	$value=$globals['anon_karma'];
}

if (!$link->insert_vote($value)) {
	if ($current_user->user_id > 0) {
		error(_('ya se votó antes con el mismo usuario o IP'));
	} else {
		error(_('ya se votó antes desde la misma IP'));
	}
}

if ($link->status == 'discard' && $current_user->user_id>0 && $link->votes > $link->negatives && $link->karma > 0) {
	$link->status = 'queued';
	$link->store_basic();
}

echo $link->json_votes_info(intval($value));

function error($mess) {
	$dict['error'] = $mess;
	echo json_encode($dict);
	die;
}
