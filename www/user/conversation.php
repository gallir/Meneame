<?php
defined('mnminclude') or die();

$rows = -1; //$db->get_var("SELECT count(distinct(conversation_from)) FROM conversations WHERE conversation_user_to=$user->id and conversation_type='comment'");
$conversation = "SELECT distinct(conversation_from) FROM conversations WHERE conversation_user_to=$user->id and conversation_type='comment' ORDER BY conversation_time desc LIMIT $offset,$page_size";

$comments = $db->get_results("SELECT comment_id, link_id, comment_type FROM comments INNER JOIN links ON (link_id = comment_link_id) INNER JOIN ($conversation) AS convs ON convs.conversation_from = comments.comment_id");

require __DIR__.'/libs-comments.php';

if ($comments) {
    $last_read = print_comment_list($comments, $user);
}

if ($last_read > 0 && $current_user->user_id == $user->id) {
    Comment::update_read_conversation($timestamp_read);
}
