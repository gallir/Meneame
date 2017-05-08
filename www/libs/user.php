<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

global $globals;

// Following functions are related to users but not done as a class so can be easily used with User and UserAuth
define('FRIEND_YES', '<img src="'.$globals['base_static'].'img/common/icon_friend_00.png" alt="del" width="18" height="16" title="'._('amigo').'"/>');
define('FRIEND_BOTH', '<img src="'.$globals['base_static'].'img/common/icon_friend_bi_00.png" alt="del" width="18" height="16" title="'._('amigos').'"/>');
define('FRIEND_NO', '<img src="'.$globals['base_static'].'img/common/icon_friend_no_00.png" alt="add" width="18" height="16" title="'._('agregar lista amigos').'"/>');
define('FRIEND_OTHER', '<img src="'.$globals['base_static'].'img/common/icon_friend_other_00.png" alt="add" width="18" height="16" title="'._('elegido').'"/>');
define('FRIEND_IGNORE', '<img src="'.$globals['base_static'].'img/common/icon_friend_ignore_00.png" alt="add" width="18" height="16" title="'._('ignorar').'"/>');

class User
{
    protected $meta = false; // Used to store further user's info
    protected $meta_modified = false;

    public $read = false;
    public $id = 0;
    public $username = '';
    public $username_register = '';
    public $level = 'normal';
    public $admin = false;
    public $modification = false;
    public $date = false;
    public $ip = '';
    public $pass = '';
    public $email = '';
    public $email_register = '';
    public $avatar = 0;
    public $names = '';
    public $lang = 1;
    public $comment_pref = 0;
    public $karma = 6;
    public $url = '';
    public $prefs = array();

    private $friendship;

    const SQL = "user_id as id, user_login as username, user_login_register as username_register, user_level as level, user_comment_pref as comment_pref, UNIX_TIMESTAMP(user_date) as date, user_ip as ip, UNIX_TIMESTAMP(user_modification) as modification, user_pass as pass, user_email as email, user_email_register as email_register, user_names as names, user_lang as lang, user_karma as karma, user_avatar as avatar, user_public_info as public_info, user_url as url, user_adcode as adcode, user_adchannel as adchannel, user_phone as phone";

    public static function get_notification($id, $type)
    {
        global $db;

        $r =  intval($db->get_var("select counter from notifications where user = $id and type = '$type'"));

        if ($r < 0) {
            User::reset_notification($id, $type);
            return 0;
        }
        return $r;
    }

    public static function add_notification($id, $type, $value = 1)
    {
        global $db;

        if (is_null(User::get_notification($id, $type))) {
            return false;
        }

        if ($value < 0) {
            $value = abs($value);
            return $db->query("update notifications set counter = counter-$value where user=$id and type = '$type' and counter >= $value");
        }

        return $db->query("insert into notifications (user, type, counter) values ($id, '$type', $value) on duplicate key update counter=counter+$value");
    }

    public static function reset_notification($id, $type, $value = 0)
    {
        global $db;

        return $db->query("replace into notifications (user, type, counter) values ($id, '$type', $value)");
    }

    public static function get_valid_username($name)
    {
        $name = strip_tags($name);
        $name = preg_replace('/&.+?;/', '', $name); // kill entities
        $name = preg_replace('/[\s\'\"\(\)]/', '_', $name);

        // Don't let start with a number
        if (preg_match('/^\d/', $name)) {
            $name = 'u_' . $name;
        }

        return substr($name, 0, 24);
    }

    public static function get_username($id)
    {
        global $db;

        $id = intval($id);

        return $db->get_var("select user_login from users where user_id = $id");
    }

    public static function get_user_id($name)
    {
        global $db;

        $name = $db->escape($name);

        return $db->get_var("select user_id from users where user_login = '$name'");
    }

