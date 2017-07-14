<?php
defined('mnminclude') or die();

$query = '
    FROM posts, votes
    WHERE (
        vote_user_id = "'.(int)$user->id.'"
        AND vote_type = "posts"
        AND post_id = vote_link_id
        AND post_user_id != vote_user_id
    )
';

$count = $db->get_var('SELECT SQL_CACHE COUNT(*) '.$query.';');

if ($count === 0) {
    return Haanga::Load('user/empty.html');
}

$posts = $db->get_results('
    SELECT SQL_CACHE vote_link_id AS id, vote_value AS value
    '.$query.'
    ORDER BY vote_date DESC
    LIMIT '.$offset.', '.$limit.';
');

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

foreach ($posts as $p) {
    echo '<li>';
    $post = Post::from_db($p->id);
    $post->poll = $pollCollection->get($post->id);
    $post->print_summary();

    if ($view == 'notes_votes') {
        if ($p->value > 0){
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

