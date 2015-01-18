<?php
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
	include('../config.php');
}

//$globals['allowed_gsm_clients'] must be defined;

$remote = $_SERVER["REMOTE_ADDR"];

$ips = array();
foreach (explode(" ", $globals['allowed_gsm_clients']) as $hostname) {
	if(($res = gethostbynamel($hostname))) {
		foreach ($res as $ip) {
			array_push($ips, $ip);
		}
	}
}

if (!in_array($remote, $ips)) {
	echo "ERROR: unauthorised IP\n";
	die;
}

$post = new Post;

if (!empty($_GET['host']) && !empty($_GET['operadora']) && !empty($_GET['movil']) && !empty($_GET['texto'])) {
	// The connection comes from nviasms.es
	$host = clean_input_string($_GET['host']);
	$phone = clean_input_string($_GET['movil']);
	$telco = clean_input_string($_GET['operadora']);
	// The gateway sends text coded in iso-8859
	$text = clean_text(@iconv('ISO-8859-1', 'UTF-8//IGNORE', $_GET['texto']));
	$text = preg_replace('/^NOTA /i', '', $text);
	$date = time();
} else if (!empty($_REQUEST['phone']) && !empty($_REQUEST['date']) && !empty($_REQUEST['text'])) {
	// Conenction from our own server
	$phone = clean_input_string($_REQUEST['phone']);
	$date = strtotime(clean_input_string($_REQUEST['date']));
	$text = clean_text($_REQUEST['text']);
} else {
	echo "ERROR: missing fields\n";
	die;
}


syslog(LOG_NOTICE, "Meneame SMS: from $remote, Tel: $phone");

if (mb_strlen($text) < 5) {
	echo 'OK ' . _('texto muy corto, nota no insertada');
	die;
}

if (strlen($phone) < 10) {
	$phone = '+34' . $phone;
} elseif (!preg_match('/^\+/', $phone)) {
	$phone = '+' . $phone;
}

$user_id = intval($db->get_var("SELECT user_id from users WHERE user_phone is not null and user_phone = '$phone' AND user_level != 'disabled' ORDER BY user_id DESC LIMIT 1"));

$user = new User;
$user->id = $user_id;

if (!$user_id > 0 || !($user->read())) {
	echo 'OK ' . _('teléfono no encontrado, nota no insertada'). ": $phone";
	die;
}

if ($user->karma < 5) {
	echo 'OK ' . _('el karma es muy bajo, nota no insertada');
	die;
}

$post = new Post;
$post->src = 'phone';
$post->author = $user_id;
$post->date = time();
$post->content = $text;
$post->randkey=$date;


if(intval($db->get_var("select count(*) from posts where post_user_id = $user->id and post_date = from_unixtime($post->date) and post_randkey = $post->randkey")) > 0 ) {
		echo 'OK ' . _('nota enviada previamente, nota no insertada');
		die;
}

if(intval($db->get_var("select count(*) from posts where post_user_id = $user->id and post_date > date_sub(now(), interval 1 minute)"))> 0) {
	echo 'OK ' . _('debe esperar 1 minuto entre apuntes vía SMS');
	die;
};

$post->store();

syslog(LOG_NOTICE, "Meneame SMS: inserted Tel: $phone, User: $user->username");
echo "OK " . _('nota insertada'). "($post->id): http://mueveme.net/notame/?id=$user->username";

if(intval($db->get_var("select count(*) from posts where post_user_id = $user->id and post_date > date_sub(now(), interval 24 hour) and post_src='phone'")) < 6) {
	$user->karma = $user->karma + 0.1;
	$user->store();
}