    public static function calculate_affinity($uid, $min_karma = 200)
    {
        global $globals, $db;

        if (!$globals['karma_user_affinity']) {
            return false;
        }

        $affinity = array();
        $log = new Annotation("affinity-$uid");

        if ($log->read() && $log->time > time() - 3600*4) {
            return unserialize($log->text);
        }

        // Check vote-to-links affinity
        $link_ids = $db->get_col("SELECT SQL_NO_CACHE link_id FROM links WHERE link_date > date_sub(now(), interval 30 day) and link_author = $uid and link_karma > $min_karma");
        $nlinks = count($link_ids);

        if ($nlinks > 4) {
            $links = implode(',', $link_ids);
            $votes = $db->get_results("select SQL_NO_CACHE vote_user_id as id, sum(vote_value/abs(vote_value)) as count from votes where vote_link_id in ($links) and vote_type='links' group by vote_user_id");

            if ($votes) {
                foreach ($votes as $vote) {
                    if ($vote->id > 0 && $vote->id != $uid && abs($vote->count) > max(1, $nlinks/10)) {
                        $w = min(1, $nlinks/10);
                        $w = max(0.7, $w);
                        $c = $vote->count/$nlinks * $w;

                        if ($vote->count > 0) {
                            $affinity[$vote->id] = round((1 - $c)*100);  // store as int (percent) to save space,
                        } else {
                            $affinity[$vote->id] = round((-1 - $c)*100);  // store as int (percent) to save space,
                        }
                    }
                }
            }
        }

        // Check vote-to-comments affinity
        $comment_ids = $db->get_col("SELECT SQL_NO_CACHE comment_id FROM comments WHERE comment_date > date_sub(now(), interval 3 day) and comment_user_id = $uid and comment_votes > 2");
        $ncomments = count($comment_ids);

        if ($ncomments > 4) {
            $comments = implode(',', $comment_ids);
            $votes = $db->get_results("select SQL_NO_CACHE vote_user_id as id, sum(vote_value/abs(vote_value)) as count from votes where vote_link_id in ($comments) and vote_type='comments' group by vote_user_id");

            if ($votes) {
                foreach ($votes as $vote) {
                    if ($vote->id > 0 && $vote->id != $uid && abs($vote->count) > max(1, $ncomments / 10)) {
                        $w = min(1, $ncomments / 15);
                        $w = max(0.5, $w);
                        $c = $vote->count / $ncomments * $w;

                        if ($vote->count > 0) {
                            $a = round((1 - $c) * 100);

                            if (!isset($affinity[$vote->id]) || $a < $affinity[$vote->id]) {
                                $affinity[$vote->id] = $a;
                            }
                        } else {
                            $a = round((-1 - $c) * 100);

                            if (!isset($affinity[$vote->id]) || ($affinity[$vote->id] < 0 && $a > $affinity[$vote->id])) {
                                $affinity[$vote->id] = $a;
                            }
                        }
                    }
                }
            }
        }

        if (count($affinity) > 0) {
            $log->text = serialize($affinity);
        } else {
            $affinity = false;
            $log->text = '';
        }

        $log->store(time() + 86400*15); // Expire in 15 days

        return $affinity;
    }

    public static function get_new_friends($user = 0)
    {
        global $db, $globals, $current_user;

        $key = 'last_friend';

        if (!$user && $current_user->user_id > 0) {
            $user = $current_user->user_id;
        }

        $last_read = intval($db->get_var("select pref_value from prefs where pref_user_id = $user and pref_key = '$key'"));

        return $db->get_col("select friend_from from friends where friend_type = 'manual' and friend_to = $user and friend_value > 0 and friend_date > FROM_UNIXTIME($last_read)");
    }

    public static function update_new_friends_date($time = false)
    {
        global $db, $globals, $current_user;

        $key = 'last_friend';

        if (!$current_user->user_id) {
            return false;
        }

        if (!$time) {
            $time = $globals['now'];
        }

        $previous = (int) $db->get_var("select pref_value from prefs where pref_user_id = $current_user->user_id and pref_key = '$key'");

        if ($time <= $previous) {
            return true;
        }

        $db->transaction();
        $db->query("delete from prefs where pref_user_id = $current_user->user_id and pref_key = '$key'");
        $db->query("insert into prefs set pref_user_id = $current_user->user_id, pref_key = '$key', pref_value = $time");
        $db->commit();

        return true;
    }

