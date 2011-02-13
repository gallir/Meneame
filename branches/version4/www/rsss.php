<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

$globals['ads'] = true;

$rows = 1000;
$page_size = 50;
$page = get_current_page();
$offset=($page-1)*$page_size;

do_header(_('apuntes de blogs') . ' | ' . _('menéame'));
do_tabs('main', _('apuntes de blogs'), true);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_best_stories();
do_best_comments();
do_vertical_tags('published');
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";


echo '<div class="topheading"><h2>'._('apuntes').'</h2></div>';

$entries = $db->get_results("select rss.blog_id, rss.user_id, title, url, user_login, user_avatar, blogs.blog_url from rss, users, blogs where rss.blog_id = blogs.blog_id and rss.user_id = users.user_id order by rss.date desc limit $offset,$page_size");

if ($entries) {
	//echo "<ul>";
	foreach ($entries as $entry) {
		echo '<h3><a href="'.get_user_uri($entry->user_login).'" class="tooltip u:'.$entry->user_id.'"><img class="avatar" src="'.get_avatar_url($entry->user_id, $entry->user_avatar, 25).'" width="25" height="25" alt="avatar"/></a>&nbsp;';
		echo "<a href='$entry->url' rel='nofollow'>$entry->title</a></h3><br/>";
	}
	//echo "</ul>";

}


do_pages($rows, $page_size);
echo '</div>';
do_footer_menu();
do_footer();

?>
