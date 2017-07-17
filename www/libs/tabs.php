<?php

final class Tabs
{
    public static function renderForSection($section, $options, $tab_class = null)
    {
        switch ($section) {
            case _('portada'):
            case _('post'):
                return self::renderForIndex($options, $tab_class);

            case _('nuevas'):
                return self::renderForShakeIt($options, $tab_class);

            case _('populares'):
                return self::renderForTopStories($options, $tab_class);

            case _('más visitadas'):
                return self::renderForTopclicked($options, $tab_class);

            case _('nótame'):
                return self::renderForSneakme($options, $tab_class);
        }
    }

    public static function renderFromProfile(array $options, $tab_class = null)
    {
        switch ($options['view']) {
            case 'articles':
                return self::renderForProfileArticles($options, $tab_class);

            case 'articles_private':
                return self::renderForProfileArticlesPrivate($options, $tab_class);

            case 'profile':
                return self::renderForProfileProfile($options, $tab_class);

            case 'friends':
                return self::renderForProfileFriends($options, $tab_class);

            case 'friend_of':
                return self::renderForProfileFriendsOf($options, $tab_class);

            case 'ignored':
                return self::renderForProfileIgnored($options, $tab_class);

            case 'friends_new':
                return self::renderForProfileFriendsNew($options, $tab_class);

            case 'history':
                return self::renderForProfileHistory($options, $tab_class);

            case 'shaken':
                return self::renderForProfileShaken($options, $tab_class);

            case 'favorites':
                return self::renderForProfileFavorites($options, $tab_class);

            case 'friends_shaken':
                return self::renderForProfileFriendsShaken($options, $tab_class);

            case 'commented':
                return self::renderForProfileCommented($options, $tab_class);

            case 'conversation':
                return self::renderForProfileConversation($options, $tab_class);

            case 'shaken_comments':
                return self::renderForProfileShakenComments($options, $tab_class);

            case 'favorite_comments':
                return self::renderForProfileFavoriteComments($options, $tab_class);
        }
    }

    public static function optionsFromProfile($view)
    {
        switch ($view) {
            case 'articles':
                return self::optionsForProfileArticles();

            case 'articles_private':
                return self::optionsForProfileArticlesPrivate();

            case 'articles_shaken':
                return self::optionsForProfileArticlesShaken();

            case 'articles_favorites':
                return self::optionsForProfileArticlesFavorites();

            case 'articles_discard':
                return self::optionsForProfileArticlesDiscard();

            case 'friends':
                return self::optionsForProfileFriends();

            case 'friend_of':
                return self::optionsForProfileFriendsOf();

            case 'ignored':
                return self::optionsForProfileIgnored();

            case 'friends_new':
                return self::optionsForProfileFriendsNew();

            case 'history':
                return self::optionsForProfileHistory();

            case 'shaken':
                return self::optionsForProfileShaken();

            case 'favorites':
                return self::optionsForProfileFavorites();

            case 'friends_shaken':
                return self::optionsForProfileFriendsShaken();

            case 'discard':
                return self::optionsForProfileDiscard();

            case 'commented':
                return self::optionsForProfileCommented();

            case 'conversation':
                return self::optionsForProfileConversation();

            case 'shaken_comments':
                return self::optionsForProfileShakenComments();

            case 'favorite_comments':
                return self::optionsForProfileFavoriteComments();

            case 'subs':
                return self::optionsForProfileSubs();

            case 'subs_follow':
                return self::optionsForProfileSubsFollow();

            case 'notes':
                return self::optionsForProfileNotes();

            case 'notes_friends':
                return self::optionsForProfileNotesFriends();

            case 'notes_favorites':
                return self::optionsForProfileNotesFavorites();

            case 'notes_conversation':
                return self::optionsForProfileNotesConversation();

            case 'notes_votes':
                return self::optionsForProfileNotesVotes();

            case 'notes_privates':
                return self::optionsForProfileNotesPrivates();
        }

        return array();
    }

