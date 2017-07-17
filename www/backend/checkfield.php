<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//         http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called 'COPYING'.

include __DIR__.'/../config.php';

header('Content-Type: text/plain; charset=UTF-8');

$name = clean_input_string($_GET['name']);
$value = clean_input_string($_GET['value']);

if ($name === 'username') {
    if (!check_username($value)) {
        die(_('Caracteres inválidos o no comienzan con una letra'));
    }

    if (strlen($value) < 3) {
        die(_('Nombre demasiado corto'));
    }

    if (strlen($value) > 24) {
        die(_('Nombre demasiado largo'));
    }

    if (!($current_user->user_id > 0 && $current_user->user_login == $value) && user_exists($value, $current_user->user_id)) {
        die(_('El usuario ya existe'));
    }

    die('OK');
}

if ($name === 'email') {
    if (!check_email($value)) {
        die(_('Dirección de correo no válida'));
    }

    if (!($current_user->user_id > 0 && $current_user->user_email == $value) && email_exists($value, $current_user->user_id == 0)) {
        // Only check for previuos used if the user is not authenticated
        die(_('Dirección de correo duplicada, o fue usada recientemente'));
    }

    die('OK');
}

require_once mnminclude . 'ban.php';

if ($name === 'ban_hostname') {
    if (strlen($value) > 64) {
        die(_('Nombre demasiado largo'));
    }

    if ($ban = check_ban($value, 'hostname')) {
        die($ban['comment']);
    }

    die('OK');
}

if ($name === 'ban_punished_hostname') {
    if (strlen($value) > 64) {
        die(_('Nombre demasiado largo'));
    }

    if ($ban = check_ban($value, 'punished_hostname')) {
        die($ban['comment']);
    }

    die('OK');
}

if ($name === 'ban_email') {
    if (strlen($value) > 64) {
        die(_('Nombre demasiado largo'));
    }

    if (!check_email($value)) {
        die(_('Dirección de correo no válida'));
    }

    if ($ban = check_ban($value, 'email')) {
        die($ban['comment']);
    }

    die('OK');
}

if ($name === 'ban_ip') {
    if (strlen($value) > 64) {
        die(_('Nombre demasiado largo'));
    }

    if ($ban = check_ban($value, 'ip')) {
        die($ban['comment']);
    }

    die('OK');
}

if ($name === 'ban_proxy') {
    if (strlen($value) > 64) {
        die(_('Nombre demasiado largo'));
    }

    if ($ban = check_ban($value, 'proxy')) {
        die($ban['comment']);
    }

    die('OK');
}

if ($name === 'ban_words') {
    if (strlen($value) > 64) {
        die(_('Nombre demasiado largo'));
    }

    if (($ban = check_ban($value, 'words'))) {
        die($ban['comment']);
    }

    die('OK');
}

die("KO $name");
