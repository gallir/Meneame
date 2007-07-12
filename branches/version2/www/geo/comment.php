<?
// The source code packaged with this file is Free Software, Copyright (C) 2007 by
// Ricardo Galli <gallir at uib dot es> and 
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('../config.php');
include(mnminclude.'comment.php');
header('Content-Type: text/html; charset=utf-8');


$id = intval($_GET['id']);
if (! $id > 0 ) die;
$comment = new Comment;
$comment->id=$id;
$comment->read();
if(!$comment->read) die;
echo '<div style="font-size:8.5pt;width:280px; margin-right:15px; overflow:hidden">';
if ($comment->avatar)
    echo '<img src="'.get_avatar_url($comment->author, $comment->avatar, 40).'" width="40" height="40" alt="avatar" style="float:left; margin: 0 5px 4px 0;"/>';
echo '<strong>' . $comment->username . '</strong><br/>';
echo put_smileys(text_to_summary($comment->content, 500));
echo '</div>';
?>