    public static function renderForIndex($option, $tab_class)
    {
        global $globals, $current_user;

        if (($globals['mobile'] && !$current_user->has_subs) || (!empty($globals['submnm']) && !$current_user->user_id)) {
            return;
        }

        $items = array();
        $items[] = array('id' => 0, 'url' => $globals['meta_skip'], 'title' => _('todas'));

        if (isset($current_user->has_subs)) {
            $items[] = array('id' => 7, 'url' => $globals['meta_subs'], 'title' => _('suscripciones'));
        }

        if (!$globals['mobile'] && empty($globals['submnm']) && ($subs = SitesMgr::get_sub_subs())) {
            foreach ($subs as $sub) {
                $items[] = array(
                    'id' => 9999, /* fake number */
                    'url' => 'm/' . $sub->name,
                    'selected' => false,
                    'title' => $sub->name,
                );
            }
        }

        $items[] = array('id' => 8, 'url' => '?meta=_*', 'title' => _('m/*'));

        // RSS teasers
        switch ($option) {
            case 7: // Personalised, published
                $feed = array("url" => "?subs=" . $current_user->user_id, "title" => _('suscripciones'));
                break;

            default:
                $feed = array("url" => '', "title" => "");
                break;
        }

        if ($current_user->user_id > 0) {
            $items[] = array('id' => 1, 'url' => '?meta=_friends', 'title' => _('amigos'));
        }

        return Haanga::Load('print_tabs.html', compact('items', 'option', 'feed', 'tab_class'), true);
    }

    public static function renderForShakeIt($option = -1, $tab_class)
    {
        global $globals, $current_user;

        $items = array();
        $items[] = array('id' => 1, 'url' => 'queue' . $globals['meta_skip'], 'title' => _('todas'));

        if ($current_user->has_subs) {
            $items[] = array('id' => 7, 'url' => 'queue' . $globals['meta_subs'], 'title' => _('suscripciones'));
        }

        if (empty($globals['submnm']) && !$globals['mobile']) {
            foreach (SitesMgr::get_sub_subs() as $sub) {
                $items[] = array(
                    'id' => 9999, /* fake number */
                    'url' => 'm/' . $sub->name . '/queue',
                    'selected' => false,
                    'title' => $sub->name
                );
            }
        }

        $items[] = array('id' => 8, 'url' => 'queue?meta=_*', 'title' => _('m/*'));
        $items[] = array('id' => 3, 'url' => 'queue?meta=_popular', 'title' => _('candidatas'));

        if ($current_user->user_id > 0) {
            $items[] = array('id' => 2, 'url' => 'queue?meta=_friends', 'title' => _('amigos'));
        }

        if (!$globals['bot']) {
            $items [] = array('id' => 5, 'url' => 'queue?meta=_discarded', 'title' => _('descartadas'));
        }

        // Print RSS teasers
        if (!$globals['mobile']) {
            switch ($option) {
                case 7: // Personalised, queued
                    $feed = array("url" => "?status=queued&amp;subs=" . $current_user->user_id, "title" => "");
                    break;

                default:
                    $feed = array("url" => "?status=queued", "title" => "");
                    break;
            }
        }

        return Haanga::Load('print_tabs.html', compact('items', 'option', 'feed', 'tab_class'), true);
    }

    public static function renderForTopstories($options, $tab_class)
    {
        global $range_values, $range_names, $month, $year;

        $count_range_values = count($range_values);

        $html = '<ul class="' . $tab_class . '">' . "\n";

        if (!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= $count_range_values) {
            $current_range = 0;
        }

        if ($month > 0 && $year > 0) {
            $html .= '<li class="selected"><a href="popular?month=' . $month . '&amp;year=' . $year . '">' . "$month-$year" . '</a></li>' . "\n";
            $current_range = -1;
        } elseif (!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= $count_range_values) {
            $current_range = 0;
        }

        for ($i = 0; $i < $count_range_values; $i++) {
            if ($i == $current_range) {
                $active = ' class="selected"';
            } else {
                $active = "";
            }

            $html .= '<li' . $active . '><a href="popular?range=' . $i . '">' . $range_names[$i] . '</a></li>' . "\n";
        }

        $html .= '</ul>' . "\n";

        return $html;
    }

