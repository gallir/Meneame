<?
// The source code packaged with this file is Free Software, Copyright (C) 2010 by
// Ricardo Galli <gallir at gmail dot com>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'comment.php');
include(mnminclude.'link.php');


$page_size = 50;
$comment = new Comment;

if (!isset($_REQUEST['id']) && $globals['base_comment_url'] && $_SERVER['PATH_INFO']) {
	$url_args = preg_split('/\/+/', $_SERVER['PATH_INFO']);
	array_shift($url_args); // The first element is always a "/"
	$comment->id = intval($url_args[0]);
} else {
	$url_args = preg_split('/\/+/', $_REQUEST['id']);
	$comment->id=intval($url_args[0]);
	if($comment->id > 0 && $globals['base_comment_url']) {
		// Redirect to the right URL if the link has a "semantic" uri
		header ('HTTP/1.1 301 Moved Permanently');
		header('Location: ' . $comment->get_relative_individual_permalink());
		die;
	}
}

if (!$comment->read()) {
	do_error(_('comentario no encontrado'), 404);
}

$link = new Link;
$link->id=$comment->link;
$link->read();

$globals['ads'] = true;
$globals['description'] = _('Autor') . ": $comment->username, " . _('Resumen') . ': '. text_sub_text($comment->content, 250);


do_header(_('comentario de') . ' ' . $comment->username . ' (' . $comment->id .') | men&eacute;ame');
/*** SIDEBAR ****/
echo '<div id="sidebar">';
do_banner_right();
//do_best_stories();
//do_best_comments();
do_banner_promotions();
echo '</div>' . "\n";
/*** END SIDEBAR ***/

echo '<div id="newswrap">'."\n";

echo '<h3><a href="'.$link->get_permalink().'">'. $link->title. '</a></h3>';

echo '<ol class="comments-list">';
$comment->print_summary($link, 10000, true);
echo "\n";
echo "</ol>\n";

// Print answers to the comment
$sql = "SELECT conversation_from as comment_id FROM conversations, comments WHERE conversation_type='comment' and conversation_to = $comment->id and comment_id = conversation_from ORDER BY conversation_from asc LIMIT $page_size";
$answers = $db->get_results($sql);
if ($answers) {
	$answer = new Comment;
	echo '<div style="padding-left: 40px; padding-top: 10px">'."\n";
	echo '<ol class="comments-list">';
	foreach ($answers as $dbanswer) {
		$answer->id = $dbanswer->comment_id;
		$answer->read();
		$answer->print_summary($link);
	}
	echo "</ol>\n";
	echo '</div>'."\n";
}


echo '</div>';
do_footer();
?>