    // $user_id is the key in annotations
    public static function get_affinity($id, $from = false)
    {
        global $current_user, $globals;

        if (!$globals['karma_user_affinity']) {
            return false;
        }

        $log = new Annotation("affinity-$id");

        if (!$log->read()) {
            return false;
        }

        $dict = unserialize($log->text);

        if (!$dict || ! is_array($dict)) {
            return false; // Failed to unserialize
        }

        if (!$from) {
            return $dict; // Asked for the whole dict
        }

        return (abs($dict[$from]) <= 100) ? intval($dict[$from]) : false;
    }

    // Functions to manage "meta variables" that willl be stored as annotations and read automatically

    // Variables that are accepted as "meta" (to avoid storing all
    public static function meta_valid($property)
    {
        return in_array($property, array('bio', 'karma_log', 'karma_calculated'));
    }

    // Return the items for the top menu
    // Used by /user.php and /profile.php
    public static function get_menu_items($view, $user)
    {
        global $globals, $current_user;

        switch ($view) {
            case 'articles':
            case 'articles_private':
            case 'articles_shaken':
            case 'articles_favorites':
            case 'articles_discard':
                $id = _('Artículos');
                break;

            case 'subs':
            case 'subs_follow':
                $id = _('subs');
                break;

            case 'history':
            case 'shaken':
            case 'friends_shaken':
            case 'favorites':
            case 'discard':
                $id = _('historias');
                break;

            case 'commented':
            case 'favorite_comments':
            case 'shaken_comments':
            case 'conversation':
                $id = _('comentarios');
                break;

            case 'friends':
            case 'friend_of':
            case 'ignored':
            case 'friends_new':
                $id = _('relaciones');
                break;

            case 'categories':
            case 'profile':
                $id = _('perfil');
                break;

            case 'notes':
            case 'notes_friends':
            case 'notes_favorites':
            case 'notes_conversation':
            case 'notes_votes':
                $id = _('notas');
                break;

            case 'notes_privates':
                $id = _('privados');
                break;

            default:
                do_error(_('opción inexistente'), 404);
                break;
        }

        $items = array();

        if ($user->id == $current_user->user_id) {
            $items[] = new MenuOption(_('Crear artículo'), $globals['base_url'] . 'submit?type=article&write=true', $id, _('enviar nueva historia'), 'submit_new_post');
        }

        $items[] = new MenuOption(_('perfil'), $user->get_uri('profile'), $id, _('Información de usuario'));
        $items[] = new MenuOption(_('historias'), $user->get_uri('history'), $id, _('Información de envíos'));
        $items[] = new MenuOption(_('subs'), $user->get_uri('subs'), $id, _('Sub menéames'));
        $items[] = new MenuOption(_('comentarios'), $user->get_uri('commented'), $id, _('Información de comentarios'));
        $items[] = new MenuOption(_('notas'), $user->get_uri('notes'), $id, _('Página de notas'));
        $items[] = new MenuOption(_('relaciones'), $user->get_uri('friends'), $id, _('Amigos e ignorados'));

        if ($user->id == $current_user->user_id) {
            $items[] = new MenuOption(_('privados'), $user->get_uri('notes_privates'), $id, _('Notas privadas'));
        }

        return $items;
    }

    public function __get($property)
    {
        if (!$this->id > 0 || ! User::meta_valid($property)) {
            return false;
        }

        if ($this->meta === false && ! $this->meta_read()) {
            return false;
        }

        if (isset($this->meta[$property])) {
            return $this->meta[$property];
        }

        return false;
    }

    public function __set($property, $value)
    {
        if (!$this->id > 0) {
            return;
        }

        if (!User::meta_valid($property)) {
            $this->$property = $value;
            return;
        }

        if ($this->meta === false) {
            $this->meta_read();
        }

        $this->meta[$property] = $value;
        $this->meta_modified = true;
    }

    public function meta_read()
    {
        $m = new Annotation("user_meta-$this->id");

        if ($m->read() && ($this->meta = json_decode($m->text, true))) {
            return true;
        }

        $this->meta = array();

        return false;
    }

    public function meta_store()
    {
        if (!is_array($this->meta)) {
            return;
        }

        $m = new Annotation("user_meta-$this->id");
        $m->text = json_encode($this->meta);
        $m->store();

        $this->meta_modified = false;
    }

// END meta

