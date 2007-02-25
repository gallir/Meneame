<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'user.php');
include(mnminclude.'post.php');
include(mnminclude.'html1.php');

array_push($globals['extra_js'], 'jquery-form.pack.js');

$user=new User();

if (!defined($_REQUEST['id']) && !empty($_SERVER['PATH_INFO'])) {
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
	$option = $url_args[1]; // The first element is always a "/"
} else {
	$url_args = preg_split('/\/+/', $_REQUEST['id']);
	$option = $url_args[0];
}

$min_date = date("Y-m-d H:00:00", time() - 192800); //  about 48 hours
switch ($option) {
	case '':
	case '_all':
		$tab_option = 1;
		$sql = "SELECT post_id FROM posts WHERE post_date > '$min_date' ORDER BY post_id desc limit 100";
		break;
	case '_friends':
		if ($current_user->user_id > 0) {
			$tab_option = 2;
			$sql = "SELECT post_id FROM posts, friends WHERE post_date > '$min_date' and friend_type='manual' and friend_from = $current_user->user_id and friend_to=post_user_id ORDER BY post_id desc limit 100";
		} else {
			$tab_option = 1;	
			$sql = "SELECT post_id FROM posts WHERE post_date > '$min_date' ORDER BY post_id desc limit 100";
		}
		break;
	default:
		$user->username = $db->escape($option);
		if(!$user->read()) {
			not_found();
		}
		$tab_option = 3;
		$sql = "SELECT post_id FROM posts WHERE post_date > '$min_date' and post_user_id=$user->id ORDER BY post_id desc limit 100";
}

$globals['ads'] = true;

do_header(_('Notame'));
do_banner_top();
echo '<div id="container">'."\n";
do_sidebar(false);
echo '<div id="contents">';
do_posts_tabs($tab_option, $user->username);

$post = new Post;

echo '<div class="comments">';

if ($current_user->user_id == $user->id && (!$post->read_last($current_user->user_id) || time() - $post->date > 900)) {
	$post->print_new_form();
}

$posts = $db->get_results($sql);
if ($posts) {
	echo '<ol class="comments-list">';
	foreach ($posts as $dbpost) {
		$post->id = $dbpost->post_id;
		$post->read();
		$post->print_summary();
	}
	echo "</ol>\n";
}

echo '</div>';
echo '</div>';
do_footer();

function do_posts_tabs($tab_selected, $username) {
	global $globals, $current_user;

	$reload_text = _('recargar');
	$active = ' class="tabmain-this"';

	echo '<ul class="tabmain">' . "\n";

	// All
	if ($tab_selected == 1) {
		echo '<li><a '.$active.' href="'.post_get_base_url().'" title="'.$reload_text.'">'._('todos').'&nbsp;&nbsp;&nbsp;'.$reload_icon.'</a></li>' . "\n";
	} else {
		echo '<li><a  href="'.post_get_base_url().'">'._('todos').'</a></li>' . "\n";
	}

	// Friends
	if ($tab_selected == 2) {
		echo '<li><a '.$active.' href="'.post_get_base_url('_friends').'" title="'.$reload_text.'">'._('amigos').'&nbsp;&nbsp;&nbsp;'.$reload_icon.'</a></li>' . "\n";
	} elseif ($current_user->user_id > 0) {
		echo '<li><a href="'.post_get_base_url('_friends').'">'._('amigos').'</a></li>' . "\n";
	}

	// User
	if ($tab_selected == 3) {
		echo '<li><a '.$active.' href="'.post_get_base_url($username).'" title="'.$reload_text.'">'.$username.'&nbsp;&nbsp;&nbsp;'.$reload_icon.'</a></li>' . "\n";
	} elseif ($current_user->user_id > 0) {
		echo '<li><a href="'.post_get_base_url($current_user->user_login).'">'.$current_user->user_login.'</a></li>' . "\n";
	}
	// END STANDARD TABS

	echo '</ul>' . "\n";
}

?>
