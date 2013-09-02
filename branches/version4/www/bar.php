<?
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

if (!isset($_REQUEST['id']) && $globals['base_bar_url'] && $_SERVER['PATH_INFO']) {
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
	array_shift($url_args); // The first element is always a "/"
	$id = intval($url_args[0]);
} else {
	$url_args = preg_split('/\/+/', $_REQUEST['id']);
	$id=intval($url_args[0]);
	if($id > 0 && $globals['base_bar_url']) {
		// Redirect to the right URL if the link has a "semantic" uri
		header ('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $globals['base_url'] . $globals['base_bar_url'] . $id);
		die;
	}
}

if (! ($link = Link::from_db($id))) {
	do_error(_('enlace no encontrado'), 404);
}

// Mark as read, add click if necessary
Link::add_click($link->id, $link->ip);

$link->title = text_to_summary($link->title, 80);

// From libs/html1.php do_header()
header('Content-Type: text/html; charset=utf-8');
$globals['security_key'] = get_security_key();
setcookie('k', $globals['security_key'], 0, $globals['base_url']);

// From libks/link.php print_summary()
$link->is_votable();
$link->permalink = $link->get_permalink();
$link->can_vote_negative = !$link->voted && $link->votes_enabled &&
		$link->negatives_allowed(true);
$link->get_box_class();
$vars = compact('type');
$vars['self'] = $link;

Haanga::Load("link_bar.html", $vars);