    public function __construct($id = 0)
    {
        /*
        // For stats
        $this->total_votes;
        $this->total_links;
        $this->published_links;
        $this->total_comments;
        $this->total_posts;
        */

        if ($id > 0) {
            $this->id = $id;
            $this->read();
        }
    }

    public function disabled()
    {
        return ($this->level === 'disabled') || ($this->level === 'autodisabled');
    }

    public function disable($auto = false)
    {
        global $db;

        require_once(mnminclude.'avatars.php');
        require_once(mnminclude.'geo.php');

        avatars_remove($this->id);
        geo_delete('user', $this->id);

        $this->username = '--'.$this->id.'--';
        $this->email = "$this->id@disabled";
        $this->url = '';

        $this->level = $auto ? 'autodisabled' : 'disabled';

        $this->names = 'disabled';
        $this->public_info = '';
        $this->adcode = '';
        $this->adchannel = '';
        $this->phone = '';
        $this->avatar = 0;
        $this->karma = 6;
        $this->store();

        syslog(LOG_INFO, "User disabled: $this->id");

        // Delete relationships
        $db->query("DELETE FROM friends WHERE friend_type='manual' and (friend_from = $this->id or friend_to = $this->id)");

        /*
        // Delete posts' conversations
        $db->query("delete from conversations where conversation_type = 'post' and conversation_user_to = $this->id");

        $db->transaction();
        $conv = $db->get_col("select post_id from posts where post_user_id = $this->id");
        if ($conv) {
            foreach ($conv as $id) {
                $db->query("delete from conversations where conversation_type = 'post' and conversation_from = $id");
            }
        }
        $db->commit();
        */

        // Delete posts
        $db->query("delete from posts where post_user_id = $this->id");

        // Delete user's meta
        $db->query("delete from annotations where annotation_key = 'user_meta-$this->id'");

        // Delete preferences
        $db->query("DELETE FROM prefs WHERE pref_user_id = $this->id");

        return true;
    }

    public function friendship()
    {
        global $db, $current_user;

        if (($this->friendship === null) && $this->id && $current_user->user_id) {
            $this->friendship = self::friend_exists($current_user->user_id, $this->id);
        }

        return $this->friendship;
    }

    public function ignored()
    {
        global $current_user;

        return !$current_user->admin && ($this->friendship() === -1);
    }

    public function friend()
    {
        return ($this->friendship() === 1);
    }

    public function store($full_save = true)
    {
        global $db, $current_user, $globals;

        if (!$this->date) {
            $this->date = $globals['now'];
        }

    /*
        if($full_save && empty($this->ip)) {
            $this->ip=$globals['user_ip'];
        }
        */
        $user_login = $db->escape($this->username);
        $user_login_register = $db->escape($this->username_register);
        $user_level = $this->level;
        $user_comment_pref = $this->comment_pref;
        $user_karma = $this->karma;
        $user_avatar = $this->avatar;
        $user_date = $this->date;
        $user_ip = $this->ip;
        $user_pass = $db->escape($this->pass);
        $user_lang = $this->lang;
        $user_email = $db->escape($this->email);
        $user_email_register = $db->escape($this->email_register);
        $user_names = $db->escape($this->names);
        $user_public_info = $db->escape(__($this->public_info));
        $user_url = $db->escape(preg_replace('/\/+$/', '', htmlspecialchars($this->url))); // remove trailing "/s"
        $user_adcode = $db->escape($this->adcode);
        $user_adchannel = $db->escape($this->adchannel);
        $user_phone = $db->escape($this->phone);

        if (!$this->id) {
            $db->query("INSERT INTO users (user_login, user_login_register, user_level, user_karma, user_date, user_ip, user_pass, user_lang, user_email, user_email_register, user_names, user_public_info, user_url, user_adcode, user_adchannel, user_phone) VALUES ('$user_login', '$user_login_register', '$user_level', $user_karma, FROM_UNIXTIME($user_date), '$user_ip', '$user_pass', $user_lang, '$user_email', '$user_email_register', '$user_names', '$user_public_info', '$user_url', '$user_adcode', '$user_adchannel', '$user_phone')");

            $this->id = $db->insert_id;
        } else {
            if ($full_save) {
                $modification = ', user_modification = now() ' ;
            }

            $db->query("UPDATE users set user_login='$user_login', user_level='$user_level', user_karma=$user_karma, user_avatar=$user_avatar, user_date=FROM_UNIXTIME($user_date), user_ip='$user_ip', user_pass='$user_pass', user_lang=$user_lang, user_comment_pref=$user_comment_pref, user_email='$user_email', user_email_register='$user_email_register', user_names='$user_names', user_public_info='$user_public_info', user_url='$user_url', user_adcode='$user_adcode', user_adchannel='$user_adchannel', user_phone='$user_phone' $modification  WHERE user_id=$this->id");
        }

        if ($this->meta_modified) {
            $this->meta_store();
        }
    }

