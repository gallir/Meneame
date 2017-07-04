<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

require_once mnminclude.'favorites.php';

class Link extends LCPBase
{
    private static $clicked = 0; // Must add a click to this link->id
    public static $original_status = false;

    public $id = 0;
    public $author = -1;
    public $blog = 0;
    public $username = false;
    public $randkey = 0;
    public $karma = 0;
    public $valid = false;
    public $date = false;
    public $sent_date = 0;
    public $published_date = 0;
    public $modified = 0;
    public $url = false;
    public $url_title = '';
    public $encoding = false;
    public $status = 'discard';
    public $type = '';
    public $votes = 0;
    public $anonymous = 0;
    public $votes_avg = 0;
    public $negatives = 0;
    public $title = '';
    public $tags = '';
    public $uri = '';
    public $thumb_url = false;
    public $content = '';
    public $content_type = '';
    public $ip = '';
    public $html = false;
    public $read = false;
    public $voted = false;
    public $banned = false;
    public $thumb_status = 'unknown';
    public $clicks = 0;
    public $is_sub;
    public $sub_id;
    public $sub_name;
    public $total_votes;
    public $avatar;

    public $image;
    public $best_comments = array();
    public $poll;
    public $nsfw;

    // sql fields to build an object from mysql
    const SQL = '
        link_id as id, link_nsfw as nsfw, link_author as author, link_blog as blog, link_status as status, sub_statuses.status as sub_status, sub_statuses.id as sub_status_id, UNIX_TIMESTAMP(sub_statuses.date) as sub_date, link_votes as votes, link_negatives as negatives, link_anonymous as anonymous, link_votes_avg as votes_avg, link_votes + link_anonymous as total_votes, link_comments as comments, link_karma as karma, sub_statuses.karma as sub_karma, link_randkey as randkey, link_url as url, link_uri as uri, link_url_title as url_title, link_title as title, link_tags as tags, link_content as content, UNIX_TIMESTAMP(link_date) as date, UNIX_TIMESTAMP(link_sent_date) as sent_date, UNIX_TIMESTAMP(link_published_date) as published_date, UNIX_TIMESTAMP(link_modified) as modified, link_content_type as content_type, link_ip as ip, link_thumb_status as thumb_status, user_login as username, user_email as email, user_avatar as avatar, user_karma as user_karma, user_level as user_level, user_adcode, user_adchannel, subs.name as sub_name, subs.id as sub_id, subs.server_name, subs.sub as is_sub, subs.owner as sub_owner, subs.base_url, subs.created_from, subs.allow_main_link, creation.status as sub_status_origen, UNIX_TIMESTAMP(creation.date) as sub_date_origen, subs.color1 as sub_color1, subs.color2 as sub_color2, subs.page_mode as page_mode, favorite_link_id as favorite, favorite_link_readed as favorite_readed, clicks.counter as clicks, votes.vote_value as voted, media.size as media_size, media.mime as media_mime, media.extension as media_extension, media.access as media_access, UNIX_TIMESTAMP(media.date) as media_date, 1 as `read` FROM links
        INNER JOIN users on (user_id = link_author)
        LEFT JOIN sub_statuses ON (@site_id > 0 and sub_statuses.id = @site_id and sub_statuses.link = links.link_id)
        LEFT JOIN (sub_statuses as creation, subs) ON (creation.link=links.link_id and creation.id=creation.origen and creation.id=subs.id)
        LEFT JOIN votes ON (link_date > @enabled_votes and vote_type="links" and vote_link_id = links.link_id and vote_user_id = @user_id and ( @user_id > 0  OR vote_ip_int = @ip_int ) )
        LEFT JOIN favorites ON (@user_id > 0 and favorite_user_id =  @user_id and favorite_type = "link" and favorite_link_id = links.link_id)
        LEFT JOIN link_clicks as clicks on (clicks.id = links.link_id)
        LEFT JOIN media ON (media.type="link" and media.id = link_id and media.version = 0)
    ';

    const SQL_BASIC = '
        link_id as id, link_nsfw as nsfw, link_author as author, link_blog as blog, link_status as status, sub_statuses.status as sub_status, sub_statuses.id as sub_status_id, link_votes as votes, link_negatives as negatives, link_anonymous as anonymous, link_votes_avg as votes_avg, link_votes + link_anonymous as total_votes, link_comments as comments, link_karma as karma, sub_statuses.karma as sub_karma, link_randkey as randkey, link_url as url, link_uri as uri, link_url_title as url_title, link_title as title, link_tags as tags, link_content as content, UNIX_TIMESTAMP(link_date) as date,   UNIX_TIMESTAMP(link_sent_date) as sent_date, UNIX_TIMESTAMP(link_published_date) as published_date, UNIX_TIMESTAMP(link_modified) as modified, link_content_type as content_type, link_ip as ip, link_thumb_status as thumb_status, user_login as username, user_email as email, user_avatar as avatar, user_karma as user_karma, user_level as user_level, user_adcode, subs.name as sub_name, subs.id as sub_id, subs.server_name, subs.sub as is_sub, subs.owner as sub_owner, subs.base_url, subs.created_from, subs.allow_main_link, creation.status as sub_status_origen, media.size as media_size, media.mime as media_mime, media.extension as media_extension, media.access as media_access, UNIX_TIMESTAMP(media.date) as media_date, 1 as `read` FROM links
        INNER JOIN users on (user_id = link_author)
        LEFT JOIN sub_statuses ON (@site_id > 0 and sub_statuses.id = @site_id and sub_statuses.link = links.link_id)
        LEFT JOIN (sub_statuses as creation, subs) ON (creation.link=links.link_id and creation.id=creation.origen and creation.id=subs.id)
        LEFT JOIN media ON (media.type="link" and media.id = link_id and media.version = 0)
    ';

    public static function from_db($id, $key = 'id', $complete = true)
    {
        global $db, $current_user;

        SitesMgr::my_id(); // Force to read current sub_id

        switch ($key) {
            case 'uri':
                $id = $db->escape($id);
                $selector = "link_uri = '$id'";
                break;

            default:
                $id = intval($id);
                $selector = "link_id = $id";
        }

        if ($complete) {
            $sql = 'SELECT '.Link::SQL.' WHERE '.$selector.';';
        } else {
            $sql = 'SELECT '.Link::SQL_BASIC.' WHERE '.$selector.';';
        }

        return $db->get_object($sql, 'Link');
    }

    public static function getPopularArticles($limit = 5)
    {
        global $globals, $db;

        if ($globals['memcache_host']) {
            $memcache_popular_articles = 'popular_articles';
        }

        if (!($popular_articles = unserialize(memcache_mget($memcache_popular_articles)))) {
            // Not in memcache
            $sql = '
                SELECT DISTINCT link
                FROM sub_statuses, subs, links
                WHERE (
                    link_content_type = "article"
                    AND link_status IN ("queued", "published")
                    AND sub_statuses.date > "'.date('Y-m-d H:00:00', $globals['now'] - $globals['time_enabled_votes']).'"
                    AND sub_statuses.link = link_id
                    AND sub_statuses.origen = subs.id
                    AND NOT EXISTS (SELECT link FROM sub_statuses WHERE sub_statuses.id='.SitesMgr::getMainSiteId().' AND sub_statuses.status="published" AND link=link_id)
                ) ORDER BY link_votes DESC LIMIT '.$limit;

            $articleIds = $db->get_col($sql);

            foreach ($articleIds as $articleId) {
                $article = self::from_db($articleId);

                $article->time_published_as_string = $article->getTimePublishedAsString();
                $article->relative_permalink = $article->get_relative_permalink();
                $article->permalink = $article->get_permalink(
                    false,
                    $article->relative_permalink
                ); // To avoid double verification
                $article->url_str = preg_replace('/^www\./', '', parse_url($article->url, 1));

                $article->max_len = 200;
                $popular_articles[] = $article;
            }

            memcache_madd($memcache_popular_articles, serialize($popular_articles), 1800);
        }

        return $popular_articles;
    }

    public static function getPromotedArticles($limit = 2)
    {

        global $globals, $db;

        if ($globals['memcache_host']) {
            $memcache_promoted_articles = 'promoted_articles';
        }

        if (!($promoted_articles = unserialize(memcache_mget($memcache_promoted_articles)))) {
            // Not in memcache
            $sql = '
                SELECT DISTINCT link
                FROM sub_statuses, subs, links
                WHERE (
                    link_content_type = "article"
                    AND link_status IN ("queued", "published")
                    AND sub_statuses.link = link_id
                    AND sub_statuses.date > "'.date('Y-m-d H:00:00', $globals['now'] - $globals['time_enabled_votes']).'"
                    AND sub_statuses.origen = subs.id
                    AND NOT EXISTS (SELECT link FROM sub_statuses WHERE sub_statuses.id='.SitesMgr::getMainSiteId().' AND sub_statuses.status="published" AND link=link_id)
                ) ORDER BY link_karma DESC LIMIT '.$limit;

            $articleIds = $db->get_col($sql);

            foreach ($articleIds as $articleId) {
                $article = self::from_db($articleId);
                $article->max_len = 600;
                $promoted_articles[] = $article;
            }

            memcache_madd($memcache_promoted_articles, serialize($promoted_articles), 1800);
        }

        return $promoted_articles;
    }

    public function getTimePublishedAsString()
    {
        $interval = date_create('now')->diff(date_create('@'.$this->published_date));


        if (($interval->d >= 3) || (($interval->d > 1) && ($interval->h === 0))) {
            return sprintf(_('Hace %s días'), $interval->d);
        }

        if ($interval->d > 1) {
            return sprintf(_('Hace %s días y %s horas'), $interval->d, $interval->h);
        }

        if (($interval->d === 1) && ($interval->h === 0)) {
            return _('Hace 1 día');
        }

        if ($interval->d === 1) {
            return sprintf(_('Hace 1 día y %s horas'), $interval->h);
        }

        if (($interval->h > 1) && ($interval->i === 0)) {
            return sprintf(_('Hace %s horas'), $interval->h);
        }

        if ($interval->h > 1) {
            return sprintf(_('Hace %s horas y %s minutos'), $interval->h, $interval->i);
        }

        if (($interval->h === 1) && ($interval->i === 0)) {
            return _('Hace 1 hora');
        }

        if ($interval->h === 1) {
            return sprintf(_('Hace 1 hora y %s minutos'), $interval->i);
        }

        if ($interval->i > 1) {
            return sprintf(_('Hace %s minutos'), $interval->i);
        }

        if ($interval->i === 1) {
            return _('Hace 1 minuto');
        }

        return sprintf(_('Hace %s segundos'), $interval->s);
    }

