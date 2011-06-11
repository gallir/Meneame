<?
// The source code packaged with this file is Free Software, Copyright (C) 2011 by
// Menéame and Ricardo Galli <gallir at gallir dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
//include('../config.php');
//include('common.php');
include(mnminclude.'html1.php');

if (! $current_user->user_id) {
	do_error(_('debe autentificarse'), 403);
}

$globals['extra_js'][] = 'jquery-form.pack.js';
$globals['extra_js'][] = 'autocomplete/jquery.autocomplete.min.js';
$globals['extra_css'][] = 'jquery.autocomplete.css';

$globals['ads'] = true;
if ($current_user->user_id) $globals['ads_branding'] = false;

$page_size = 50;
$offset=(get_current_page()-1)*$page_size;
$page_title = _('mensajes privados') . ' | '._('menéame');

switch ($argv[1]) {
	case 'sent':
		$where = "privates.user = $current_user->user_id";
		$order_by = "ORDER BY date desc";
		$limit = "LIMIT $offset,$page_size";
		$view = 1;
		break;
	default:
		$where = "privates.to = $current_user->user_id";
		$order_by = "ORDER BY date desc";
		$limit = "LIMIT $offset,$page_size";
		$view = 0;
}

$rows = $db->get_var("select count(*) from privates where $where");

do_header($page_title);
do_posts_tabs(5, $current_user->user_login);
$options = array(
	_('recibidos') => post_get_base_url('_priv'),
	_('enviados') => post_get_base_url('_priv').'/sent',

);
do_priv_subheader($options, $view);



/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
//do_best_stories();
if ($rows > 20) {
	do_best_posts();
	do_best_comments();
}
do_banner_promotions();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

$messages = $db->object_iterator("SELECT".PrivateMessage::SQL."$from WHERE $where $order_by $limit", 'PrivateMessage');
if ($messages) {
	echo '<ol class="comments-list">';
	foreach ($messages as $message) {
		if ( $message_id > 0 && $user->id > 0 && $user->id != $message->author) {
			echo '<li>'. _('Error: nota no existente') . '</li>';
		} else {
			echo '<li>';
			$message->print_summary();
			if (! $message->date_read && $message->to == $current_user->user_id) {
				$message->mark_read();
			}
			echo '</li>';
		}
	}
	echo "</ol>\n";

	do_pages($rows, $page_size);
}

echo '</div>';
do_footer();
exit(0);
?>
