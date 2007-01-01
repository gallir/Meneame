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

if (!$tb_id > 0 || !$link->read())
	trackback_response(1, 'I really need an ID for this to work.');

if (empty($title) && empty($tb_url) && empty($blog_name)) {
	// If it doesn't look like a trackback at all...
	header('Location: ' . $link->get_permalink());
	exit;
}

// Antispam, avoid trackbacks in old articles
if ($link->date < (time() - 86400*7)) die;
// Antispam of sites like xxx.yyy-zzz.info/archives/xxx.php
if (preg_match('/http:\/\/[a-z0-9]\.[a-z0-9]+-[^\/]+\.info\/archives\/.+\.php$/', $tb_url)) die;

if ( !empty($tb_url) && !empty($title) && !empty($excerpt) ) {
	header('Content-Type: text/xml; charset=UTF-8');

	$title     = clean_text($title);
	$excerpt   = clean_text($excerpt);
	$blog_name = clean_text($blog_name);
	$title = (strlen($title) > 150) ? substr($title, 0, 150) . '...' : $title;
	$excerpt = (strlen($excerpt) > 200) ? substr($excerpt, 0, 200) . '...' : $excerpt;

	$trackres = new Trackback;
	$trackres->link=$tb_id;
	$trackres->type='in';
	$trackres->url = $tb_url;
	$dupe = $trackres->read();
	if ( $dupe ) {
		syslog(LOG_DEBUG, 'We already have a ping from that URI for this post.');
		trackback_response(1, 'We already have a ping from that URI for this post.');
	}
  
	$contents=@file_get_contents($tb_url);
	if(!$contents) {
		syslog(LOG_DEBUG, 'The provided URL does not seem to work.');
		trackback_response(1, 'The provided URL does not seem to work.');
	}
	

	$permalink=$link->get_permalink();
    $permalink_q=preg_quote($permalink,'/');
	$pattern="/<\s*a.*href\s*=[\"'\s]*".$permalink_q."[#\/0-9a-z\-]*[\"'\s]*.*>.*<\s*\/\s*a\s*>/i";
	if(!preg_match($pattern,$contents)) {
		syslog(LOG_DEBUG, 'The provided URL does not have a link back to us.');
		trackback_response(1, 'The provided URL does not have a link back to us.');
	}
	
	$trackres->title=$title;
	$trackres->content=$excerpt;
	$trackres->status='ok';
	$trackres->store();

	trackback_response(0);
}

?>
