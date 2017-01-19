<?php
defined('config_done') or die();

switch ($tab_option) {
    case 1:
    case 2:
        echo '<div class="comments" id="comments-top">';

        if ($tab_option == 1) {
            print_external_analysis($link);

            if ($show_relevants || $no_page) {
                print_relevant_comments($link);
            }
        } else {
            $last_com_first = false;
        }

        print_story_tabs($tab_option);
        do_comment_pages($link->comments, $current_page, $last_com_first);

        $update_comments = false;
        $comments = $db->get_results("SELECT".Comment::SQL."WHERE comment_link_id=$link->id ORDER BY $order_field $limit", "Comment");

        if ($comments) {
            $order = $offset + 1;
            $prev = false;

            echo '<ol class="comments-list">';

            foreach($comments as $comment) {
                // Check the comment order is correct, otherwise, force an update
                if ($tab_option == 1) {
                    if ($comment->order != $order) {
                        if ($prev) {
                            $prev->update_order();
                        }

                        syslog(LOG_INFO, "Updating order for $comment->id, order: $comment->order -> $order");

                        $comment->update_order();
                        $update_comments = true;
                        $prev = false;
                    } else {
                        $prev = $comment;
                    }
                }

                echo '<li>';

                $comment->link_object = $link;
                $comment->print_summary(2500, true);

                echo '</li>';

                $order++;
            }

            echo '</ol>';
        }

        if (($tab_option == 1) && $update_comments) {
            $link->update_comments();
        }

        /* Force to show the last ad for anonymous users only */
        if (!$current_user->user_id) {
            $counter = $page_size = $globals['comments_page_size'];
            Haanga::Safe_Load('private/ad-interlinks.html', compact('counter', 'page_size'));
        }

        do_comment_pages($link->comments, $current_page, $last_com_first);

        if ($link->comments > 5) {
            add_javascript('get_total_answers("comment","'.$order_field.'",'.$link->id.','.$offset.','.$globals['comments_page_size'].');');
        }

        Comment::print_form($link);

        echo '</div>';

        break;

    case 3:
        print_story_tabs($tab_option);

        // Show voters
        echo '<div class="voters" id="voters">';
        echo '<div id="voters-container" style="padding: 10px;">';

        if ($globals['link']->sent_date < $globals['now'] - 60*86400) { // older than 60 days
            echo _('Noticia antigua, datos de votos archivados');
        } else {
            include(mnmpath.'/backend/meneos.php');
        }

        echo '</div><br />';
        echo '</div>';

        break;

    case 6:
        print_story_tabs($tab_option);

        // Show favorited by
        echo '<div class="voters" id="voters">';
        echo '<div id="voters-container">';

        include(mnmpath.'/backend/get_link_favorites.php');

        echo '</div><br />';
        echo '</div>';

        break;

    case 4:
        print_story_tabs($tab_option);
        // Show logs

        $globals['extra_js'][] = 'jquery.flot.min.js';
        $globals['extra_js'][] = 'jquery.flot.time.min.js';

        $logs = $db->get_results("select logs.*, UNIX_TIMESTAMP(logs.log_date) as ts, user_id, user_login, user_level, user_avatar from logs, users where log_type in ('link_new', 'link_publish', 'link_discard', 'link_edit', 'link_geo_edit', 'link_depublished') and log_ref_id=$link->id and user_id= log_user_id order by log_date desc");

        foreach ($logs as $log) {
            $log->annotation = Log::has_annotation($log->log_id);
        }

        // Show karma logs from annotations
        $annotations = $link->read_annotation("link-karma");

        Haanga::Load("story/link_logs.html", compact('link', 'logs', 'annotations'));
        break;

    case 5:
        print_story_tabs($tab_option);

        // Micro sneaker
        Haanga::Load('story/link_sneak.html', compact('link'));
        break;

    case 8:
        print_story_tabs($tab_option);

        $related = $link->get_related(10);

        if ($related) {
            Haanga::Load("story/related.html", compact('related', 'link'));
        }

        break;

    case 9:
        print_story_tabs($tab_option);

        echo '<div class="comments" id="comments-top">';

        $sql = "SELECT conversation_to as id, count(*) as t FROM conversations, comments WHERE comment_link_id = $link->id AND comment_id = conversation_to AND conversation_type='comment' GROUP BY conversation_to ORDER BY t desc, id asc LIMIT ".$globals['comments_page_size'] ;

        $results = $db->get_results($sql);

        if ($results) {
            echo '<ol class="comments-list">';

            $ids = array();
            $max = 0;

            foreach ($results as $res) {
                if ($res->t > $max) {
                    $max = $res->t;
                }

                if ($max > 1 && $res->t < 2) {
                    break;
                }

                $ids[] = $res->id;
                $comment = Comment::from_db($res->id);

                echo '<li>';

                $comment->link_object = $link;
                $comment->print_summary(2500, true);

                echo '</li>';
            }

            echo '</ol>';

            Haanga::Load('get_total_answers_by_ids.html', array('type' => 'comment', 'ids' => implode(',', $ids)));

            Comment::print_form($link);
        }

        echo '</div>';

        break;

    /////////////// TODO: in progress
    case 10:
        echo '<div class="comments" id="comments-top">';

        include_once(mnminclude.'commenttree.php');

        $tree = new CommentTree();

        if (!$current_page) {
            $current_page = 1;
        }

        $offset = ($current_page - 1) * $globals['comments_page_size'];
        $limit = $globals['comments_page_size'];
        $global_limit = $limit * 2; // The limit including references

        if ($show_relevants || $no_page) {
            print_external_analysis($link);
            print_relevant_comments($link);
        }

        print_story_tabs($tab_option);

        if ($link->page_mode === 'interview') {
            $sql = "select t1.comment_id as parent, t1.w1 as w1, t2.comment_id as child, t2.comment_karma + 200 * (t2.comment_user_id = $link->author) as w2 FROM comments as t0 INNER JOIN (select comment_id, comment_karma + 200 * (comment_user_id = $link->author) as w1 from comments WHERE comment_link_id = $link->id order by w1 desc LIMIT $offset, $limit) t1 ON t1.comment_id = t0.comment_id LEFT JOIN (conversations as c, comments as t2) ON conversation_type='comment' and conversation_to = t0.comment_id and c.conversation_from = t2.comment_id order by w1 desc, w2 desc LIMIT $global_limit";

            $res = $db->get_results($sql);

            if ($res) {
                foreach ($res as $c) {
                    $tree->addByIds($c->parent, $c->child);
                }
            }

            $sort_roots = true;
        } else {
            $sql = "select t1.comment_id as parent, c.conversation_from as child FROM comments as t0 INNER JOIN (select comment_id from comments WHERE comment_link_id = $link->id order by comment_id asc LIMIT $offset, $limit) t1 ON t1.comment_id = t0.comment_id LEFT JOIN conversations c ON c.conversation_type='comment' and c.conversation_to = t0.comment_id order by t0.comment_id, c.conversation_from LIMIT $global_limit";
            $res = $db->get_results($sql);

            if ($res) {
                foreach ($res as $c) {
                    $tree->addByIds($c->parent, $c->child);
                }
            }

            $sort_roots = false;
        }

        // A /url/c0#comment_order all, we add it
        if (!empty($globals['referenced_comment'])) {
            $order = intval($globals['referenced_comment']);
            $pair = $db->get_row("select comment_id as child, conversation_to as parent FROM comments LEFT JOIN (conversations) ON conversation_type='comment' and conversation_from = comment_id WHERE comment_link_id = $link->id and comment_order = $order");

            if ($pair) {
                $tree->addByIds($pair->parent, $pair->child);
            }
        }

        Comment::print_tree($tree, $link, 500, $sort_roots);

        /* Force to show the last ad for anonymous users only */
        if (! $current_user->user_id) {
            $counter = $page_size = $globals['comments_page_size'];
            Haanga::Safe_Load('private/ad-interlinks.html', compact('counter', 'page_size'));
        }

        do_comment_pages($link->comments, $current_page, false);

        Comment::print_form($link);

        echo '</div>';

        break;
}