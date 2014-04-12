<?
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('./config.php');

$id = intval($_GET['id']);
if ($id > 0) {
	$what = $_GET['what'];
	switch ($what) {
		/* From notifier */
		case 'privates':
			$url = post_get_base_url('_priv');
			do_redirection($url);
			exit(0);
		case 'posts':
			$url = post_get_base_url($current_user->user_login) . '/_conversation';
			do_redirection($url);
			exit(0);
		case 'comments':
			$url = get_user_uri($current_user->user_login, 'conversation');
			do_redirection($url);
			exit(0);
		case 'friends':
			$url = get_user_uri($current_user->user_login, 'friends_new');
			do_redirection($url);
			exit(0);


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
			$l = Link::from_db($id, null, false);
			if (! $l) exit(0);

			if (! $globals['mobile']
				&& ! $globals['mobile_version']
				&& ! empty($l->url)
				&& $current_user->user_id > 0
				&& User::get_pref($current_user->user_id, 'use_bar')
				&& $db->get_var("select blog_type from blogs where blog_id = $l->blog") != 'noiframe') {
				$url = $globals['base_url'] . 'b/' . $id;
				do_redirection($url, 307);
			} else {
				if (empty($l->url)) $url = $l->get_permalink();
				else $url = $l->url;
				do_redirection($url);
			}
			$l->add_click();
			exit(0);
	}
} else {
	require(mnminclude.$globals['html_main']);
	do_error(_('enlace inexistente'), 404);
}

function do_redirection($url, $code = 301) {
	if (isset($_GET['quiet'])) {
		return; // Don't redirect if the caller asked so
	}
	redirect($url, $code);
}
