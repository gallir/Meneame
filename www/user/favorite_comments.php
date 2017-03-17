<?php
defined('mnminclude') or die();

$comment = new Comment;
$rows = $db->get_var("SELECT count(*) FROM favorites WHERE favorite_user_id=$user->id AND favorite_type='comment'");
$comments = $db->get_col("SELECT comment_id FROM comments, favorites WHERE favorite_user_id=$user->id AND favorite_type='comment' AND favorite_link_id=comment_id ORDER BY comment_id DESC LIMIT $offset,$page_size");

if (empty($comments)) {
    return;
}

echo '<ol class="comments-list">';

foreach ($comments as $comment_id) {
    echo '<li>';
    Comment::from_db($comment_id)->print_summary(2000, false);
    echo '</li>';
}

echo "</ol>\n";