    public static function count($status = '', $force = false)
    {
        global $db, $globals;

        $my_id = SitesMgr::my_id();

        if (!$status) {
            return Link::count('published', $force)
                + Link::count('queued', $force)
                + Link::count('discard', $force)
                + Link::count('abuse', $force)
                + Link::count('autodiscard', $force);
        }

        $count = get_count("$my_id.$status");

        if ($count === false || $force) {
            $count = $db->get_var("select count(*) from sub_statuses where id = $my_id and status = '$status'");
            set_count("$my_id.$status", $count);
        }

        return $count;
    }

    public static function duplicates($url, $site = false)
    {
        global $db;

        $trimmed = $db->escape(preg_replace('/\/$/', '', $url));
        $list = "'$trimmed', '$trimmed/'";

        if (preg_match('/^http.{0,1}:\/\/www\./', $trimmed)) {
            $link_alternative = preg_replace('/^(http.{0,1}):\/\/www\./', '$1://', $trimmed);
        } else {
            $link_alternative = preg_replace('/^(http.{0,1}):\/\//', '$1://www.', $trimmed);
        }

        $list .= ", '$link_alternative', '$link_alternative/'";

        /* Alternative to http and https */
        if (preg_match('/^http:/', $url)) {
            $list2 = preg_replace('/http:\/\//', 'https://', $list);
        } else {
            $list2 = preg_replace('/https:\/\//', 'http://', $list);
        }

        $list .= ", $list2";

        /***** TODO: check and decid how duplicated must be dealt with
         * $subs = array();
         * $site_id = SitesMgr::my_id();
         * $subs[] = $site_id;
         * $subs = array_merge($subs, SitesMgr::get_senders(), SitesMgr::get_receivers());
         * $subs = implode(',', $subs);
         *
         * // If it was abuse o autodiscarded allow other to send it again
         * $found = $db->get_var("SELECT link_id FROM links, sub_statuses WHERE link_url in ($list) AND status not in ('abuse') AND link_votes > 0 AND sub_statuses.link = link_id AND sub_statuses.id in ($subs) ORDER by link_id asc limit 1");
         *******/

        if ($site > 0) {
            $from_extra = ', sub_statuses';
            $where_extra = "and sub_statuses.id = $site and sub_statuses.link = link_id";
        } else {
            $from_extra = $where_extra = '';
        }

        // If it was abuse o autodiscarded allow other to send it again
        return $db->get_var(
            "SELECT link_id FROM links $from_extra WHERE link_url in ($list) AND link_status not in ('abuse') AND link_votes > 0 $where_extra ORDER by link_id asc limit 1"
        );
    }

    public static function visited($id)
    {
        global $globals;

        if (!isset($_COOKIE['v']) || !($visited = preg_split('/x/', $_COOKIE['v'], 0, PREG_SPLIT_NO_EMPTY))) {
            $visited = array();
            $found = false;
        } else {
            $found = array_search($id, $visited);

            if (count($visited) > 10) {
                array_shift($visited);
            }

            if ($found !== false) {
                unset($visited[$found]);
            }
        }

        $visited[] = $id;

        setcookie('v', implode('x', $visited), 0, $globals['base_url_general'], UserAuth::domain());

        return $found !== false;
    }

    public static function store_clicks()
    {
        global $globals, $db;

        if (!self::$clicked) {
            return false;
        }

        $id = self::$clicked;

        self::$clicked = 0;

        if (!memcache_menabled()) {
            $db->query("UPDATE link_clicks SET counter = counter + 1 WHERE id = $id");

            return true;
        }

        $key = 'clicks_cache';
        $cache = memcache_mget($key);

        if (!$cache || !is_array($cache)) {
            $cache = array();
            $cache['time'] = $globals['start_time'];
            $cache[$id] = 1;
            $in_cache = false;
        } else {
            $cache[$id]++;
            $in_cache = true;
        }

        // We use random to minimize race conditions for deleting the cache
        if ($globals['start_time'] - $cache['time'] <= (3.0 + rand(0, 100) / 100)) {
            memcache_madd($key, $cache);

            return;
        }

        if ($in_cache && !memcache_mdelete($key)) {
            memcache_madd($key, array());
            syslog(LOG_INFO, "store_clicks: Delete failed");
        }

        ksort($cache); // To avoid transaction's deadlocks

        $show_errors = $db->show_errors;
        $db->show_errors = false; // we know there can be lock timeouts :(
        $tries = 0; // By the way, freaking locking timeouts with few updates per second with this technique

        while ($tries < 3) {
            $error = false;
            $total = 0;
            $r = true;

            $db->transaction();

            foreach ($cache as $id => $counter) {
                if ($id > 0 && $counter > 0) {
                    $r = $db->query(
                        "INSERT INTO link_clicks (id, counter) VALUES ($id,$counter) ON DUPLICATE KEY UPDATE counter=counter+$counter"
                    );

                    if (!$r) {
                        break;
                    }

                    $total += $counter;
                }
            }

            if ($r) {
                $db->commit();
                $tries = 100000; // Stop it
            } else {
                $tries++;
                syslog(LOG_INFO, "failed $tries attempts in store_clicks");
                $db->rollback();
            }
        }

        $db->show_errors = $show_errors;
    }

    public static function top()
    {
        global $globals;

        // It retrieves the annotiation generated by the Python script top-news.py
        $top = new Annotation('top-link-'.$globals['site_shortname']);

        if (!$top->read()) {
            return false;
        }

        $ids = explode(',', $top->text);

        if (!$ids) {
            return false;
        }

        $link = Link::from_db($ids[0]);

        if ($link) {
            $link->has_thumb();
        }

        return $link;
    }

    public static function userArticlesDraft($user)
    {
        global $db;

        if ($user->id < 1) {
            return 0;
        }

        return (int)$db->get_var(
            '
            SELECT SQL_CACHE COUNT(*)
            FROM links
            WHERE (
                link_status = "discard"
                AND link_author = "'.$user->id.'"
                AND link_content_type = "article"
                AND link_votes = 0
            );
        '
        );
    }

    public function add_click($no_go = false)
    {
        global $globals, $db;

        if (
            !$globals['bot']
            && !Link::visited($this->id)
            && $globals['click_counter']
            && ($no_go || (isset($_COOKIE['k']) && check_security_key($_COOKIE['k'])))
            && $this->ip !== $globals['user_ip']
        ) {
            // Delay storing
            self::$clicked = $this->id;
        }
    }

    public function json_votes_info($value = false)
    {
        $dict = array();
        $dict['id'] = $this->id;

        if ($value) {
            $dict['value'] = $value;

            if ($value < 0) {
                $dict['vote_description'] = get_negative_vote($value);
            } else {
                $dict['vote_description'] = _('¡hecho!');
            }
        }

        $dict['votes'] = $this->votes;
        $dict['anonymous'] = $this->anonymous;
        $dict['negatives'] = $this->negatives;

        if ($this->sub_status === 'published') {
            $dict['karma'] = intval($this->sub_karma);
        } else {
            $dict['karma'] = intval($this->karma);
        }

        return json_encode($dict);
    }

    public function print_html()
    {
        echo "Valid: ".$this->valid."<br>\n";
        echo "Url: ".$this->url."<br>\n";
        echo "Title: ".$this->url_title."<br>\n";
        echo "encoding: ".$this->encoding."<br>\n";
    }

    public function check_url($url, $check_ban = true, $first_level = false)
    {
        global $globals, $current_user;

        if (!preg_match('/^http[s]*:/', $url)) {
            return false;
        }

        $url_components = @parse_url($url);

        if (!$url_components || !preg_match('/[a-z]+/', $url_components['host'])) {
            return false;
        }

        if (!$check_ban) {
            return true;
        }

        $quoted_domain = preg_quote(get_server_name());

        if (preg_match("/^$quoted_domain$/", $url_components['host'])) {
            $this->ban = array();
            $this->ban['comment'] = _('el servidor es local');

            syslog(LOG_NOTICE, "Meneame, server name is local name ($current_user->user_login): $url");

            return false;
        }

        require_once mnminclude.'ban.php';

        if (!($this->ban = check_ban($url, 'hostname', false, $first_level))) {
            return true;
        }

        syslog(LOG_NOTICE, "Meneame, server name is banned ($current_user->user_login): $url");

        $this->banned = true;

        return false;
    }

