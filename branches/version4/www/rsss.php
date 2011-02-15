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

$rows = -1;
$page_size = 25;
$page = get_current_page();
$offset=($page-1)*$page_size;

do_header(_('apuntes de blogs') . ' | ' . _('menéame'));
do_tabs('main', _('apuntes'), true);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_best_stories();
do_best_posts();
do_best_comments();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

echo '<div class="topheading"><h2>'._('apuntes de blogs').'</h2></div>';
echo '<table class="decorated"><tr>';


$entries = $db->get_results("select rss.blog_id, rss.user_id, title, url, user_login, user_avatar, blogs.blog_url, blogs.blog_title from rss, users, blogs where rss.blog_id = blogs.blog_id and rss.user_id = users.user_id order by rss.date_parsed desc limit $offset,$page_size");

if ($entries) {
	foreach ($entries as $entry) {
		$title = strip_tags($entry->title);
		$url = clean_input_string($entry->url);
		$blog_title = strip_tags($entry->blog_title);

		echo '<tr>';
		echo '<td style="width:45px"><a href="'.get_user_uri($entry->user_login).'" class="tooltip u:'.$entry->user_id.'"><img class="avatar" src="'.get_avatar_url($entry->user_id, $entry->user_avatar, 40).'" width="40" height="40" alt="avatar"/></a></td>';
		echo '<td style="font-size:110%;width:30%"><a href="'.$entry->blog_url.'" rel="nofollow">'.$blog_title.'</a></td>';
		echo '<td style="font-size:120%"><a href="'.$url.'" rel="nofollow">'.$title.'</a></td>';
		echo '</tr>';
	}

}
echo '</table>';

do_pages($rows, $page_size);
echo '</div>';
do_footer_menu();
do_footer();

?>
