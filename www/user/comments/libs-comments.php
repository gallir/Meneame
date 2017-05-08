<?php
defined('mnminclude') or die();

function print_comment_list($comments, $user)
{
    global $globals, $current_user;

    $comment = new Comment;
    $timestamp_read = 0;
    $last_link = 0;

    $ids = array();

    foreach ($comments as $dbcomment) {
        $comment = Comment::from_db($dbcomment->comment_id);

        // Don't show admin comment if it's her own profile.
        if ($comment->type === 'admin' && !$current_user->admin && $user->id == $comment->author) {
            continue;
        }

        if ($last_link != $dbcomment->link_id) {
            $link = Link::from_db($dbcomment->link_id, null, false); // Read basic

            echo '<h4>';
            echo '<a href="' . $link->get_permalink() . '">' . $link->title . '</a>';
            echo ' [' . $link->comments . ']';
            echo '</h4>';

            $last_link = $link->id;
        }

        if ($comment->date > $timestamp_read) {
            $timestamp_read = $comment->date;
        }

        echo '<ol class="comments-list">';
        echo '<li>';

        $comment->link_object = $link;
        $comment->print_summary(2000, false);

        echo '</li>';
        echo "</ol>\n";

        $ids[] = $comment->id;
    }

    Haanga::Load('get_total_answers_by_ids.html', array(
        'type' => 'comment',
        'ids' => implode(',', $ids),
    ));

    // Return the timestamp of the most recent comment
    return $timestamp_read;
}