    public function get($url, $maxlen = 150000, $check_ban = true)
    {
        global $globals, $current_user;

        $url = trim($url);
        $url_components = @parse_url($url);

        $this->valid = false;
        $this->noiframe = false;

        if (($response = get_url($url))) {
            $this->content_type = preg_replace('#^(\w+).+#', '$1', $response['content_type']);

            // Check if it has pingbacks
            if (preg_match('/X-Pingback: *(.+)/i', $response['header'], $match)) {
                $this->pingback = 'ping:'.clean_input_url($match[1]);
            }

            /* Were we redirected? */
            if ($response['redirect_count'] > 0) {
                /* update $url with where we were redirected to */
                $new_url = clean_input_url($response['location']);
            }

            if (!empty($new_url) && $new_url !== $url) {
                syslog(LOG_NOTICE, "Meneame, redirected ($current_user->user_login): $url -> $new_url");

                /* Check again the url */
                if (!$this->check_url($new_url, $check_ban, true)) {
                    $this->url = $new_url;

                    return false;
                }

                // Change the url if we were directed to another host
                if (strlen($new_url) < 300 && ($new_url_components = @parse_url($new_url))) {
                    if ($url_components['host'] !== $new_url_components['host']) {
                        syslog(LOG_NOTICE, "Meneame, changed source URL ($current_user->user_login): $url -> $new_url");

                        $url = $new_url;
                        $url_components = $new_url_components;
                    }
                }
            }

            $this->html = $response['content'];

            $url_ok = true;
        } else {
            syslog(LOG_NOTICE, "Meneame, error getting ($current_user->user_login): $url");

            $url_ok = false;
        }

        $this->url = $url;

        // Fill content type if empty
        // Right now only check for typical image extensions
        if (empty($this->content_type) && preg_match('/(jpg|jpeg|gif|png)(\?|#|$)/i', $this->url)) {
            $this->content_type = 'image';
        }

        // NO more to do
        if (!$url_ok || !preg_match('/html/', $response['content_type'])) {
            return true;
        }

        // Check if it forbides including in an iframe
        if (preg_match('/X-Frame-Options: *(.+)/i', $response['header']) || preg_match(
                '/top\.location\.href *=/',
                $response['content']
            )
        ) {
            $this->noiframe = true;
        }

        if (preg_match('/charset=([a-zA-Z0-9-_]+)/i', $this->html, $matches)) {
            $this->encoding = trim($matches[1]);

            if (strcasecmp($this->encoding, 'utf-8') !== 0) {
                $this->html = iconv($this->encoding, 'UTF-8//IGNORE', $this->html);
            }
        }

        // Check if the author doesn't want to share
        if (preg_match('/<!-- *noshare *-->/', $this->html)) {
            $this->ban = array();
            $this->ban['comment'] = _('el autor no desea que se envíe el artículo, respeta sus deseos');

            syslog(LOG_NOTICE, "Meneame, noshare ($current_user->user_login): $url");

            return false;
        }

        // Now we analyse the html to find links to banned domains
        // It avoids the trick of using google or technorati
        // Ignore it if the link has a rel="nofollow" to ignore comments in blogs
        if (!preg_match('/content="[^"]*(vBulletin|phpBB)/i', $this->html)) {
            preg_match_all(
                '/(< *meta +http-equiv|< *frame[^<]*>|window\.|document.\|parent\.|top\.|self\.)[^><]*(url|src|replace) *[=\(] *[\'"]{0,1}https*:\/\/[^\s "\'>]+[\'"\;\)]{0,1}[^>]*>/i',
                $this->html,
                $matches
            );
        } else {
            preg_match_all(
                '/(<* meta +http-equiv|<* iframe|<* frame[^<]*>|window\.|document.\|parent\.|top\.|self\.)[^><]*(href|url|src|replace) *[=\(] *[\'"]{0,1}https*:\/\/[^\s "\'>]+[\'"\;\)]{0,1}[^>]*>/i',
                $this->html,
                $matches
            );
        }

        $check_counter = 0;
        $second_level = preg_quote(preg_replace('/^(.+\.)*([^\.]+)\.[^\.]+$/', "$2", $url_components['host']));

        foreach ($matches[0] as $match) {
            if ($check_counter >= 5) {
                break;
            }

            if (preg_match('/<a.+rel=.*nofollow.*>/', $match)) {
                continue;
            }

            preg_match(
                '/(href|url|src|replace) *[=\(] *[\'"]{0,1}(https*:\/\/[^\s "\'>]+)[\'"\;\)]{0,1}/i',
                $match,
                $url_a
            );

            $embeded_link = $url_a[2];
            $new_url_components = @parse_url($embeded_link);

            if (empty($embeded_link) || $checked_links[$new_url_components['host']]) {
                continue;
            }

            if (!preg_match("/$second_level\.[^\.]+$/", $new_url_components['host'])) {
                $check_counter++;
            }

            $checked_links[$new_url_components['host']] = true;

            if (!$this->check_url($embeded_link, false) && $this->banned) {
                return false;
            }
        }

        // The URL has been checked
        $this->valid = true;

        if (preg_match('/<title[^<>]*>([^<>]*)<\/title>/si', $this->html, $matches)) {
            $url_title = clean_text($matches[1]);

            if (mb_strlen($url_title) > 3) {
                $this->url_title = $url_title;
            }
        }

        if (preg_match(
            '/< *meta +name=[\'"]description[\'"] +content=[\'"]([^<>]+)[\'"] *\/*>/si',
            $this->html,
            $matches
        )) {
            $this->url_description = clean_text_with_tags($matches[1], 0, false, 400);
        }

        return true;
    }

    public function trackback()
    {
        $trackback = '';

        // Now detect trackbacks
        if (preg_match('/trackback:ping="([^"]+)"/i', $this->html, $matches) ||
            preg_match('/trackback:ping +rdf:resource="([^>]+)"/i', $this->html, $matches) ||
            preg_match('/<trackback:ping>([^<>]+)/i', $this->html, $matches)
        ) {
            $trackback = trim($matches[1]);
        } elseif (preg_match('/<a[^>]+rel="trackback"[^>]*>/i', $this->html, $matches)) {
            if (preg_match('/href="([^"]+)"/i', $matches[0], $matches2)) {
                $trackback = trim($matches2[1]);
            }
        } elseif (preg_match('/<a[^>]+href=[^>#]+>[^>]*trackback[^>]*<\/a>/i', $this->html, $matches)) {
            if (preg_match('/href="([^"]+)"/i', $matches[0], $matches2)) {
                $trackback = trim($matches2[1]);
            }
        } elseif (preg_match('/(http:\/\/[^\s#]+\/trackback[\/\w]*)/i', $this->html, $matches)) {
            $trackback = trim($matches[0]);
        }

        if (empty($trackback)) {
            return false;
        }

        $this->trackback = clean_input_url($trackback);

        return true;
    }

    public function pingback()
    {
        $trackback = '';
        $url_components = @parse_url($this->url);

        // Now we use previous pingback or detect it
        if ((!empty($url_components['query']) || preg_match('|^/.*[\.-/]+|', $url_components['path']))) {
            if (!empty($this->pingback)) {
                $trackback = $this->pingback;
            } elseif (preg_match('/<link[^>]+rel="pingback"[^>]*>/i', $this->html, $matches)) {
                if (preg_match('/href="([^"]+)"/i', $matches[0], $matches2)) {
                    $trackback = 'ping:'.trim($matches2[1]);
                }
            }
        }

        if (empty($trackback)) {
            return false;
        }

        $this->trackback = clean_input_url($trackback);

        return true;
    }

    public function enqueue()
    {
        global $db, $globals, $current_user;
        // Check this one was not already queued

        if ($this->votes || $this->author != $current_user->user_id || $this->status === 'queued') {
            return;
        }

        $this->status = 'queued';
        $this->sent_date = $this->date = time();
        $this->get_uri();

        $db->transaction();

        if (!$this->store()) {
            $db->rollback();

            return false;
        }

        $this->insert_vote($current_user->user_karma);

        Log::conditional_insert('link_new', $this->id, $this->author);

        $db->query(
            '
            DELETE FROM links
            WHERE (
                link_author = "'.$this->author.'"
                AND link_date > DATE_SUB(NOW(), INTERVAL 2 HOUR)
                AND link_status = "discard"
                AND link_content_type != "article"
                AND link_votes = 0
            )
        '
        );

        if (!empty($_POST['trackback'])) {
            $trackres = new Trackback;
            $trackres->url = clean_input_url($_POST['trackback']);
            $trackres->link_id = $this->id;
            $trackres->link = $this->url;
            $trackres->author = $this->author;
            $trackres->status = 'pendent';
            $trackres->store();
        }

        $db->commit();

        if (!empty($_POST['trackback'])) {
            fork("backend/send_pingbacks.php?id=$this->id");
        }
    }

    public function enqueuePrivate()
    {
        global $db, $globals, $current_user;

        if ($this->votes || $this->author != $current_user->user_id || $this->status === 'private') {
            return;
        }

        $this->status = 'private';
        $this->sent_date = $this->date = time();
        $this->get_uri();

        $db->transaction();

        if (!$this->store()) {
            return $db->rollback();
        }

        $this->insert_vote($current_user->user_karma);

        Log::conditional_insert('link_new', $this->id, $this->author);

        $db->query(
            '
            DELETE FROM links
            WHERE (
                link_author = "'.$this->author.'"
                AND link_date > DATE_SUB(NOW(), INTERVAL 2 HOUR)
                AND link_status = "discard"
                AND link_content_type != "article"
                AND link_votes = 0
            )
        '
        );

        $db->commit();
    }

    public function has_rss()
    {
        return preg_match('/<link[^>]+(text\/xml|application\/rss\+xml|application\/atom\+xml)[^>]+>/i', $this->html);
    }

    public function create_blog_entry()
    {
        $blog = new Blog();
        $blog->analyze_html($this->url, $this->html);

        if (!$blog->read('key') || ($blog->type !== 'noiframe' && $this->noiframe)) {
            if ($blog->type !== 'noiframe' && $this->noiframe) {
                $blog->type = 'noiframe';
                syslog(LOG_INFO, "Meneame, changed to noiframe ($blog->id, $blog->url)");
            }

            $blog->store();
        }

        $this->blog = $blog->id;
        $this->type = $blog->type;
    }

    public function type()
    {
        if ($this->type) {
            return $this->type;
        }

        if (!$this->blog) {
            return 'normal';
        }

        $blog = new Blog();
        $blog->id = $this->blog;

        if (!$blog->read()) {
            return 'normal';
        }

        return $this->type = $blog->type;
    }

    public function store()
    {
        global $db, $current_user, $globals;

        $db->transaction();

        if (!$this->store_basic()) {
            $db->rollback();

            return false;
        }

        $link_url = $db->escape($this->url);
        $link_uri = $db->escape($this->uri);
        $link_url_title = $db->escape($this->url_title);
        $link_title = $db->escape($this->title);
        $link_tags = $db->escape($this->tags);
        $link_content = $db->escape($this->content);
        $link_thumb_status = $db->escape($this->thumb_status);
        $link_nsfw = $this->nsfw ? 1 : 0;

        $r = $db->query(
            '
            UPDATE links
            SET
                link_url = "'.$link_url.'",
                link_uri = "'.$link_uri.'",
                link_url_title = "'.$link_url_title.'",
                link_title = "'.$link_title.'",
                link_content = "'.$link_content.'",
                link_tags = "'.$link_tags.'",
                link_thumb_status = "'.$link_thumb_status.'",
                link_nsfw = "'.$link_nsfw.'"
            WHERE link_id = "'.$this->id.'"
            LIMIT 1;
        '
        );

        $db->commit();

        return $r;
    }