    public static function renderForTopclicked($options, $tab_class)
    {
        global $range_values, $range_names;

        $count_range_values = count($range_values);

        $html = '<ul class="' . $tab_class . '">' . "\n";

        if (!($current_range = check_integer('range')) || $current_range < 1 || $current_range >= $count_range_values) {
            $current_range = 0;
        }

        for ($i = 0; $i < $count_range_values; $i++) {
            if ($i == $current_range) {
                $active = ' class="selected"';
            } else {
                $active = "";
            }

            $html .= '<li' . $active . '><a href="top_visited?range=' . $i . '">' . $range_names[$i] . '</a></li>' . "\n";
        }

        $html .= '</ul>' . "\n";

        return $html;
    }

    public static function renderForSneakme($options, $tab_class)
    {
        global $globals, $current_user;

        list($content, $selected, $rss, $rss_title) = $options;

        $html = '';

        // arguments: hash array with "button text" => "button URI"; Nº of the selected button
        $html .= '<ul class="' . $tab_class . '">' . "\n";

        if (is_array($content)) {
            $n = 0;
            foreach ($content as $text => $url) {
                if ($selected === $n) {
                    $class_b = ' class = "selected"';
                } else {
                    $class_b = ($n > 4) ? ' class="wideonly"' : '';
                }

                $html .= '<li' . $class_b . '>' . "\n";
                $html .= '<a href="' . $url . '">' . $text . "</a>\n";
                $html .= '</li>' . "\n";
                $n++;
            }
        } elseif (!empty($content)) {
            $html .= '<li>' . $content . '</li>';
        }

        if ($rss && !empty($content)) {
            if (!$rss_title) {
                $rss_title = 'rss2';
            }
        }

        $html .= '<li class="icon wideonly"><a href="' . $globals['base_url'] . $rss . '" title="' . $rss_title . '"><i class="fa fa-rss-square"></i> RSS</a></li>';
        $html .= '</ul>' . "\n";

        return $html;
    }

    public static function renderUserProfileSubheader($options, $selected = false, $rss = false, $rss_title = '', $tab_class)
    {
        global $user, $current_user;

        if ($current_user->user_id > 0 && $user->id != $current_user->user_id) { // Add link to discussion among them
            $between = 'type=comments&amp;u1='.$user->username.'&amp;u2='.$current_user->user_login;
        } else {
            $between = false;
        }

        return Haanga::Load('user/subheader.html', compact(
            'options', 'selected', 'rss', 'rss_title', 'between', 'tab_class'
        ), true);
    }

