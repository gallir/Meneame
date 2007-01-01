<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'link.php');
include(mnminclude.'comment.php');

$globals['ads'] = true;

do_header(_('mejores comentarios en 24 horas'));
do_navbar(_('comentarios más valorados') . ' &#187; ' . _('estadísticas'));
do_sidebar_top();
echo '<div id="contents">';
do_tabs('main');
echo '<div class="topheading"><h2>'._('comentarios más valorados 24 horas').'</h2></div>';


$last_link = 0;
$counter = 0;
$comment = new Comment;
$link = new Link;

echo '<div class="comments">';

$comments = $db->get_results("SELECT comment_id, link_id FROM comments, links WHERE comment_date > date_sub(now(), interval 24 hour) and link_id=comment_link_id ORDER BY comment_karma desc, link_id asc limit 25");
if ($comments) {
	foreach ($comments as $dbcomment) {
		$link->id=$dbcomment->link_id;
		$comment->id = $dbcomment->comment_id;
		$link->read();
		$comment->read();
		if ($last_link != $link->id) {
			if ($counter % 12 == 5)  // AdSense
				do_banner_story();
			//$link->print_summary('short');
			echo '<h3>';
			echo '<a href="'.$link->get_permalink().'">'. $link->title. '</a>';
			echo '</h3>';
		}
			echo '<ol class="comments-list">';
		$comment->print_summary($link, 2000, false);
		if ($last_link != $link->id) {
			$last_link = $link->id;
			$counter++;
		}
		echo "</ol>\n";
	}
}

echo '</div>';
echo '</div>';

do_footer();


function do_sidebar_top() {
	global $db, $dblang, $range_values, $range_names;

	echo '<div id="sidebar">'."\n";
	do_mnu_faq('topcomments');
	do_mnu_submit();
	do_mnu_sneak();
	do_mnu_meneria();
	do_mnu_rss();
	echo '</div>';

}

?>