    public function store_basic($really_basic = false)
    {
        global $db, $current_user, $globals;

        if (!$this->date) {
            $this->date = $globals['now'];
        }

        $link_author = $this->author;
        $link_blog = $this->blog;
        $link_status = $db->escape($this->status);
        $link_anonymous = $this->anonymous;
        $link_karma = $this->karma;
        $link_votes_avg = $this->votes_avg;
        $link_randkey = $this->randkey;
        $link_date = $this->date;
        $link_sent_date = $this->sent_date;
        $link_published_date = $this->published_date;
        $link_content_type = $db->escape($this->content_type);

        $db->transaction();

        if (empty($this->id)) {
            $this->ip = $globals['user_ip'];
            $this->ip_int = $globals['user_ip_int'];

            $r = $db->query(
                '
                INSERT INTO links
                SET
                    link_author = "'.$link_author.'",
                    link_blog = "'.$link_blog.'",
                    link_status = "'.$link_status.'",
                    link_randkey = "'.$link_randkey.'",
                    link_date = FROM_UNIXTIME('.$link_date.'),
                    link_sent_date = FROM_UNIXTIME('.$link_sent_date.'),
                    link_published_date = FROM_UNIXTIME('.$link_published_date.'),
                    link_karma = "'.$link_karma.'",
                    link_anonymous = "'.$link_anonymous.'",
                    link_votes_avg = "'.$link_votes_avg.'",
                    link_content_type = "'.$link_content_type.'",
                    link_ip_int = "'.$this->ip_int.'",
                    link_ip = "'.$db->escape($this->ip).'";
            '
            );
            $this->id = $db->insert_id;
        } else {
            $r = $db->query(
                '
                UPDATE links
                SET
                    link_author = "'.$link_author.'",
                    link_blog = "'.$link_blog.'",
                    link_status = "'.$link_status.'",
                    link_randkey = "'.$link_randkey.'",
                    link_date = FROM_UNIXTIME('.$link_date.'),
                    link_sent_date = FROM_UNIXTIME('.$link_sent_date.'),
                    link_published_date = FROM_UNIXTIME('.$link_published_date.'),
                    link_karma = "'.$link_karma.'",
                    link_votes_avg = "'.$link_votes_avg.'",
                    link_content_type = "'.$link_content_type.'"
                WHERE link_id = "'.$this->id.'"
                LIMIT 1;
            '
            );
        }

        // Deploy changes to other sub sites
        if (!$r || !SitesMgr::deploy($this)) {
            syslog(LOG_INFO, "failed insert of update in store_basic: $this->id");
            $db->rollback();

            return false;
        }

        if (!$really_basic) {
            if ($this->votes == 1 && $this->negatives == 0 && ($this->status === 'queued')) {
                // This is a new link, add it to the events, it an additional control
                // just in case the user dind't do the last submit phase and voted later
                Log::conditional_insert('link_new', $this->id, $this->author);
            }

            $this->update_votes();
            $this->update_comments();
        }

        $db->commit();

        return true;
    }

    public function update_votes()
    {
        global $db, $globals;

        // ALERT: Do not modify if votes are already closed
        if ($this->date < time() - ($globals['time_enabled_votes'] + 3600)) {
            return;
        }

        $count = $db->get_var(
            "select count(*) from votes where vote_type='links' and vote_link_id=$this->id FOR UPDATE"
        );

        if ($count == ($this->votes + $this->anonymous + $this->negatives)) {
            return;
        }

        $db->query(
            "update links set link_votes=(select count(*) from votes where vote_type='links' and vote_link_id=$this->id and vote_user_id > 0 and vote_value > 0), link_anonymous = (select count(*) from votes where vote_type='links' and vote_link_id=$this->id and vote_user_id = 0 and vote_value > 0), link_negatives = (select count(*) from votes where vote_type='links' and vote_link_id=$this->id and vote_user_id > 0 and vote_value < 0) where link_id = $this->id"
        );

        if ($db->affected_rows > 0) {
            syslog(
                LOG_INFO,
                "Votes count ($count) are wrong in $this->id ($this->votes, $this->anonymous, $this->negatives), updating"
            );
        }
    }

    public function read_basic($key = 'id')
    {
        global $db, $current_user;

        SitesMgr::my_id(); // Force to read current sub_id

        switch ($key) {
            case 'uri':
                $cond = "link_uri = '$this->uri'";
                break;

            case 'url':
                $cond = "link_url = '$this->url'";
                break;

            default:
                $cond = "link_id = $this->id";
                break;
        }

        if (!($result = $db->get_row('SELECT '.Link::SQL_BASIC.' WHERE '.$cond.';'))) {
            return false;
        }

        foreach (get_object_vars($result) as $var => $value) {
            $this->$var = $value;
        }

        return true;
    }

    public function read($key = 'id')
    {
        global $db, $current_user;

        SitesMgr::my_id(); // Force to read current sub_id

        switch ($key) {
            case 'uri':
                $cond = 'link_uri = "'.$this->uri.'"';
                break;

            case 'url':
                $cond = 'link_url = "'.$this->url.'"';
                break;

            default:
                $cond = 'link_id = "'.$this->id.'"';
                break;
        }

        if (!($result = $db->get_row('SELECT '.Link::SQL.' WHERE '.$cond.';'))) {
            return $this->read = false;
        }

        foreach (get_object_vars($result) as $var => $value) {
            $this->$var = $value;
        }

        return true;
    }

    public function print_summary(
        $type = 'full',
        $karma_best_comment = 0,
        $show_tags = true,
        $template = 'link_summary.html',
        $tag = ""
    ) {
        global $current_user, $current_user, $globals, $db;

        if (!$this->read) {
            return;
        }

        $this->is_votable();

        $this->get_current_sub_status_and_date();

        if (!empty($this->max_len) && $this->max_len > 0) {
            $this->truncate($this->max_len);

            if ($this->content_type === 'article') {
                $this->content = text_to_summary($this->content, $this->max_len);
            }
        }

        $this->content = $this->to_html($this->content);

        $this->show_tags = $show_tags;
        $this->relative_permalink = $this->get_relative_permalink();
        $this->permalink = $this->get_permalink(false, $this->relative_permalink); // To avoid double verification
        $this->show_shakebox = $type !== 'preview' && $this->votes > 0;
        $this->has_warning = !(!$this->check_warn() || $this->is_discarded());
        $this->is_editable = $this->is_editable();
        $this->url_str = preg_replace('/^www\./', '', parse_url($this->url, 1));
        $this->has_thumb();
        $this->map_editable = $this->geo && $this->is_map_editable();

        if (
            !$this->voted
            && $this->votes_enabled
            && $this->negatives_allowed($globals['link_id'] > 0)
            && ($type !== 'short')
            && ($type !== 'preview')
            && !$this->is_sponsored()
        ) {
            $this->can_vote_negative = true;
        } else {
            $this->can_vote_negative = false;
        }

        if (($this->status === 'abuse') || $this->has_warning) {
            $this->negative_text = false;

            $negatives = $db->get_row(
                '
                SELECT SQL_CACHE vote_value, COUNT(vote_value) AS `count`
                FROM votes
                WHERE (
                    vote_type = "links"
                    AND vote_link_id = "'.$this->id.'"
                    AND vote_value < 0
                )
                GROUP BY vote_value
                ORDER BY `count` DESC
                LIMIT 1
            '
            );

            if ($negatives->count > 2 && $negatives->count >= $this->negatives / 2 && ($negatives->vote_value == -6 || $negatives->vote_value == -8)) {
                $this->negative_text = get_negative_vote($negatives->vote_value);
            }
        }

        if ($karma_best_comment > 0 && $this->comments > 0 && $this->comments < 50 && $globals['now'] - $this->date < 86400) {
            $this->best_comment = $db->get_row(
                DbHelper::queryPlain(
                    '
                SELECT SQL_CACHE comment_id, comment_order, comment_content AS content_full,
                    comment_date, comment_modified, SUBSTR(comment_content, 1, 225) AS content,
                    user_id, user_login, user_avatar
                FROM comments
                JOIN users ON (user_id = comment_user_id)
                WHERE (
                    comment_link_id = "'.$this->id.'"
                    AND comment_karma > "'.$karma_best_comment.'"
                    AND comment_votes > 0
                )
                ORDER BY comment_karma DESC
                LIMIT 1;
            '
                )
            );
        } else {
            $this->best_comment = false;
        }

        if ($this->geo && $this->map_editable && $current_user->user_id == $this->author && $this->sent_date > $globals['now'] - 600 && !$this->latlng) {
            $this->add_geo = true;
        } else {
            $this->add_geo = false;
        }

        $this->get_box_class();

        if ($this->do_inline_friend_votes) {
            $this->friend_votes = $db->get_results(
                '
                SELECT vote_user_id AS user_id, vote_value, user_avatar,
                    user_login, UNIX_TIMESTAMP(vote_date) AS ts,
                    INET_NTOA(vote_ip_int) AS ip
                FROM votes, users, friends
                WHERE (
                    vote_type = "links"
                    AND vote_link_id = "'.$this->id.'"
                    AND vote_user_id = friend_to
                    AND vote_user_id > 0
                    AND user_id = vote_user_id
                    AND friend_type = "manual"
                    AND friend_from = "'.$current_user->user_id.'"
                    AND friend_value > 0
                    AND vote_value > 0
                    AND vote_user_id != "'.$this->author.'"
                )
                ORDER BY vote_date DESC
            '
            );
        }

        if ($this->poll === true) {
            $this->poll = new Poll;

            if ($this->id) {
                $this->poll->read('link_id', $this->id);
            }
        }

        $sponsored = $this->is_sponsored();

        $vars = compact('type', 'sponsored', 'tag');
        $vars['self'] = $this;

        return Haanga::Load($template, $vars);
    }

