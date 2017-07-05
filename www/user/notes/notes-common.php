<?php
defined('mnminclude') or die();

if (empty($posts)) {
    return Haanga::Load('user/empty.html');
}

if ($current_user->user_id > 0) {
    $post = new Post;
    $post->author = $current_user->user_id;
    $post->print_edit_form();
}

$all_ids = array_map(function ($value) {
    return $value->id;
}, $posts);

$pollCollection = new PollCollection;
$pollCollection->loadFromRelatedIds('post_id', $all_ids);

$ids = array();

echo '<ol class="comments-list">';

$time_read = 0;

foreach ($posts as $post) {
    echo '<li>';

    $post->poll = $pollCollection->get($post->id);
    $post->print_summary();

    if ($view == 'notes_votes') {
        if ($post->value > 0){
            echo '<i class="fa fa-arrow-circle-up" style="color:green;position:relative;top: -29px;"></i>';
        } else {
            echo '<i class="fa fa-arrow-circle-down" style="color:red;position:relative;top: -29px;"></i>';
        }
    }

    echo '</li>';

    if ($post->date > $time_read) {
        $time_read = $post->date;
    }

    $ids[] = $post->id;
}

echo "</ol>\n";

Haanga::Load('get_total_answers_by_ids.html', array(
    'type' => 'post',
    'ids' => implode(',', $ids),
));

do_pages($count, $limit);