    public static function renderForProfileProfile($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileProfile(), 0, 'rss?friends_of=' . $user->id, _('enví­os de amigos en rss2'), $tab_class);
    }

    public static function optionsForProfileProfile()
    {
        global $user, $current_user, $globals;

        $options = array(
            'profile' => array(
                'title' => $user->username,
                'link' => $user->get_uri()
            )
        );

        if ($current_user->user_id == $user->id || ($current_user->user_level === 'god')) {
            $options['edit'] = array(
                'title' => _('modificar perfil'),
                'link' => $globals['base_url'].'profile?login='.urlencode($params['login'])
            );
        }

        return $options;
    }

    public static function renderForProfileArticles($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileArticles(), 0, false, '', $tab_class);
    }

    public static function optionsForProfileArticles()
    {
        global $user;

        if ($discard = Link::userArticlesDraft($user)) {
            $discard = __('Borradores (%s)', $discard);
        } else {
            $discard = __('Borradores');
        }

        return array(
            'articles' => array(
                'title' => _('Públicos'),
                'link' => $user->get_uri('articles'),
            ),
            'articles_private' => array(
                'title' => _('Privados'),
                'link' => $user->get_uri('articles_private'),
            ),
            'articles_shaken' => array(
                'title' => _('Votados'),
                'link' => $user->get_uri('articles_shaken'),
            ),
            'articles_favorites' => array(
                'title' => _('Favoritos'),
                'link' => $user->get_uri('articles_favorites'),
            ),
            'articles_discard' => array(
                'title' => $discard,
                'link' => $user->get_uri('articles_discard'),
            ),
        );
    }

    public static function renderForProfileArticlesPrivate($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileArticlesPrivate(), 1, false, '', $tab_class);
    }

    public static function optionsForProfileArticlesPrivate()
    {
        return self::optionsForProfileArticles();
    }

    public static function renderForProfileArticlesShaken($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileArticlesShaken(), 2, false, '', $tab_class);
    }

    public static function optionsForProfileArticlesShaken()
    {
        return self::optionsForProfileArticles();
    }

    public static function renderForProfileArticlesFavorites($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileArticlesFavorites(), 3, false, '', $tab_class);
    }

    public static function optionsForProfileArticlesFavorites()
    {
        return self::optionsForProfileArticles();
    }

    public static function renderForProfileArticlesDiscard($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileArticlesDiscard(), 4, false, '', $tab_class);
    }

    public static function optionsForProfileArticlesDiscard()
    {
        return self::optionsForProfileArticles();
    }

    public static function renderForProfileFriends($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileFriends(), 0, 'rss?friends_of=' . $user->id, _('enví­os de amigos en rss2'), $tab_class);
    }

    public static function optionsForProfileFriends()
    {
        global $user, $current_user;

        $options = array(
            'friends' => array(
                'title' => _('Amigos'),
                'link' => $user->get_uri('friends'),
            ),
            'friend_of' => array(
                'title' => _('Elegido por'),
                'link' => $user->get_uri('friend_of'),
            )
        );

        if ($user->id == $current_user->user_id) {
            $options['ignored'] = array(
                'title' => _('Ignorados'),
                'link' => $user->get_uri('ignored'),
            );

            $options['friends_new'] = array(
                'title' => _('Nuevos'),
                'link' => $user->get_uri('friends_new'),
            );
        }

        return $options;
    }

    public static function renderForProfileFriendsOf($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileFriendsOf(), 1, false, '', $tab_class);
    }

    public static function optionsForProfileFriendsOf()
    {
        return self::optionsForProfileFriends();
    }

    public static function renderForProfileIgnored($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileIgnored(), 2, false, '', $tab_class);
    }

    public static function optionsForProfileIgnored()
    {
        return self::optionsForProfileFriends();
    }

    public static function renderForProfileFriendsNew($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileFriendsNew(), 3, false, '', $tab_class);
    }

    public static function optionsForProfileFriendsNew()
    {
        return self::optionsForProfileFriends();
    }

    public static function renderForProfileHistory($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileHistory(), 0, 'rss?sent_by=' . $user->id, _('envíos en rss2'), $tab_class);
    }

    public static function optionsForProfileHistory()
    {
        global $user;

        return array(
            'history' => array(
                'title' => _('Envíos'),
                'link' => $user->get_uri('history'),
            ),
            'shaken' => array(
                'title' => _('Votadas'),
                'link' => $user->get_uri('shaken'),
            ),
            'favorites' => array(
                'title' => _('Favoritas'),
                'link' => $user->get_uri('favorites'),
            ),
            'friends_shaken' => array(
                'title' => _('Votadas por amigos'),
                'link' => $user->get_uri('friends_shaken'),
            ),
            'discard' => array(
                'title' => _('Borradores'),
                'link' => $user->get_uri('discard'),
            ),
        );
    }

    public static function renderForProfileShaken($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileShaken(), 1, 'rss?voted_by=' . $user->id, _('votadas en rss2'), $tab_class);
    }

    public static function optionsForProfileShaken()
    {
        return self::optionsForProfileHistory();
    }

    public static function renderForProfileFavorites($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileFavorites(), 2, 'rss?voted_by=' . $user->id, _('votadas en rss2'), $tab_class);
    }

    public static function optionsForProfileFavorites()
    {
        return self::optionsForProfileHistory();
    }

    public static function renderForProfileFriendsShaken($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileFriendsShaken(), 3, false, '', $tab_class);
    }

    public static function optionsForProfileFriendsShaken()
    {
        return self::optionsForProfileHistory();
    }

    public static function renderForProfileDiscard($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileDiscard(), 3, false, '', $tab_class);
    }

    public static function optionsForProfileDiscard()
    {
        return self::optionsForProfileHistory();
    }

    public static function renderForProfileCommented($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileCommented(), 0, 'comments_rss?user_id=' . $user->id, _('comentarios en rss2'), $tab_class);
    }

    public static function optionsForProfileCommented()
    {
        global $user, $globals;

        return array(
            'commented' => array(
                'title' => _('Realizados'),
                'link' => $user->get_uri('commented'),
            ),
            'conversation' => array(
                'title' => _('Conversación').$globals['extra_comment_conversation'],
                'link' => $user->get_uri('conversation'),
            ),
            'shaken_comments' => array(
                'title' => _('Votados'),
                'link' => $user->get_uri('shaken_comments'),
            ),
            'favorite_comments' => array(
                'title' => _('Favoritos'),
                'link' => $user->get_uri('favorite_comments'),
            ),
        );
    }

    public static function renderForProfileConversation($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileConversation(), 1, false, '', $tab_class);
    }

    public static function optionsForProfileConversation()
    {
        return self::optionsForProfileCommented();
    }

    public static function renderForProfileShakenComments($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileShakenComments(), 2, false, '', $tab_class);
    }

    public static function optionsForProfileShakenComments()
    {
        return self::optionsForProfileCommented();
    }

    public static function renderForProfileFavoriteComments($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileFavoriteComments(), 3, false, '', $tab_class);
    }

    public static function optionsForProfileFavoriteComments()
    {
        return self::optionsForProfileCommented();
    }

    public static function renderForProfileSubs($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileSubs(), 0, '', $tab_class);
    }

    public static function optionsForProfileSubs()
    {
        global $user, $globals;

        return array(
            'subs' => array(
                'title' => _('Propietario'),
                'link' => $user->get_uri('subs'),
            ),
            'subs_follow' => array(
                'title' => _('Siguiendo'),
                'link' => $user->get_uri('subs_follow'),
            ),
        );
    }

    public static function renderForProfileSubsFollow()
    {
        return self::optionsForProfileSubsFollow();
    }

    public static function optionsForProfileSubsFollow()
    {
        return self::optionsForProfileSubs();
    }

    public static function renderForProfileNotes($params, $tab_class)
    {
        return self::renderUserProfileSubheader(self::optionsForProfileNotes(), 0, '', $tab_class);
    }

    public static function optionsForProfileNotes()
    {
        global $current_user, $user, $globals;

        $options = array(
            'notes' => array(
                'title' => _('Enviadas'),
                'link' => $user->get_uri('notes'),
            ),

            'notes_friends' => array(
                'title' => _('Amigos'),
                'link' => $user->get_uri('notes_friends'),
            ),

            'notes_favorites' => array(
                'title' => _('Favoritas'),
                'link' => $user->get_uri('notes_favorites'),
            ),

            'notes_conversation' => array(
                'title' => _('Conversación'),
                'link' => $user->get_uri('notes_conversation'),
            ),

            'notes_votes' => array(
                'title' => _('Votadas'),
                'link' => $user->get_uri('notes_votes'),
            )
        );

        if ($user->id == $current_user->user_id) {
            $options['notes_privates'] = array(
                'title' => _('Privados'),
                'link' => $user->get_uri('notes_privates'),
            );
        }

        return $options;
    }

    public static function optionsForProfileNotesFriends()
    {
        return self::optionsForProfileNotes();
    }

    public static function optionsForProfileNotesFavorites()
    {
        return self::optionsForProfileNotes();
    }

    public static function optionsForProfileNotesConversation()
    {
        return self::optionsForProfileNotes();
    }

    public static function optionsForProfileNotesVotes()
    {
        return self::optionsForProfileNotes();
    }

    public static function optionsForProfileNotesPrivates()
    {
        return self::optionsForProfileNotes();
    }
}