    public function get_best_comments($limit = 5)
    {
        global $db;

        if (empty($this->id) || $this->best_comments) {
            return $this->best_comments;
        }

        $this->best_comments = $db->get_results(
            DbHelper::queryPlain(
                '
            SELECT SQL_CACHE comment_id, comment_order, comment_content AS content_full,
                comment_date, comment_modified, SUBSTR(comment_content, 1, 225) AS content,
                user_id, user_login, user_avatar
            FROM comments
            JOIN users ON (user_id = comment_user_id)
            WHERE (
                comment_link_id = "'.$this->id.'"
                AND comment_karma > 0
                AND comment_votes > 0
            )
            ORDER BY comment_karma DESC
            LIMIT '.(int)$limit.';
        '
            )
        );

        foreach ($this->best_comments as $comment) {
            $comment->html = str_replace('<br />', ' ', $this->html($this->truncate_text($comment->content_full, 500)));
        }

        return $this->best_comments;
    }

    public function get_box_class()
    {
        switch ($this->status) {
            case 'queued': // another color box for not-published
                $this->box_class = 'mnm-queued';
                break;

            case 'abuse': // another color box for discarded
            case 'autodiscard': // another color box for discarded
            case 'discard': // another color box for discarded
                $this->box_class = 'mnm-discarded';
                break;

            default: // default for published
                $this->box_class = 'mnm-published';
                break;
        }
    }

    public function check_warn()
    {
        global $db, $globals;

        // Don't warn it hasn't an url and it's in the original sub
        if (empty($this->url) && SitesMgr::my_id() == $this->sub_id) {
            return $this->warned = false;
        }

        $age = $globals['now'] - $this->sent_date;

        // Don't warn the first x/60 minutes
        if ($age < 600) {
            return $this->warned = false;
        }

        // Percentage increases with time, until 1 hour
        $coef = min(1, $age / 3600);

        if ($this->sub_status === 'published') {
            $neg_percent = 0.11 / $coef;
        } else {
            $neg_percent = 0.1 / $coef;
        }

        if ($this->negatives < 4 || $this->negatives < $this->votes * $neg_percent) {
            return $this->warned = false;
        }

        // Dont do further analisys for published or discarded links
        if ($this->sub_status === 'published' || $this->is_discarded(
            ) || $globals['bot'] || $globals['now'] - $this->date > 86400 * 3
        ) {
            return $this->warned = true;
        }

        // Check positive and negative karmas
        $pos = $db->get_row(
            "select sum(vote_value) as karma, avg(vote_value) as avg from votes where vote_type = 'links' and vote_link_id = $this->id and vote_value > 0 and vote_user_id > 0"
        );
        $neg = $db->get_row(
            "select sum(user_karma) as karma, avg(user_karma) as avg from votes, users where vote_type = 'links' and vote_link_id = $this->id and vote_value < 0 and user_id = vote_user_id and user_level not in ('autodisabled','disabled')"
        );

        if ($neg->karma > 0 && $neg->avg > 0 && $pos->avg > 0) {
            // To avoid division by zero
            $karma_neg_corrected = $neg->karma * $neg->avg / $pos->avg; // Adjust to averages for each type

            if ($karma_neg_corrected < $pos->karma * $neg_percent) {
                return $this->warned = false;
            }
        }

        return $this->warned = true;
    }

    public function vote_exists($user)
    {
        $vote = new Vote('links', $this->id, $user);

        return $vote->exists(false);
    }

    public function votes($user)
    {
        $vote = new Vote('links', $this->id, $user);

        return $vote->count();
    }

    public function insert_vote($value)
    {
        global $db, $current_user, $globals;

        $vote = new Vote('links', $this->id, $current_user->user_id);

        if ($vote->exists(true)) {
            return false;
        }

        // For karma calculation
        $status = !empty($this->sub_status) ? $this->sub_status : $this->status;
        $vote_value = ($value > 0 ? $value : -$current_user->user_karma);

        if ($value < 0 and isset($globals['negative_vote_karma_weights'])) {
            $vote_value *= $globals['negative_vote_karma_weights'][$value];
        }

        $karma_value = round($vote_value);

        $vote->value = $value;

        $db->transaction();

        if (!$vote->insert()) {
            $db->rollback();

            return false;
        }

        if ($vote->user > 0) {
            if ($value > 0) {
                $what = 'link_votes=link_votes+1';
            } else {
                $what = 'link_negatives=link_negatives+1';
            }
        } else {
            $what = 'link_anonymous=link_anonymous+1';
        }

        $r = $db->query("update links set $what, link_karma=link_karma+$karma_value where link_id = $this->id");

        if (!$r) {
            syslog(LOG_INFO, "failed transaction in Link::insert_vote: $this->id ($r)");
            $value = false;
        } else {
            // Update in memory object
            if ($vote->user > 0) {
                if ($value > 0) {
                    $this->votes += 1;
                } else {
                    $this->negatives += 1;
                }
            } else {
                $this->anonymous += 1;
            }

            // Update karma and check votes
            if ($status !== 'published') {
                $this->karma += $karma_value;
                $this->update_votes();
            }
        }

        $db->commit();

        return $value;
    }

    public function publish()
    {
        global $globals;

        if (!$this->read) {
            $this->read_basic();
        }

        $this->published_date = $globals['now'];
        $this->date = $globals['now'];
        $this->status = 'published';
        $this->store_basic();
    }

    public function update_comments()
    {
        global $db;

        $count = $db->get_var("SELECT count(*) FROM comments WHERE comment_link_id = $this->id FOR UPDATE");

        if ($count == $this->comments && $count !== false) {
            return true;
        }

        $db->query(
            "update links set link_comments = (SELECT count(*) FROM comments WHERE comment_link_id = link_id) where link_id = $this->id"
        );

        $this->comments = $count;
    }

    public function is_discarded()
    {
        $status = (empty($this->sub_status) ? $this->status : $this->sub_status);

        return ($status === 'discard') || ($status === 'abuse') || ($status === 'autodiscard');
    }

    public function is_editable()
    {
        global $current_user, $db, $globals;

        if (!$current_user->user_id) {
            return false;
        }

        $mine = $this->author == $current_user->user_id;
        $article = $this->content_type === 'article';

        return (
            ($current_user->admin || ($current_user->user_id == $this->sub_owner))

            || (
                $mine
                && (($this->sub_status === 'queued') || (($this->status === 'discard') && ($this->votes == 0)))
                && ($article || (($globals['now'] - $this->sent_date) < $globals['link_edit_time']))
            ) || (
                !$mine
                && $current_user->special
                && ($this->sub_status === 'queued')
                && (($globals['now'] - $this->sent_date) < $globals['link_edit_special_time'])
            ) || (
                !$mine
                && ($current_user->user_level === 'blogger')
                && (($globals['now'] - $this->date) < $globals['link_edit_blogger_time'])
            )
        );
    }

    public function is_map_editable()
    {
        global $current_user, $db, $globals;

        if (!$globals['google_maps_in_links'] || !$current_user->user_id || $this->votes < 1) {
            return false;
        }

        return (
            $current_user->admin

            || (
                $this->author == $current_user->user_id
                && $current_user->user_level === 'normal'
                && $globals['now'] - $this->sent_date < 9800
            ) || (
                $current_user->special
                && $globals['now'] - $this->sent_date < 14400
            )
        );
    }

    public function is_votable()
    {
        global $globals;

        return $this->votes_enabled = !(
            $globals['bot']
            || ($this->status === 'abuse')
            || ($this->status === 'autodiscard')
            || $this->is_sponsored()
            || (
                $globals['time_enabled_votes'] > 0
                && $this->date < $globals['now'] - $globals['time_enabled_votes']
            )
        );
    }

    public function is_sponsored()
    {
        global $globals;

        if ($globals['sponsored_tag']) {
            return preg_match("/\b".$globals['sponsored_tag']."\b/i", $this->tags);
        }
    }

    public function negatives_allowed($extended = false)
    {
        global $globals, $current_user;

        if ($extended) {
            $period = $globals['time_enabled_votes'];
        } else {
            $period = $globals['time_enabled_negative_votes'];
        }

        // Allows to vote negative to published with high ratio of negatives
        // or a link recently published

        return (
            ($current_user->user_id > 0)
            && ($this->votes > 0)
            && ($this->status !== 'abuse')
            && ($this->status !== 'autodiscard')
            && ($current_user->user_karma >= $globals['min_karma_for_negatives'])
            && (
                ($this->status !== 'published')
                || $this->warned
                || (
                    ($this->status === 'published')
                    && ($this->date > $globals['now'] - $period || $this->negatives > $this->votes / 10)
                )
            )
        );
    }

    public function get_uri()
    {
        global $db, $globals, $routes;

        require_once mnminclude.'uri.php';

        $new_uri = $base_uri = get_uri($this->title);
        $seq = 0;

        // The uri is not equal to a standard uri, from dispatch.php
        while (($seq < 20) && (!empty($routes[$new_uri]) || $db->get_var(
                    "select count(*) from links where link_uri='$new_uri' and link_id != $this->id"
                ))) {
            $seq++;
            $new_uri = $base_uri."-$seq";
        }

        // In case we tried 20 times, we just add the id of the article
        if ($seq >= 20) {
            $new_uri = $base_uri."-$this->id";
        }

        $this->uri = $new_uri;
    }

    public function get_short_permalink()
    {
        global $globals;

        if ($globals['url_shortener']) {
            $server_name = $globals['url_shortener'].'/';
            $id = base_convert($this->id, 10, 36);
        } else {
            $server_name = get_server_name().$globals['base_url'].'story/';
            $id = $this->id;
        }

        return 'http://'.$server_name.$id;
    }

    public function get_relative_permalink($strict = false)
    {
        global $globals;

        if (empty($this->base_url)) {
            $this->base_url = $globals['base_url_general'];
        }

        if ($this->is_sub && ($globals['submnm'] || $strict || self::$original_status || !$this->allow_main_link)) {
            if (!empty($globals['submnm']) && $this->sub_status_id == SitesMgr::my_id(
                ) && !$strict && !self::$original_status
            ) {
                $base = $this->base_url.'m/'.$globals['submnm'].'/';
            } else {
                $base = $this->base_url.'m/'.$this->sub_name.'/';
            }
        } elseif ($this->status === 'private') {
            $base = $this->base_url.'my-story/'.$this->username.'/';
        } else {
            $base = $this->base_url.'story/';
        }

        return $base.(empty($this->uri) ? $this->id : $this->uri);
    }