    public function read()
    {
        global $db, $current_user;

        $id = $this->id;

        if ($this->id > 0) {
            $where = "user_id = $id";
        } elseif (!empty($this->username)) {
            $where = "user_login='".$db->escape(mb_substr($this->username, 0, 64))."'";
        } elseif (!empty($this->email)) {
            $where = "user_email='".$db->escape(mb_substr($this->email, 0, 64))."' and user_level != 'disabled' and user_level != 'autodisabled'";
        }

        $this->stats = false;

        if (empty($where) || !($result = $db->get_row("SELECT ".User::SQL." FROM users WHERE $where limit 1"))) {
            return $this->read = false;
        }

        foreach (get_object_vars($result) as $var => $value) {
            $this->$var = $value;
        }

        $this->admin = (($this->level === 'admin') || ($this->level === 'god'));

        return $this->read = true;
    }

    public function all_stats()
    {
        global $db, $globals, $current_user;

        if ($this->stats) {
            return;
        }

        if (!$this->read) {
            $this->read();
        }

        $do_cache = ($this->date < $globals['now'] - 86400); // Don't cache for new users
        $cache_time = 7200;
        $stats = new Annotation("user_stats-$this->id");

        if (
            $do_cache && $stats->read()
            && (
                $stats->time > $globals['now'] - $cache_time
                || $globals['bot'] || $current_user->user_id == 0
                || $stats->time > intval($db->get_var("select unix_timestamp(max(vote_date)) from votes where vote_user_id = $this->id and vote_type in ('links', 'posts', 'comments')"))
            )
        ) {
            $obj = unserialize($stats->text);
        } elseif ($globals['bot'] || $current_user->user_id == 0) {
            return; // Don't calculate stats por bots
        }

        $obj = new stdClass;
        $obj->total_votes = (int) $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_user_id = $this->id");
        $obj->total_links = (int) $db->get_var("SELECT count(*) FROM links WHERE link_author = $this->id and link_votes > 0");
        $obj->published_links = (int) $db->get_var("SELECT count(*) FROM links WHERE link_author = $this->id AND link_status = 'published'");
        $obj->total_comments = (int) $db->get_var("SELECT count(*) FROM comments WHERE comment_user_id = $this->id");
        $obj->total_posts = (int) $db->get_var("SELECT count(*) FROM posts WHERE post_user_id = $this->id");
        $obj->total_friends = (int) $db->get_var("select count(*) from friends where friend_to = $this->id");
        $obj->total_images = Upload::user_uploads($this->id) - Upload::user_uploads($this->id, false, 'private');

        if ($do_cache) {
            $stats->text = serialize($obj);
            $stats->store($globals['now'] + 86400 * 90); // Expires in 90 days
        }

        foreach (get_object_vars($obj) as $var => $value) {
            $this->$var = $value;
        }

        $this->stats = true;
    }

    public function getMedals()
    {
        global $globals, $db;

        if (($this->level === 'disabled') || ($this->level === 'autodisabled')) {
            return array();
        }

        $this->all_stats();

        $medals = array();

        if ($medal = $this->getMedalAntiquity()) {
            $medals[] = $medal;
        }

        if ($medal = $this->getMedalRatio()) {
            $medals[] = $medal;
        }

        if ($medal = $this->getMedalPublished()) {
            $medals[] = $medal;
        }

        if ($medal = $this->getMedalFriends()) {
            $medals[] = $medal;
        }

        return $medals;
    }

