<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

$page_size = $globals['page_size'];
$current_page = get_current_page();
$offset=($current_page-1)*$page_size;

if (!$current_user->user_id) {
	header("Location: " . $globals['base_url']);
	die;
}


$friends = $db->get_col("select friend_to from friends where friend_type = 'manual' and friend_from = $current_user->user_id and friend_value > 0");
if ($friends) {
	$friends_list = implode(',', $friends);
	$sql = "select distinct vote_link_id as link_id from votes where vote_type = 'links' and vote_user_id in ($friends_list) and vote_value > 0 order by vote_link_id desc";

	$links = $db->get_results("$sql LIMIT $offset,$page_size");
}

do_header(_('votadas por amigos') . ' | ' . _('menéame'));
$globals['tag_status'] = 'published';
do_tabs('main', 'friends');

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_banner_promotions();
do_best_comments();
do_vertical_tags('published');
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

if ($links) {
	foreach($links as $dblink) {
		$link = Link::from_db($dblink->link_id);
		$link->do_inline_friend_votes = true;
		$link->print_summary();
	}
}
do_pages(-1, $page_size);
echo '</div>'."\n";

do_footer_menu();
do_footer();

?>