    public function get_permalink($strict = false, $relative = false)
    {
        global $globals;

        if (empty($globals['server_name'])) {
            $server_name = $this->server_name;
        } else {
            $server_name = $globals['server_name'];
        }

        if (empty($relative)) {
            $relative = $this->get_relative_permalink($strict);
        }

        return $globals['scheme'].'//'.$server_name.$relative;
    }

    public function get_canonical_permalink($page = false)
    {
        global $globals;

        if (empty($globals['canonical_server_name'])) {
            $server_name = $this->server_name;
        } else {
            $server_name = $globals['canonical_server_name'];
        }

        $page = (!$page || $page == 1) ? '' : "/$page";

        return $globals['scheme'].'//'.$server_name.$this->get_relative_permalink(true).$page;
    }

    public function get_trackback()
    {
        global $globals;

        return $globals['scheme'].'//'.get_server_name().$globals['base_url_general'].'trackback.php?id='.$this->id;
    }

    public function get_status_text($status = false)
    {
        $status = $status ?: $this->status;

        switch ($status) {
            case 'abuse':
                return _('abuso');
            case 'discard':
                return _('descartada');
            case 'autodiscard':
                return _('autodescartada');
            case 'queued':
                return _('pendiente');
            case 'published':
                return _('publicada');
        }

        return $status;
    }

    public function get_latlng()
    {
        require_once mnminclude.'geo.php';

        return geo_latlng('link', $this->id);
    }

    public function print_content_type_buttons()
    {
        global $globals;
        $type = array();

        // Is it an image or video?
        switch ($this->content_type) {
            case 'image':
            case 'video':
            case 'text':
                $type[$this->content_type] = 'checked="checked"';
                break;

            default:
                $type['text'] = 'checked="checked"';
                break;
        }

        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo '<input type="radio" '.$type['text'].' name="type" value="text"/>';
        echo '&nbsp;'._('texto').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        echo '<input type="radio" '.$type['image'].' name="type" value="image"/>';
        echo '&nbsp;<img src="'.$globals['base_static'].'img/common/is-photo02.png" class="media-icon" width="18" height="15" alt="'._(
                '¿es una imagen?'
            ).'" title="'._('¿es una imagen?').'" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        echo '<input type="radio" '.$type['video'].' name="type" value="video"/>';
        echo '&nbsp;<img src="'.$globals['base_static'].'img/common/is-video02.png" class="media-icon" width="18" height="15" alt="'._(
                '¿es un vídeo?'
            ).'" title="'._('¿es un vídeo?').'" />';
    }

    public function read_content_type_buttons($type)
    {
        switch ($type) {
            case 'image':
                $this->content_type = 'image';
                break;

            case 'video':
                $this->content_type = 'video';
                break;

            default:
                $this->content_type = 'text';
                break;
        }
    }

    // Calculate real karma of the link
    public function calculate_karma()
    {
        global $db, $globals;

        require_once mnminclude.'ban.php';

        $this->old_karma = round($this->karma);

        if (!$globals['users_karma_avg']) {
            $globals['users_karma_avg'] = (float)$db->get_var(
                "select SQL_NO_CACHE avg(link_votes_avg) from links where link_status = 'published' and link_date > date_sub(now(), interval 72 hour)"
            );
        }

        if (empty($this->annotation)) {
            $this->annotation = '';
        }

        // Read the stored affinity for the author
        $affinity = User::get_affinity($this->author);

        // high =~ users with higher karma greater than average
        // low =~ users with higher karma less-equal than average
        $votes_pos = $votes_neg = $karma_pos_user_high = $karma_pos_user_low = $karma_neg_user = 0;

        $votes_pos_anon = intval(
            $db->get_var(
                "select SQL_NO_CACHE count(*) from votes where vote_type='links' AND vote_link_id=$this->id and vote_user_id = 0 and vote_value > 0"
            )
        );

        $votes = $db->get_results(
            "select SQL_NO_CACHE user_id, vote_value, user_karma from votes, users where vote_type='links' AND vote_link_id=$this->id and vote_user_id > 0 and vote_user_id = user_id and user_level !='disabled'"
        );

        $n = $vlow = $vhigh = $diff = 0;

        foreach ($votes as $vote) {
            if ($vote->vote_value > 0) {
                $votes_pos++;

                if ($affinity && $affinity[$vote->user_id] > 0) {
                    $n++;
                    $diff += ($vote->user_karma - $vote->vote_value);
                    // Change vote_value if there is affinity
                    //echo "$vote->vote_value -> ";
                    $vote->vote_value = max($vote->user_karma * $affinity[$vote->user_id] / 100, 6);
                    //echo "$vote->vote_value ($this->author -> $vote->user_id)\n";
                }

                if ($vote->vote_value >= $globals['users_karma_avg']) {
                    $karma_pos_user_high += $vote->vote_value;
                    $vhigh++;
                } else {
                    $karma_pos_user_low += $vote->vote_value;
                    $vlow++;
                }
            } else {
                $votes_neg++;

                if ($affinity && $affinity[$vote->user_id] < 0) {
                    $karma_neg_user += min(-6, $vote->user_karma * $affinity[$vote->user_id] / 100);
                    //echo "Negativo: " .  min(-5, $vote->user_karma *  $affinity[$vote->user_id]/100) . "$vote->user_id\n";
                    $diff -= ($vote->user_karma + $karma_neg_user);
                } else {
                    $karma_neg_user -= $vote->user_karma;
                }
            }
        }

        echo "Affinity Difference: $diff Base: ".intval(
                $karma_pos_user_high + $karma_pos_user_low + $karma_neg_user
            )." ($n, $votes_pos)\n";

        if ($n > $votes_pos / 5) {
            $this->annotation .= intval($n / $votes_pos * 100)._('% de votos con afinidad elevada')."<br/>";
        }

        $karma_pos_ano = intval(
            $db->get_var(
                "select SQL_NO_CACHE sum(vote_value) from votes where vote_type='links' AND vote_link_id=$this->id and vote_user_id = 0 and vote_value > 0"
            )
        );

        if ($this->votes != $votes_pos || $this->anonymous != $votes_pos_anon || $this->negatives != $votes_neg) {
            $this->votes = $votes_pos;
            $this->anonymous = $votes_pos_anon;
            $this->negatives = $votes_neg;
        }

        // Make sure we don't deviate too much from the average (it avoids vote spams and abuses)
        if ($karma_pos_user_high == 0 || $karma_pos_user_low / $karma_pos_user_high > 1.15) {
            $perc = intval($vlow / ($vlow + $vhigh) * 100);

            $this->low_karma_perc = $perc;
            $this->annotation .= $perc._('% de votos con karma menores que la media')." (".round(
                    $globals['users_karma_avg'],
                    2
                ).")<br/>";
        }

        $karma_pos_user = (int)$karma_pos_user_high + (int)min(
                max($karma_pos_user_high * 1.15, 4),
                $karma_pos_user_low
            ); // Allowed difference up to 15% of $karma_pos_user_high
        $karma_pos_ano = min($karma_pos_user_high * 0.1, $karma_pos_ano);

        // Small quadratic punishment for links having too many negatives
        if ($karma_pos_user + $karma_pos_ano > abs($karma_neg_user) && abs($karma_neg_user) / $karma_pos_user > 0.075) {
            $r = min(max(0, abs($karma_neg_user) * 2 / $karma_pos_user), 0.5);
            $karma_neg_user = max(-($karma_pos_user + $karma_pos_ano), $karma_neg_user * pow((1 + $r), 2));
        }

        // Get met subs coefficientes that will be used below
        $meta_coef = $this->subs_coef_get();

        // BONUS
        // Give more karma to news voted very fast during the first two hours (ish)
        if (
            abs($karma_neg_user) / $karma_pos_user < 0.05
            && $globals['now'] - $this->sent_date < 7200
            && $globals['now'] - $this->sent_date > 600
        ) {
            $this->coef = $globals['bonus_coef'] - ($globals['now'] - $this->sent_date) / 7200;
            // It applies the same meta coefficient to the bonus'
            // Check 1 <= bonus <= $bonus_coef
            $this->coef = max(min($this->coef, $globals['bonus_coef']), 1);
            // if it's has bonus and therefore time-related, use the base min_karma
        } elseif ($karma_pos_user + $karma_pos_ano > abs($karma_neg_user)) {
            // Aged karma
            if ($globals['news_sub'] > 0 && $this->sub_id != $globals['news_sub']) {
                $plain_hours = $globals['karma_start_decay'];
                $max_hours = $globals['karma_decay'];
            } else {
                $plain_hours = $globals['karma_news_start_decay'];
                $max_hours = $globals['karma_news_decay'];
            }

            $d = 3600 * $max_hours * (1 + $globals['min_decay']);
            $diff = max(0, $globals['now'] - ($this->sent_date + $plain_hours * 3600));
            $c = 1 - $diff / $d;
            $c = max($globals['min_decay'], $c);
            $c = min(1, $c);

            $this->coef = $c;
        } else {
            $this->coef = 1;
        }

        if ($this->coef < .99) {
            $this->annotation .= _('Noticia «antigua»')."<br/>";
        } elseif ($this->coef > 1.01) {
            $this->annotation .= _('Bonus por noticia reciente')."<br/>";
        }

        /*
         * DISABLED: global affinity votes behaves better
         *
        // Give the "new source" only if if has less than %5 of negative karma
        if (abs($karma_neg_user)/$karma_pos_user < 0.05) {
        $c = $this->calculate_source_bonus();
        if ($c > 1) {
        $this->coef = min($globals['bonus_coef'], $this->coef*$c);
        $c = round($c, 2);
        $this->annotation .= _('Bonus por fuente esporádica'). " ($c)<br/>";
        }
        }
         */

        $this->karma = ($karma_pos_user + $karma_pos_ano + $karma_neg_user) * $this->coef;

        if ($meta_coef && $meta_coef[$this->sub_id]) {
            $this->karma *= $meta_coef[$this->sub_id];

            // Annotate meta's coeeficient if the variation > 5%
            if (abs(1 - $meta_coef[$this->sub_id]) > 0.05) {
                $this->annotation .= _('Coeficiente sub').' ('.$this->sub_name.') : '.round(
                        $meta_coef[$this->sub_id],
                        2
                    )."<br/>";
            }
        }

        // Give a small bonus (= $w) to links according to their clicks
        if (
            !empty($this->url)
            && $this->karma > 0
            && $globals['click_counter']
            && $this->id >= $globals['click_counter']
            && $globals['karma_clicks_bonus'] > 0
            && $this->negatives < $this->votes / 5
        ) {
            $w = $globals['karma_clicks_bonus'];

            $this->clicks = $this->get_clicks(); // Just in case it was not read

            $c = $this->clicks / ($this->total_votes + $this->negatives) - 0.5;
            $c = max($c, 0.005); // Be sure not to calculate log of zero or negative
            $c = $w * log($c);
            $c = min(0.6, $c);
            $c = max($c, -0.3);
            $bonus = round($this->karma * $c);

            $this->annotation .= _('Bonus clics').": $bonus<br/>";
            $this->karma += $bonus;
        }

        $this->karma = round($this->karma);
    }