    private function getMedalAntiquity()
    {
        global $globals;

        if (($this->total_votes <= 20) && ($this->total_links <= 20)) {
            return;
        }

        $years = intval(($globals['now'] - $this->date) / (86400 * 365));

        if ($years > 2) {
            $type = 'gold';
        } elseif ($years > 1) {
            $type = 'silver';
        } elseif ($years > 0) {
            $type = 'bronze';
        } else {
            return;
        }

        return array(
            'type' => $type,
            'title' => __('antigüedad > %s años', $years)
        );
    }

    private function getMedalRatio()
    {
        if (($this->total_links <= 20) || ($this->published_links <= 2)) {
            return;
        }

        $ratio = round($this->published_links / $this->total_links, 2);

        if ($ratio > 0.15) {
            $type = 'gold';
        } elseif ($ratio > 0.10) {
            $type = 'silver';
        } elseif ($ratio > 0.08) {
            $type = 'bronze';
        } else {
            return;
        }

        return array(
            'type' => $type,
            'title' => __('porcentaje publicadas (%s)', $ratio)
        );
    }

    private function getMedalPublished()
    {
        if (empty($this->published_links)) {
            return;
        }

        $ratio = round($this->published_links / $this->total_links, 2);

        if ($this->published_links > 50) {
            $type = 'gold';
        } elseif ($this->published_links > 20) {
            $type = 'silver';
        } elseif (($this->published_links > 2) || (($this->published_links > 10) && ($ratio > 0.05))) {
            $type = 'bronze';
        } else {
            return;
        }

        return array(
            'type' => $type,
            'title' => __('publicadas (%s)', $this->published_links)
        );
    }

    private function getMedalFriends()
    {
        if ($this->total_friends > 200) {
            $type = 'gold';
        } elseif ($this->total_friends > 100) {
            $type = 'silver';
        } elseif ($this->total_friends > 50) {
            $type = 'bronze';
        } else {
            return;
        }

        return array(
            'type' => $type,
            'title' => __('amigos (%s)', $this->total_friends)
        );
    }

    public function getMedalImage($type, $title = '')
    {
        global $globals;

        return '<img src="'.$globals['base_static'].'img/common/medal_'.$type.'_1.png" alt="" title="'.$title.'"/>';
    }

    public function print_medals()
    {
        global $globals, $db;

        if (($this->level === 'disabled') || ($this->level === 'autodisabled')) {
            return;
        }

        foreach ($this->getMedals() as $medal) {
            echo $this->getMedalImage($medal['type'], $medal['title']);
        }
    }

