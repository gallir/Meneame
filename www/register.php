<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once __DIR__.'/config.php';
require_once mnminclude.'html1.php';
require_once mnminclude.'recaptcha2.php';
require_once mnminclude.'ban.php';

$globals['ads'] = false;

// Clean return variable
if (!empty($_REQUEST['return'])) {
    $_REQUEST['return'] = clean_input_string($_REQUEST['return']);
}

if ($current_user->user_id > 0) {
    if (!isset($_COOKIE['return_site'])) {
        $_COOKIE['return_site'] = get_server_name();
    }

    die(header('Location: http://'.$_COOKIE['return_site'].get_user_uri($current_user->user_login)));
}

$process = empty($_POST['process']) ? 0 : intval($_POST['process']);

if ($process) {
    $globals['secure_page'] = true;
} else {
    setcookie('return_site', get_server_name(), 0, $globals['base_url'], UserAuth::domain());
}

do_header(_('registro'), 'post');

switch ($process) {
    case 1:
        do_register1();
        break;

    case 2:
        do_register2();
        break;

    default:
        do_register0();
        break;
}

do_footer();

exit;

function do_register0()
{
    Haanga::Load('register/step-0.html');
}

function do_register1()
{
    if ($error = check_register_error()) {
        return do_register_error($error);
    }

    Haanga::Load('register/step-1.html', array(
        'captcha_form' => ts_print_form()
    ));
}

function do_register2()
{
    if (!ts_is_human()) {
        return do_register_error(_('el código de seguridad no es correcto'));
    }

    if ($error = check_register_error()) {
        return do_register_error($error);
    }

    // Extra check
    if (!check_security_key($_POST['base_key'])) {
        return do_register_error(_('código incorrecto o pasó demasiado tiempo'));
    }

    $username = clean_input_string(trim($_POST['username'])); // sanity check

    if (user_exists($username)) {
        return do_register_error(_('el usuario ya existe'));
    }

    global $db, $globals;

    $dbusername = $db->escape($username); // sanity check
    $password = UserAuth::hash(trim($_POST['password']));
    $email = clean_input_string(trim($_POST['email'])); // sanity check
    $dbemail = $db->escape($email); // sanity check
    $user_ip = $globals['user_ip'];

    if (!$db->query("INSERT INTO users (user_login, user_login_register, user_email, user_email_register, user_pass, user_date, user_ip) VALUES ('$dbusername', '$dbusername', '$dbemail', '$dbemail', '$password', now(), '$user_ip')")) {
        return do_register_error(_('el usuario no ha podido ser registrado en la base de datos'));
    }

    $user = new User();
    $user->username = $username;

    if (!$user->read()) {
        return do_register_error(_('el usuario no ha podido ser registrado en la base de datos'));
    }

    require_once(mnminclude.'mail.php');

    if (!send_recover_mail($user, false)) {
        return do_register_error(_('error enviando el correo electrónico, seguramente está bloqueado'));
    }

    $globals['user_ip'] = $user_ip; //we force to insert de log with the same IP as the form

    Log::insert('user_new', $user->id, $user->id);

    syslog(LOG_INFO, "new user $user->id $user->username $email $user_ip");

    Haanga::Load('register/step-2.html');
}

function check_register_error()
{
    if (check_ban_proxy()) {
        return _('IP no permitida');
    }

    if (!isset($_POST['username']) || strlen($_POST['username']) < 3) {
        return _('nombre de usuario erróneo, debe ser de 3 o más caracteres alfanuméricos');
    }

    if (!check_username($_POST['username'])) {
        return _('nombre de usuario erróneo, caracteres no admitidos o no comienzan con una letra');
    }

    if (user_exists(trim($_POST['username']))) {
        return _('el usuario ya existe');
    }

    if (!check_email(trim($_POST['email']))) {
        return _('el correo electrónico no es correcto');
    }

    if (email_exists(trim($_POST['email']))) {
        return _('dirección de correo duplicada, o fue usada recientemente');
    }

    if (preg_match('/[ \'\"]/', $_POST['password'])) {
        return _('caracteres inválidos en la clave');
    }

    if (!check_password($_POST['password'])) {
        return _('clave demasiado corta, debe ser de 8 o más caracteres e incluir mayúsculas, minúsculas y números');
    }

    global $globals, $db;

    if (empty($globals['skip_ip_register'])) {
        return;
    }

    // Check registers from the same IP network
    $user_ip = $globals['user_ip'];
    $ip_classes = explode(".", $user_ip);

    // From the same IP
    $registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 24 hour) and log_type in ('user_new', 'user_delete') and log_ip = '$user_ip'");

    if ($registered) {
        syslog(LOG_NOTICE, "Meneame, register not accepted by IP address (".$_POST['username'].") $user_ip");

        return _("para registrar otro usuario desde la misma dirección debes esperar 24 horas");
    }

    // Check class
    // nnn.nnn.nnn
    $ip_class = $ip_classes[0].'.'.$ip_classes[1].'.'.$ip_classes[2].'.%';
    $registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 6 hour) and log_type in ('user_new', 'user_delete') and log_ip like '$ip_class'");

    if ($registered) {
        syslog(LOG_NOTICE, "Meneame, register not accepted by IP class (".$_POST['username'].") $ip_class");

        return _("para registrar otro usuario desde la misma red debes esperar 6 horas"). " ($ip_class)";
    }

    // Check class
    // nnn.nnn
    $ip_class = $ip_classes[0].'.'.$ip_classes[1].'.%';
    $registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 1 hour) and log_type in ('user_new', 'user_delete') and log_ip like '$ip_class'");

    if ($registered > 2) {
        syslog(LOG_NOTICE, "Meneame, register not accepted by IP class (".$_POST['username'].") $ip_class");

        return _("para registrar otro usuario desde la misma red debes esperar unos minutos") . " ($ip_class)";
    }
}

function do_register_error($message, $back = null)
{
    Haanga::Load('register/error.html', compact('message', 'back'));
}
