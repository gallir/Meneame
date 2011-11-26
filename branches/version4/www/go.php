<?
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('./config.php');

$id = intval($_GET['id']);
if ($id > 0) {
	$what = $_GET['what'];
	switch ($what) {
		case 'post':
			$url = 'http://'.get_server_name().post_get_base_url($id);
			do_redirection($url);
			exit(0);
		case 'comment':
			$c = new Comment();
			$c->id = $id;
			$url = 'http://'.get_server_name().$c->get_relative_individual_permalink();
			do_redirection($url);
			exit(0);
		default:

			if (! $globals['mobile_version']
				&& $current_user->user_id > 0
				&& User::get_pref($current_user->user_id, 'use_bar')
				&& $db->get_var("select blog_type from links, blogs where link_id = $id and blog_id = link_blog") != 'noiframe') {
				if ($globals['base_bar_url']) {
					$url = $globals['base_url'] . $globals['base_bar_url'] . $id;
				} else {
					$url = $globals['base_url'] . "bar.php?id=$id";
				}
				Link::add_click($id);
				do_redirection($url, 307);
				exit(0);
			}

			$l = $db->get_row("select link_url as url, link_ip as ip from links where link_id = $id");
			if ($l) {
				Link::add_click($id);
				do_redirection($l->url);
				exit(0);
			}
	}
}
require(mnminclude.$globals['html_main']);
do_error(_('enlace inexistente'), 404);

function do_redirection($url, $code = 301) {
	header("HTTP/1.1 $code Moved");
	header('Location: ' . $url);
	header("Content-Length: 0");
	header("Connection: close");
	flush();
}
