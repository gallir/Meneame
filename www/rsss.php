<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

$globals['ads'] = false;

$rows = -1;
$page_size = $globals['page_size'];
$page = get_current_page();
$offset=($page-1)*$page_size;

$globals['extra_head'] .= '<link rel="alternate" type="application/rss+xml" title="'._('blogs de Menéame').'" href="http://'.get_server_name().$globals['base_url_general'].'blogs_rss2.php" />'."\n";
do_header(_('apuntes de blogs') . ' | ' . _('menéame'));
do_tabs('main', _('apuntes'), true);

/*** SIDEBAR 
echo '<div id="sidebar">';
do_banner_right();
do_best_stories();
do_best_posts();
do_best_comments();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="singlewrap">'."\n";

echo '<div style="margin: 20px 0"><h2>'._('apuntes de blogs').'&nbsp;&nbsp;<a href="'.$globals['base_url_general'].'blogs_rss2.php" title="blogs"><img src="'.$globals['base_static'].'img/common/feed-icon-001.png" width="18" height="18" alt="rss2"/></a></h2>';
echo '</div>';
echo '<table class="decorated">';


$entries = $db->get_results("select rss.blog_id, rss.user_id, title, url, user_login, user_avatar, blogs.blog_url, blogs.blog_title from rss, users, blogs where rss.blog_id = blogs.blog_id and rss.user_id = users.user_id order by rss.date desc limit $offset,$page_size");

if ($entries) {
	foreach ($entries as $entry) {
		$title = strip_tags($entry->title);
		$url = clean_input_string($entry->url);
		$blog_title = strip_tags($entry->blog_title);

		echo '<tr>';
		echo '<td style="width:35px"><a href="'.get_user_uri($entry->user_login).'" class="tooltip u:'.$entry->user_id.'"><img class="avatar" src="'.get_avatar_url($entry->user_id, $entry->user_avatar, 25).'" width="25" height="25" alt="avatar"/></a></td>';
		echo '<td style="font-size:110%;width:30%"><a href="'.$entry->blog_url.'" rel="nofollow">'.$blog_title.'</a></td>';
		echo '<td style="font-size:120%"><a href="'.$url.'" rel="nofollow">'.$title.'</a></td>';
		echo '</tr>';
	}

}
echo '</table>';
echo '<fieldset id="nota"><legend>'._('nota').'</legend>';
echo _('Los enlaces son de apuntes de blogs indicados en el perfil de usuarios activos de Menéame.');
echo ' ';
echo _('No tienen relación con meneame.net, ni han sido seleccionados por su comunidad de usuarios.');
echo '</fieldset>';

do_pages($rows, $page_size);
echo '</div>';
do_footer_menu();
do_footer();

