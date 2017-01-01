<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (!defined('mnmpath')) {
    include('../config.php');
    include(mnminclude.'html1.php');
}

array_push($globals['cache-control'], 'no-cache');
http_cache();

if (empty($current_user->user_id)) {
    die('ERROR: '._('Esta acción sólo es posible para usuarios registrados'));
}

$_POST = array_map('intval', $_POST);

if (empty($_POST['id'])) {
    die('ERROR: '._('No se ha podido obtener la encuesta solictada'));
}

if (empty($_POST['option']) || empty($_POST['option'])) {
    die('ERROR: '._('La opción de votación indicada no es válida'));
}

$poll = new Poll;
$poll->id = $_POST['id'];

if (!$poll->read()) {
    die('ERROR: '._('La encuesta indicada no existe'));
}

if ($poll->voted) {
    die('ERROR: '._('Ya habías votado en esta encuesta'));
}

if (!($option = $poll->getOption($_POST['option']))) {
    die('ERROR: '._('La opción votada no existe para esta encuesta'));
}

// Check the user is not a clon by cookie of others that voted the same comment
if (UserAuth::check_clon_votes($current_user->user_id, $poll->id, 5, 'polls')) {
    die('ERROR: '._('No se puede votar con clones'));
}

// Verify that there are a period of 1 minute between posts.
if (Vote::fast_vote('polls', 30)) {
    die('ERROR: '._('Debes esperar 30 segundos entre votaciones'));
}

if (!$poll->vote($option)) {
    die('ERROR: '._('Lo sentimos pero no ha sido posible registrar el voto'));
}

Haanga::Load('poll_voted.html', array('poll' => $poll));

die();