    // Bonus for sources than are not frequently sent
    public function calculate_source_bonus()
    {
        global $db, $globals;

        $hours = $db->get_var(
            "select ($this->date - unix_timestamp(link_date))/3600 from links where link_blog=$this->blog and link_id < $this->id order by link_id desc limit 1"
        );

        if (!isset($hours) || $hours > $globals['new_source_max_hours']) {
            $hours = $globals['new_source_max_hours'];
        }

        if ($hours >= 24) {
            return 1 + ($globals['new_source_bonus'] - 1) * ($hours - $globals['new_source_min_hours']) / ($globals['new_source_max_hours'] - $globals['new_source_min_hours']);
        }

        return 0;
    }

    public function save_annotation($key, $site_name = false)
    {
        global $globals;

        $key .= "-$this->id";
        $log = new Annotation($key);

        if ($log->read()) {
            $array = unserialize($log->text);
        }

        if (!$array || !is_array($array)) {
            $array = array();
        }

        $dict = array();
        $dict['site_name'] = $site_name;
        $dict['time'] = time();
        $dict['positives'] = $this->votes;
        $dict['negatives'] = $this->negatives;
        $dict['anonymous'] = $this->anonymous;
        $dict['clicks'] = $this->clicks;

        if (empty($this->old_karma)) {
            $this->old_karma = $this->karma;
        }

        $dict['old_karma'] = $this->old_karma;
        $dict['karma'] = $this->karma;
        $dict['coef'] = sprintf("%.2f", $this->coef);
        $dict['annotation'] = $this->annotation;

        array_unshift($array, $dict);

        $log->text = serialize($array);
        $log->store();

        $this->annotation = '';
    }

    public function read_annotation($key)
    {
        global $globals;

        $log = new Annotation($key."-$this->id");

        if ($log->read()) {
            $array = unserialize($log->text);
        }

        return ($array && is_array($array)) ? $array : array();
    }

    public function time_annotation($key)
    {
        $log = Annotation::from_db($key."-$this->id");

        return $log ? $log->time : 0;
    }

    // Read affinity values using annotations
    public function subs_coef_get()
    {
        global $globals;

        if (empty($globals['sub_balance_metas']) || !in_array(SitesMgr::my_id(), $globals['sub_balance_metas'])) {
            return false;
        }

        $log = new Annotation("subs-coef-".SitesMgr::my_id());

        if (!$log->read()) {
            return false;
        }

        $dict = unserialize($log->text);

        return ($dict && is_array($dict)) ? $dict : false;
    }

    public function get_thumb($debug = false, $url = false)
    {
        global $globals;

        $site = false;

        if (empty($this->url) && !$this->read()) {
            return false;
        }

        $blog = new Blog();
        $blog->id = $this->blog;

        if ($blog->read()) {
            $site = $blog->url;
        }

        if (empty($url)) {
            $this->image_parser = new HtmlImages($this->url, $site);
            $this->image_parser->debug = $debug;
            $this->image_parser->referer = $this->get_permalink();
        } else {
            $this->image_parser = new HtmlImages($url);
        }

        if ($debug) {
            echo "<!-- Meneame, before image_parser -->\n";
        }

        $img = $this->image_parser->get();

        if ($debug) {
            echo "<!-- Meneame, after image_parser: $img->url -->\n";
        }

        $this->thumb_status = 'checked';
        $this->thumb = '';

        if ($img) {
            $filepath = Upload::get_cache_dir()."/tmp/thumb-$this->id.jpg";
            $thumbnail = $img->scale($globals['medium_thumb_size']);
            $thumbnail->save($filepath, IMAGETYPE_JPEG);

            if ($this->move_tmp_image(basename($filepath), 'image/jpeg')) {
                $this->image_parser->seen_add($img->url);
                $this->thumb_status = 'remote';

                if ($debug) {
                    echo "<!-- Meneame, new thumbnail $img->url -->\n";
                }
            } else {
                $this->thumb_status = 'error';

                if ($debug) {
                    echo "<!-- Meneame, error saving thumbnail ".$this->get_permalink()." -->\n";
                }
            }
        }

        $this->store_thumb_status();

        return $this->has_thumb();
    }

    public function store_thumb_status()
    {
        global $db;

        $this->thumb = $db->escape($this->thumb);

        $db->query("update links set link_thumb_status = '$this->thumb_status' where link_id = $this->id");
    }

    public function delete_thumb($base = '')
    {
        global $globals;

        if (!$this->media_size) {
            return;
        }

        $this->delete_image();
        $this->thumb_status = 'deleted';
        $this->store_thumb_status();
    }

    public function has_thumb()
    {
        global $globals;

        if (!empty($this->thumb_url)) {
            return $this->thumb_url;
        }

        if (!$this->media_size > 0) {
            // New format
            return $this->thumb_url = false;
        }

        $base = $globals['base_static_noversion'];

        $this->thumb_uri = Upload::get_cache_relative_dir(
                $this->id
            )."/media_thumb-link-$this->id.$this->media_extension?$this->media_date";
        $this->thumb_url = $base.$this->thumb_uri;
        $this->media_url = Upload::get_url('link', $this->id, 0, $this->media_date, $this->media_mime);
        $this->thumb_x = $this->thumb_y = $globals['thumb_size'];

        return $this->thumb_url;
    }

