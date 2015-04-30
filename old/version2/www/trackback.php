<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'trackback.php');
include(mnminclude.'link.php');
include(mnminclude.'ban.php');

$tb_url    = clean_input_string($_POST['url']);
$title     = $_POST['title'];
$excerpt   = $_POST['excerpt'];
$blog_name = $_POST['blog_name'];
$charset   = $_POST['charset'];


if(!empty($charset)) {
	$title = @iconv($charset, 'UTF-8//IGNORE', $title);
	$excerpt = @iconv($charset, 'UTF-8//IGNORE', $excerpt);
	$blog_name = @iconv($charset, 'UTF-8//IGNORE', $blog_name);
}
$tb_id = intval($_GET['id']);

$link = new Link;
$link->id= $tb_id;

$urlfrom = parse_url($tb_url);

// Antispam of sites like xxx.yyy-zzz.info/archives/xxx.php
if (preg_match('/http:\/\/[a-z0-9]\.[a-z0-9]+-[^\/]+\.info\/archives\/.+\.php$/', $tb_url)) die;

if(check_ban($globals['user_ip'], 'ip')) {
	syslog(LOG_NOTICE, "Meneame: trackback, IP is banned: $urlfrom[host], $globals[user_ip]");
	trackback_response(1, 'Server banned.');
}

if(check_ban($urlfrom[host], 'hostname', false)) {
	syslog(LOG_NOTICE, "Meneame: trackback, server is banned: $urlfrom[host]");
	trackback_response(1, 'Server banned.');
}

if (!$tb_id > 0 || !$link->read())
	trackback_response(1, 'I really need an ID for this to work.');

if (empty($title) && empty($tb_url) && empty($blog_name)) {
	// If it doesn't look like a trackback at all...
	header('Location: ' . $link->get_permalink());
	exit;
}

// Antispam, avoid trackbacks in old articles
if ($link->date < (time() - 86400*7)) {
	//syslog(LOG_NOTICE, "Meneame: Too old: $tb_url -> " . $link->get_permalink());
	die;
}

if ( !empty($tb_url) && !empty($title) && !empty($excerpt) ) {
	header('Content-Type: text/xml; charset=UTF-8');

	$title     = clean_text($title);
	$excerpt   = clean_text($excerpt);
	$blog_name = clean_text($blog_name);
	$title = (mb_strlen($title) > 120) ? mb_substr($title, 0, 120) . '...' : $title;
	$excerpt = (mb_strlen($excerpt) > 200) ? mb_substr($excerpt, 0, 200) . '...' : $excerpt;

	$trackres = new Trackback;
	$trackres->link_id=$tb_id;
	$trackres->type='in';
	$trackres->link = $tb_url;
	$trackres->url = $tb_url;
	if ($trackres->abuse()) {
		trackback_response(1, 'Dont abuse.');
	}

	$dupe = $trackres->read();
	if ( $dupe ) {
		syslog(LOG_NOTICE, "Meneame: We already have a ping from that URI for this post: $tb_url - $permalink");
		trackback_response(1, 'We already have a ping from that URI for this post.');
	}
  
	$contents=@file_get_contents($tb_url);
	if(!$contents) {
		syslog(LOG_NOTICE, "Meneame: The provided URL does not seem to work: $tb_url");
		trackback_response(1, 'The provided URL does not seem to work.');
	}
	

	$permalink=$link->get_permalink();
    $permalink_q=preg_quote($permalink,'/');
	$pattern="/<\s*a.*href\s*=[\"'\s]*".$permalink_q."[#\/0-9a-z\-]*[\"'\s]*.*>.*<\s*\/\s*a\s*>/i";
	if(!preg_match($pattern,$contents)) {
		syslog(LOG_NOTICE, "Meneame: The provided URL does not have a link back to us: $tb_url");
		trackback_response(1, 'The provided URL does not have a link back to us.');
	}
	
	$trackres->title=$title;
	//$trackres->content=$excerpt;
	$trackres->status='ok';
	$trackres->store();
	syslog(LOG_NOTICE, "Meneame: trackback ok: $tb_url - $permalink");

	trackback_response(0);
}

function trackback_response($error = 0, $error_message = '') {
	header('Content-Type: text/xml; charset=UTF-8');
	if ($error) {
		echo '<?xml version="1.0" encoding="utf-8"?'.">\n";
		echo "<response>\n";
		echo "<error>1</error>\n";
		echo "<message>$error_message</message>\n";
		echo "</response>";
		die();
	} else {
		echo '<?xml version="1.0" encoding="utf-8"?'.">\n";
		echo "<response>\n";
		echo "<error>0</error>\n";
		echo "</response>";
	}
	die;
}


?>
