<?php
defined('mnminclude') or die();

$rows = -1; // $db->get_var("SELECT count(*) FROM comments WHERE comment_user_id=$user->id");
$comments = $db->get_results("SELECT comment_id, link_id, comment_type FROM comments, links WHERE comment_user_id=$user->id and link_id=comment_link_id ORDER BY comment_date desc LIMIT $offset,$page_size");

if (empty($comments)) {
    return;
}

require __DIR__.'/libs-comments.php';

print_comment_list($comments, $user);