    public function get_related($max = 10)
    {
        global $globals, $db;

        $related = array();
        $phrases = 0;

        // Only work with sphinx
        if (!$globals['sphinx_server']) {
            return $related;
        }

        require_once mnminclude.'search.php';

        $maxid = $db->get_var("select max(link_id) from links");

        if ($this->status === 'published') {
            $_REQUEST['s'] = '! abuse discard autodiscard';
        }

        $words = array();
        $freqs = array();
        $hits = array();
        $freq_min = 1;

        // Filter title
        $a = preg_split(
            '/[\s,\.;:“”–\"\'\-\(\)\[\]«»<>\/\?¿¡!]+/u',
            preg_replace(
                '/[\[\(] *\w{1,6} *[\)\]] */',
                ' ',
                htmlspecialchars_decode($this->title, ENT_QUOTES)
            ) // delete [lang] and (lang), -1, PREG_SPLIT_NO_EMPTY
        );
        $i = 0;
        $n = count($a);

        foreach ($a as $w) {
            $w = unaccent($w);
            $wlower = mb_strtolower($w);
            $len = mb_strlen($w);

            if (
                !isset($words[$wlower])
                && ($len > 3 || preg_match('/^[A-Z]{2,}$/', $w))
                && !preg_match('/^\d{1,3}\D{0,1}$/', $w)
            ) {
                $h = sphinx_doc_hits($wlower);
                $hits[$wlower] = $h;

                // If 0 or 1 it won't help to the search, too frequents neither
                if ($h < 1 || $h > $maxid / 10) {
                    continue;
                }

                // Store the frequency
                $freq = $h / $maxid;

                if (!isset($freqs[$wlower]) || $freqs[$wlower] > $freq) {
                    $freqs[$wlower] = $freq;
                }

                if ($freq < $freq_min) {
                    $freq_min = max(0.0001, $freq);
                }

                if (preg_match('/^[A-Z]/', $w) && $len > 2) {
                    $coef = 2 * log10($maxid / $h);
                } else {
                    $coef = 2;
                }

                // Increase coefficient if a name appears also in tags
                // s{0,1} is a trick for plurals, until we use stemmed words
                if (preg_match('/(^|[ ,])'.preg_quote($w).'s{0,1}([ ,]|$)/ui', $this->tags)) {
                    $coef *= 2;

                    // It's the first or last word
                    if ($i == 0 || $i == $n - 1) {
                        $coef *= 2;
                    }
                }

                $words[$wlower] = intval($h / $coef);
            }

            $i++;
        }

        // Filter tags
        $a = preg_split('/,+/', $this->tags, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($a as $w) {
            $w = trim($w);
            $wlower = mb_strtolower(unaccent($w));
            $len = mb_strlen($w);

            if (isset($words[$wlower])) {
                continue;
            }

            if (preg_match('/\s/', $w)) {
                $wlower = "\"$wlower\"";
                $phrases++;
            }

            $h = sphinx_doc_hits($wlower);
            $hits[$wlower] = $h;

            // If 0 or 1 it won't help to the search, too frequents neither
            if ($h < 1 || $h > $maxid / 10) {
                continue;
            }

            // Store the frequency
            $freq = $h / $maxid;

            if (!isset($freqs[$wlower]) || $freqs[$wlower] > $freq) {
                $freqs[$wlower] = $freq;
            }

            if ($freq < $freq_min) {
                $freq_min = max(0.0001, $freq);
            }

            $words[$wlower] = intval($h / 2);
        }

        // Filter content, check length and that it's begin con capital
        $a = preg_split(
            '/[\s,\.;:“”–\"\'\-\(\)\[\]«»<>\/\?¿¡!]+/u',
            preg_replace('/https{0,1}:\/\/\S+|[\[\(] *\w{1,6} *[\)\]]/i', '', $this->sanitize($this->content)),
            // Delete parenthesided and links too
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        foreach ($a as $w) {
            $wlower = mb_strtolower(unaccent($w));

            if (!preg_match('/^[A-Z][a-zA-Z]{2,}/', $w)) {
                continue;
            }

            $len = mb_strlen($w);

            if (
                !isset($words[$wlower])
                && ($len > 2 || preg_match('/^[A-Z]{2,}$/', $w))
                && !preg_match('/^\d{1,3}\D{0,1}$/', $w)
            ) {
                $h = sphinx_doc_hits($wlower);
                $hits[$wlower] = $h;

                // If 0 or 1 it won't help to the search, too frequents neither
                if ($h < 1 || $h > $maxid / 50) {
                    continue;
                }

                if (preg_match('/^[A-Z]/', $w) && $h < $maxid / 1000) {
                    $coef = max(log10($maxid / $h) - 1, 1);
                } else {
                    $coef = 1;
                }

                $words[$wlower] = intval($h / $coef);
            }
        }

        // Increase "hits" proportional to word's lenght
        // because longer words tends to appear less
        foreach ($words as $w => $v) {
            $len = mb_strlen($w);

            if ($len > 6 && !preg_match('/ /', $w)) {
                $words[$w] = $v * $len / 6;
            }
        }

        asort($words);

        $i = 0;
        $text = '';

        foreach ($words as $w => $v) {
            // Filter words if we got good candidates
            // echo "<!-- $w: ".$freqs[$w]." coef: ".$words[$w]."-->\n";
            if ($i > 4 && $freq_min < 0.005 && strlen(
                    $w
                ) > 3 && (empty($freqs[$w]) || $freqs[$w] > 0.01 || $freqs[$w] > $freq_min * 100)
            ) {
                continue;
            }

            $i++;

            if ($i > 14 or ($i > 8 && $v > $maxid / 2000)) {
                break;
            }

            $text .= "$w ";
        }

        echo "\n<!-- Search terms: $text Phrases: $phrases -->\n";

        $_REQUEST['q'] = $text;

        // Center the date about the the link's date
        $_REQUEST['root_time'] = $this->date;

        $this->old = (($globals['now'] - $this->date) > 86400 * 5);

        $response = do_search(false, 0, $max + 1, false);

        if (empty($response) || empty($response['ids'])) {
            return array();
        }

        foreach ($response['ids'] as $id) {
            if ($id == $this->id) {
                continue;
            }

            $l = Link::from_db($id);

            if (!$l) {
                continue;
            }

            if (empty($l->permalink)) {
                $l->permalink = $l->get_permalink();
            }

            $related[] = $l;
        }

        return $related;
    }

    public function get_clicks()
    {
        global $db, $globals;

        if ($globals['click_counter'] && $this->id > $globals['click_counter'] && !$this->clicks > 0) {
            $this->clicks = intval($db->get_var("select counter from link_clicks where id = $this->id"));
        }

        return $this->clicks;
    }

    public function user_clicked()
    {
        return (isset($_COOKIE['v']) && preg_match('/(x|^)'.$this->id.'(x|$)/', $_COOKIE['v']));
    }

    public function calculate_common_votes()
    {
        global $db, $globals;

        $this->mean_common_votes = false;

        $previous = $db->get_row(
            "select value, n, UNIX_TIMESTAMP(date) as date, UNIX_TIMESTAMP(created) as created from link_commons where link = $this->id and created > date_sub(now(), interval 3 hour)"
        );

        if ($previous && $previous->n > 0 && $previous->date > 0) {
            $this->mean_common_votes = $previous->value;
            $from_date = $previous->date;
            $created = $previous->created;
        } else {
            $from_date = 0;
            $created = time();
        }

        if ($this->status === 'published') {
            $to_date = "and vote_date < FROM_UNIXTIME($this->date)";
        } else {
            $to_date = '';
        }

        $votes = $db->get_results(
            "select vote_user_id, vote_value, UNIX_TIMESTAMP(vote_date) as date from votes where vote_type = 'links' and vote_link_id = $this->id and vote_user_id > 0 and vote_value > 0 $to_date order by vote_id asc"
        );

        if (!$votes) {
            return $this->mean_common_votes;
        }

        $users = array();
        $total_values = 0;
        $values_total = 0;
        $last_date = 0;

        foreach ($votes as $vote) {
            $users[$vote->vote_user_id] = $vote->date; //round($vote->vote_user_id/abs($vote->vote_user_id));

            if ($vote->date > $last_date) {
                $last_date = $vote->date;
            }
        }

        foreach ($users as $x => $xdate) {
            foreach ($users as $y => $ydate) {
                if ($x >= $y || ($xdate >= $from_date && $ydate <= $from_date)) {
                    continue;
                }

                $common = $db->get_row(
                    "select value, UNIX_TIMESTAMP(date) as date from users_similarities where minor = $x and major = $y"
                );

                if (!$common) {
                    $value = 0;
                } else {
                    if ($globals['now'] - $common->date > 86400) {
                        $coef = 1 - min(1, ($globals['now'] - $common->date) / (86400 * 60));
                    } else {
                        $coef = 1;
                    }

                    $value = $common->value * $coef;
                }

                $values_total += $value;
                $total_values++;
            }
        }

        if ($previous && $previous->n > 0 && $previous->date > 0) {
            $values_total += $previous->value * $previous->n;
            $total_values += $previous->n;
        }

        $this->mean_common_votes = $values_total / $total_values;

        $db->query(
            "REPLACE link_commons (link, value, n, date, created) VALUES ($this->id, $this->mean_common_votes, $total_values, FROM_UNIXTIME($last_date), FROM_UNIXTIME($created))"
        );

        return $this->mean_common_votes;
    }

    public function get_current_sub_status_and_date()
    {
        if (self::$original_status) {
            $this->status = $this->sub_status_origen;
            $this->sub_date = $this->sub_date_origen; // Show the published date of the original sub
        } elseif (!empty($this->sub_status)) {
            $this->status = $this->sub_status;
        }
    }

    /* TODO: Used in backend/sneaker2.php, could be avoided */
    public function get_a_status()
    {
        $this->get_current_sub_status_and_date();

        return $this->status;
    }

    public function check_field_errors()
    {
        global $globals;

        if (!$this->sub_id && ($this->content_type !== 'article')) {
            return _('No has seleccionado ningún SUB');
        }

        if ($this->sub_id) {
            $properties = SitesMgr::get_extended_properties($this->sub_id);

            if (empty($this->url) && empty($properties['no_link'])) {
                return _('En este SUB es obligatorio enviar contenidos mediante enlace');
            }
        }

        $title = $this->get_title_fixed();
        $content = $this->get_content_fixed();

        if (mb_strlen($title) < 8) {
            return __('El título es demasiado corto, debe ser al menos de %s caracteres', 8);
        }

        if (mb_strlen($title) > 100) {
            return __('El título es demasiado largo, debe ser como máximo %s caracteres', 100);
        }

        if (get_uppercase_ratio($title) > 0.5) {
            return _('Hay demasiadas mayúsculas en el título');
        }

        if ($properties['intro_max_len'] > 0 && $properties['intro_min_len'] > 0 && mb_strlen(
                $content
            ) < $properties['intro_min_len'] && ($this->content_type !== 'article')
        ) {
            return __('El texto es demasiado corto, debe ser al menos de %s caracteres', $properties['intro_min_len']);
        }

        if (($this->content_type !== 'article') && get_uppercase_ratio($content) > 0.3) {
            return _('Hay demasiadas mayúsculas en el texto');
        }

        if (strlen($this->tags) < 3) {
            return _('No has puesto etiquetas');
        }

        if (preg_match('/.*http:\//', $title)) {
            return _('No puedes añadir enlaces en el título');
        }
    }

    public function get_title_fixed()
    {
        return clean_text(preg_replace('/(\w) *[.,] *$/', "$1", $this->title), 50, true, 120);
    }

    public function get_content_fixed()
    {
        $properties = SitesMgr::get_extended_properties($this->sub_id);
        $replace_nl = $properties['allow_paragraphs'] ? false : true;

        if ($this->content_type === 'article') {
            return clean_html_with_tags($this->content, 0, $replace_nl, $properties['intro_max_len']);
        }

        return clean_text_with_tags($this->content, 0, $replace_nl, $properties['intro_max_len']);
    }

    public function get_media($type = null)
    {
        return parent::get_media('link');
    }

    public function store_image_from_form($field = 'image', $type = null)
    {
        return parent::store_image_from_form('link', $field);
    }

    public function store_image($file, $type = null)
    {
        return parent::store_image('link', $file);
    }

    public function move_tmp_image($file, $mime, $type = null)
    {
        return parent::move_tmp_image('link', $file, $mime);
    }

    public function delete_image($type = null)
    {
        return parent::delete_image('link');
    }

    /**
     * Get tags as array
     * return array
     */
    public function get_tags()
    {
        if (empty($this->tags)) {
            return array();
        }

        $tags = array_map(
            'trim',
            explode(",", $this->tags)
        );

        return $tags;
    }

    /**
     * Get date
     * return mixed false | string
     */
    public function get_date()
    {
        if (empty($this->date)) {
            return false;
        }

        $date = new DateTime();
        $date->setTimestamp($this->date);

        return $date;
    }

    /**
     * Get creator uri
     * return string
     */
    public function get_creator_uri()
    {
        global $globals;

        return $globals['scheme'].'//'.$globals['server_name'].get_user_uri($this->username);
    }

    /**
     * Get creator avatar
     * return string
     */
    public function get_creator_avatar()
    {
        global $globals;

        return $globals['scheme'].get_avatar_url($this->author, $this->avatar, 25, false);
    }

    public static function getUserArticleDraftsCount()
    {

        global $current_user, $db;

        $query = '
    FROM links
    WHERE (
        link_author = "'.(int)$current_user->user_id.'"
        AND link_status = "discard"
        AND link_content_type = "article"
        AND link_votes = 0
    )
';

        $count = $db->get_var('SELECT COUNT(*) '.$query.';');

        return $count;

    }

}