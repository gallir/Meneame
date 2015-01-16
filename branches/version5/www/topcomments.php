<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');

do_header(_('mejores comentarios en 24 horas') . ' | ' . $globals['site_name']);
do_tabs('main', '+ ' . _('comentarios'), true);

/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
do_best_stories();
do_best_comments();
do_vertical_tags('published');
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";


echo '<div class="topheading"><h2>'._('comentarios más valorados 24 horas').'</h2></div>';


$last_link = 0;
$counter = 0;

echo '<div class="comments">';

$min_date = date("Y-m-d H:00:00", time() - 86000); //  about 24 hours
$comments = $db->get_results("SELECT comment_id, link_id FROM comments, links WHERE comment_date > '$min_date' and link_id=comment_link_id ORDER BY comment_karma desc, link_id asc limit 25");
if ($comments) {
	foreach ($comments as $dbcomment) {
		$link = Link::from_db($dbcomment->link_id, null, false);
		$comment = Comment::from_db($dbcomment->comment_id);
		if ($last_link != $link->id) {
			echo '<h3>';
			echo '<a href="'.$link->get_relative_permalink().'">'. $link->title. '</a>';
			echo '</h3>';
		}
		echo '<ol class="comments-list">';
		echo '<li>';
		$comment->link_object = $link;
		$comment->print_summary(2000, false);
		echo '</li>';
		if ($last_link != $link->id) {
			$last_link = $link->id;
			$counter++;
		}
		echo "</ol>\n";
	}
}

echo '</div>';
echo '</div>';
do_footer_menu();
do_footer();

?>
