<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

if (! defined('mnmpath')) {
	include('../config.php');
	include_once(mnminclude.'post.php');
	include_once(mnminclude.'user.php');
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
if (empty($_REQUEST['phone']) || empty($_REQUEST['date']) || empty($_REQUEST['text'])) {
	echo "ERROR: missing fields\n";
	die;
}
$phone = clean_input_string($_REQUEST['phone']);
$date = clean_input_string($_REQUEST['date']);
$text = clean_text($_REQUEST['text']);

if (mb_strlen($text) < 5) {
	echo 'ERROR: ' . _('texto muy corto');
	die;
}
if (!preg_match('/^\+/', $phone)) {
	$phone = '+' . $phone;
}
$user_id = intval($db->get_var("SELECT user_id from users WHERE user_phone is not null and user_phone = '$phone' AND user_level != 'disabled' ORDER BY user_id DESC LIMIT 1"));

$user = new User;
$user->id = $user_id;

if (!$user_id > 0 || !($user->read())) {
	echo "ERROR: phone/user not found: $phone-$user_id\n";
	die;
}

if ($user->karma < 6) {
	echo 'ERROR: ' . _('el karma es muy bajo');
	die;
}

$post = new Post;
$post->src = 'phone';
$post->author = $user_id;
$post->date = strtotime($date);
$post->content = $text;
$post->randkey=$post->date;


if(intval($db->get_var("select count(*) from posts where post_user_id = $user->id and post_date = from_unixtime($post->date) and post_randkey = $post->randkey")) > 0 ) {
		echo 'ERROR: ' . _('comentario grabado previamente');
		die;
}

if(intval($db->get_var("select count(*) from posts where post_user_id = $user->id and post_date > date_sub(now(), interval 5 minute)"))> 0) {
	echo 'ERROR: ' . _('debe esperar 5 minutos entre apuntes vía SMS');
	die;
};

$post->store();

echo "OK: stored, $post->id\n";

?>