    public function ranking($format = true)
    {
        global $db;

        if (!$this->read) {
            $this->read();
        }

        $value = (int)$db->get_var('
            SELECT SQL_CACHE COUNT(*)
            FROM users
            WHERE user_karma > "'.(float)$this->karma.'";
        ') + 1;

        return $format ? get_human_number($value) : $value;
    }

    public function blogs()
    {
        global $db;

        return $db->get_var("select  count(distinct link_blog) from links where link_author=$this->id");
    }

    public function get_api_key()
    {
        global $site_key;

        return substr(md5($this->user.$this->date.$this->pass.$site_key), 0, 10);
    }

    public function get_api_token($version = 0)
    {
        global $site_key;

        return "$version:".md5($this->username_register.$this->email_register.$this->date.$this->id.$site_key);
    }

    public function get_latlng()
    {
        require_once(mnminclude.'geo.php');

        return geo_latlng('user', $this->id);
    }

    public function get_uri($view = '')
    {
        global $globals;

        $uri = $globals['base_url_general'].'user/'.htmlspecialchars($this->username);
        $uri .= $view ? ('/'.$view) : '';

        return $uri;
    }

    public function add_karma($inc, $log = false)
    {
        global $globals;

        $this->karma = min($globals['max_karma'], $this->karma + $inc);
        $this->karma = max($globals['min_karma'], $this->karma);
        $this->karma = round($this->karma, 2);

        if (!empty($log) && mb_strlen($log) > 5) {
            $this->karma_log .= "$log: $inc, " . _('nuevo karma') . ": $this->karma\n";
            $this->karma_calculated = time();
        }

        $this->store();
    }

    public function get_prefs($force = false)
    {
        if (empty($this->id)) {
            return array();
        }

        if ($this->prefs && ($force === false)) {
            return $this->prefs;
        }

        global $db;

        $results = $db->get_results('SELECT pref_key, pref_value FROM prefs where pref_user_id = "'.$this->id.'";');

        foreach ($results as $result) {
            $this->prefs[$result->pref_key] = $result->pref_value;
        }

        return $this->prefs;
    }

    public static function friend_exists($from, $to)
    {
        global $db;

        if ($from == $to) {
            return 0;
        }

        return (int)$db->get_var('
            SELECT SQL_NO_CACHE friend_value
            FROM friends
            WHERE (
                friend_type = "manual"
                AND friend_from = "'.(int)$from.'"
                AND friend_to = "'.(int)$to.'"
            )
            LIMIT 1;
        ');
    }

    public static function friend_insert($from, $to, $value = 1)
    {
        global $db;

        if ($from == $to) {
            return 0;
        }

        if ((int)$db->get_var("SELECT SQL_NO_CACHE count(*) FROM users WHERE user_id IN ($from, $to)") !== 2) {
            return false;
        }

        return $db->query("REPLACE INTO friends (friend_type, friend_from, friend_to, friend_value) VALUES ('manual', $from, $to, $value)");
    }

    public static function friend_delete($from, $to)
    {
        global $db;

        return $db->query("DELETE FROM friends WHERE friend_type='manual' and friend_from = $from and friend_to = $to");
    }

    public static function friend_add_delete($from, $to)
    {
        if ($from == $to) {
            return '';
        }

        switch (self::friend_exists($from, $to)) {
            case 0:
                self::friend_insert($from, $to);

                return (self::friend_exists($to, $from) > 0) ? FRIEND_BOTH : FRIEND_YES;

            case 1:
                self::friend_insert($from, $to, -1);
                return FRIEND_IGNORE;

            case -1:
                self::friend_delete($from, $to);

                return (self::friend_exists($to, $from) > 0) ? FRIEND_OTHER : FRIEND_NO;
        }
    }

    public static function friend_teaser($from, $to)
    {
        if ($from == $to) {
            return '';
        }

        switch (self::friend_exists($from, $to)) {
            case 0:
                return (self::friend_exists($to, $from) > 0) ? FRIEND_OTHER : FRIEND_NO;

            case 1:
                return (self::friend_exists($to, $from) > 0) ? FRIEND_BOTH : FRIEND_YES;

            case -1:
                return FRIEND_IGNORE;
        }
    }

    public static function get_pref($id, $key, $value = false)
    {
        global $db, $current_user;

        if (empty($id) && empty($current_user->user_id)) {
            return;
        }

        $id = intval($id ?: $current_user->user_id);

        $key = $db->escape($key);

        if ($value) {
            $value = intval($value);
            $extra = "and pref_value=$value";
        } else {
            $extra = '';
        }

        return intval($db->get_var("select pref_value from prefs where pref_user_id = $id and pref_key = '$key' $extra limit 1"));
    }

    public static function set_pref($id, $key, $value)
    {
        global $db, $current_user;

        if (empty($id) && empty($current_user->user_id)) {
            return;
        }

        $id = intval($id ?: $current_user->user_id);

        $value = intval($value);
        $key = $db->escape($key);

        if ($value == 0) {
            return $db->query("delete from prefs where pref_user_id = $id and pref_key = '$key'");
        }

        return $db->query("replace into prefs set pref_value = $value, pref_user_id = $id, pref_key = '$key'");
    }

    public static function delete_pref($id, $key, $value = false)
    {
        global $db;

        return $db->query('
            DELETE FROM `prefs`
            WHERE (
                `pref_user_id` = "'.(int)$id.'"
                AND `pref_key` = "'.$db->escape($key).'"
                '.($value ? ('AND `pref_value` = "'.(int)$value.'"') : '').'
            );
        ');
    }
}
