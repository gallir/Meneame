<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

global $globals;
// Following functions are related to users but not done as a class so can be easily used with User and UserAuth
define('FRIEND_YES', '<img src="'.$globals['base_static'].'img/common/icon_friend_00.png" alt="del" width="18" height="16" title="'._('amigo').'"/>');
define('FRIEND_BOTH', '<img src="'.$globals['base_static'].'img/common/icon_friend_bi_00.png" alt="del" width="18" height="16" title="'._('amigos').'"/>');
define('FRIEND_NO', '<img src="'.$globals['base_static'].'img/common/icon_friend_no_00.png" alt="add" width="18" height="16" title="'._('agregar lista amigos').'"/>');
define('FRIEND_OTHER', '<img src="'.$globals['base_static'].'img/common/icon_friend_other_00.png" alt="add" width="18" height="16" title="'._('elegido').'"/>');
define('FRIEND_IGNORE', '<img src="'.$globals['base_static'].'img/common/icon_friend_ignore_00.png" alt="add" width="18" height="16" title="'._('ignorar').'"/>');

class User {

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

	const SQL = "user_id as id, user_login as username, user_login_register as username_register, user_level as level, user_comment_pref as comment_pref, UNIX_TIMESTAMP(user_date) as date, user_ip as ip, UNIX_TIMESTAMP(user_modification) as modification, user_pass as pass, user_email as email, user_email_register as email_register, user_names as names, user_lang as lang, user_karma as karma, user_avatar as avatar, user_public_info as public_info, user_url as url, user_adcode as adcode, user_adchannel as adchannel, user_phone as phone";

	static function get_valid_username($name) {
		$name = strip_tags($name);
		$name = preg_replace('/&.+?;/', '', $name); // kill entities
		$name = preg_replace('/[\s\'\"]/', '_', $name);
		if (preg_match('/^\d/', $name)) $name = 'u_' . $name; // Don't let start with a number
		return substr($name, 0, 24);
	}

	static function get_username($id) {
		global $db;
		$id = intval($id);
		return $db->get_var("select user_login from users where user_id = $id");
	}

	static function get_user_id($name) {
		global $db;

		$name = $db->escape($name);
		return $db->get_var("select user_id from users where user_login = '$name'");
	}

	static function calculate_affinity($uid, $min_karma = 200) {
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
					if ($vote->id > 0 && $vote->id != $uid && abs($vote->count) > max(1, $nlinks/10) ) {
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
					if ($vote->id > 0 && $vote->id != $uid && abs($vote->count) > max(1, $ncomments/10) ) {
						$w = min(1, $ncomments/15);
						$w = max(0.5, $w);
						$c = $vote->count/$ncomments * $w;
						if ($vote->count > 0) {
							$a = round((1 - $c)*100);
							if (!isset($affinity[$vote->id]) || $a < $affinity[$vote->id]) {
								$affinity[$vote->id] = $a;
							}
						} else {
							$a = round((-1 - $c)*100);
							if (!isset($affinity[$vote->id]) || ($affinity[$vote->id] < 0 && $a > $affinity[$vote->id]) ) {
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

	static function get_new_friends($user = 0) {
		global $db, $globals, $current_user;
		$key = 'last_friend';

		if (!$user && $current_user->user_id > 0) $user = $current_user->user_id;
		$last_read = intval($db->get_var("select pref_value from prefs where pref_user_id = $user and pref_key = '$key'"));
		return $db->get_col("select friend_from from friends where friend_type = 'manual' and friend_to = $user and friend_value > 0 and friend_date > FROM_UNIXTIME($last_read)");
	}

	static function update_new_friends_date($time = false) {
		global $db, $globals, $current_user;
		$key = 'last_friend';

		if (! $current_user->user_id ) return false;
		if (! $time) $time = $globals['now'];
		$previous = (int) $db->get_var("select pref_value from prefs where pref_user_id = $current_user->user_id and pref_key = '$key'");
		if ($time > $previous) {
			$db->transaction();
			$db->query("delete from prefs where pref_user_id = $current_user->user_id and pref_key = '$key'");
			$db->query("insert into prefs set pref_user_id = $current_user->user_id, pref_key = '$key', pref_value = $time");
			$db->commit();
		}
		return true;

	}


	// $user_id is the key in annotations
	static function get_affinity($id, $from = false) {
		global $current_user, $globals;

		if (!$globals['karma_user_affinity']) {
			return false;
		}

		$log = new Annotation("affinity-$id");
		if (!$log->read()) return false;
		$dict = unserialize($log->text);
		if (!$dict || ! is_array($dict)) return false; // Failed to unserialize
		if (!$from) return $dict; // Asked for the whole dict
		if (abs($dict[$from]) <= 100) return intval($dict[$from]); // Asked just a value;
		return false; // Nothing found
	}


	// Functions to manage "meta variables" that willl be stored as annotations and read automatically

	// Variables that are accepted as "meta" (to avoid storing all
	static function meta_valid($property) {
		switch ($property) {
			case 'bio':
			case 'karma_log':
			case 'karma_calculated':
				return true;
			default:
				return false;
		}
	}

	// Return the items for the top menu
	// Used by /user.php and /profile.php
	static function get_menu_items($view, $user) {
		global $globals, $current_user;

		switch ($view) {
			case 'history':
			case 'shaken':
			case 'friends_shaken':
			case 'favorites':
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
				$id = _('personal');
				break;
			default:
				do_error(_('opción inexistente'), 404);
				break;
		}


		$items = array();
		$items[] = new MenuOption(_('personal'), get_user_uri($user), $id, _('información de usuario'));
		$items[] = new MenuOption(_('relaciones'), get_user_uri($user, 'friends'), $id, _('amigos e ignorados'));
		$items[] = new MenuOption(_('historias'), get_user_uri($user, 'history'), $id, _('información de envíos'));
		$items[] = new MenuOption(_('comentarios'), get_user_uri($user, 'commented'), $id, _('información de comentarios'));
		$items[] = new MenuOption(_('notas'), post_get_base_url($user), $id, _('página de notas'));

		return $items;
	}

	function __get($property) {
		if (! $this->id > 0 || ! User::meta_valid($property) ) return false;

		if ($this->meta === false && ! $this->meta_read() ) {
			return false;
		}
		if (isset($this->meta[$property])) {
			return $this->meta[$property];
		}
		return false;
	}


	function __set($property, $value) {
		if (! $this->id > 0 ) return;
		if (! User::meta_valid($property) ) {
			$this->$property = $value;
			return;
		}
		if ($this->meta === false) {
			$this->meta_read();
		}
		$this->meta[$property] = $value;
		$this->meta_modified = true;
	}

	function meta_read() {
		$m = new Annotation("user_meta-$this->id");
		if (! $m->read() || ! ($this->meta = json_decode($m->text, true)) ) {
			$this->meta = array();
			return false;
		}
		return true;
	}

	function meta_store() {
		if (! is_array($this->meta)) return;
		$m = new Annotation("user_meta-$this->id");
		$m->text = json_encode($this->meta);
		$m->store();
		$this->meta_modified = false;
	}

// END meta

	function __construct($id = 0) {
		/*
		// For stats
		$this->total_votes;
		$this->total_links;
		$this->published_links;
		$this->total_comments;
		$this->total_posts;
		*/
		if ($id>0) {
			$this->id = $id;
			$this->read();
		}
	}

	function disabled() {
		return $this->level == 'disabled' || $this->level == 'autodisabled';
	}

	function disable($auto = false) {
		global $db;

		require_once(mnminclude.'avatars.php');
		require_once(mnminclude.'geo.php');
		avatars_remove($this->id);
		geo_delete('user', $this->id);

		// Delete relationships
		$db->query("DELETE FROM friends WHERE friend_type='manual' and (friend_from = $this->id or friend_to = $this->id)");
		// Delete preferences
		$db->query("DELETE FROM prefs WHERE pref_user_id = $this->id");
		// Delete posts' conversations
		$db->query("delete from conversations where conversation_type = 'post' and conversation_user_to = $this->id");
		$db->query("delete from conversations where conversation_type = 'post' and conversation_from in (select post_id from posts where post_user_id = $this->id)");
		// Delete posts
		$db->query("delete from posts where post_user_id = $this->id");
		// Delete user's meta
		$db->query("delete from annotations where annotation_key = 'user_meta-$this->id'");


		$this->username = '--'.$this->id.'--';
		$this->email = "$this->id@disabled";
		$this->url = '';
		if ($auto) $this->level = 'autodisabled';
		else $this->level = 'disabled';
		$this->names = 'disabled';
		$this->public_info = '';
		$this->adcode = '';
		$this->adchannel = '';
		$this->phone = '';
		$this->avatar = 0;
		$this->karma = 6;
		return $this->store();
	}

	function store($full_save = true) {
		global $db, $current_user, $globals;

		if(!$this->date) $this->date=$globals['now'];
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
		$user_public_info = $db->escape(htmlentities($this->public_info));
		$user_url = $db->escape(preg_replace('/\/+$/', '', htmlspecialchars($this->url))); // remove trailing "/s"
		$user_adcode = $db->escape($this->adcode);
		$user_adchannel = $db->escape($this->adchannel);
		$user_phone = $db->escape($this->phone);
		if($this->id===0) {
			$db->query("INSERT INTO users (user_login, user_login_register, user_level, user_karma, user_date, user_ip, user_pass, user_lang, user_email, user_email_register, user_names, user_public_info, user_url, user_adcode, user_adchannel, user_phone) VALUES ('$user_login', '$user_login_register', '$user_level', $user_karma, FROM_UNIXTIME($user_date), '$user_ip', '$user_pass', $user_lang, '$user_email', '$user_email_register', '$user_names', '$user_public_info', '$user_url', '$user_adcode', '$user_adchannel', '$user_phone')");
			$this->id = $db->insert_id;
		} else {
			if ($full_save) $modification = ', user_modification = now() ' ;
			$db->query("UPDATE users set user_login='$user_login', user_level='$user_level', user_karma=$user_karma, user_avatar=$user_avatar, user_date=FROM_UNIXTIME($user_date), user_ip='$user_ip', user_pass='$user_pass', user_lang=$user_lang, user_comment_pref=$user_comment_pref, user_email='$user_email', user_email_register='$user_email_register', user_names='$user_names', user_public_info='$user_public_info', user_url='$user_url', user_adcode='$user_adcode', user_adchannel='$user_adchannel', user_phone='$user_phone' $modification  WHERE user_id=$this->id");
		}
		if ($this->meta_modified) $this->meta_store();
	}

	function read() {
		global $db, $current_user;
		$id = $this->id;
		if($this->id>0) $where = "user_id = $id";
		elseif(!empty($this->username)) $where = "user_login='".$db->escape(mb_substr($this->username,0,64))."'";
		elseif(!empty($this->email)) $where = "user_email='".$db->escape(mb_substr($this->email,0,64))."' and user_level != 'disabled' and user_level != 'autodisabled'";

		$this->stats = false;
		if(!empty($where) && ($result = $db->get_row("SELECT ".User::SQL." FROM users WHERE $where limit 1"))) {
			foreach(get_object_vars($result) as $var => $value) $this->$var = $value;
			if ($this->level == 'admin' || $this->level == 'god') $this->admin = true;
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}

	function all_stats() {
		global $db, $globals, $current_user;

		if ($this->stats) return;
		if(!$this->read) $this->read();

		$do_cache = ($this->date < $globals['now'] - 86400); // Don't cache for new users
		$stats = new Annotation("user_stats-$this->id");

		if ($do_cache && $stats->read()
			&& ($stats->time > $globals['now'] - 7200
				|| $globals['bot'] || $current_user->user_id == 0
				|| $stats->time > intval($db->get_var("select unix_timestamp(max(vote_date)) from votes where vote_user_id = $this->id and vote_type in ('links', 'posts', 'comments')")))
			) {
				$obj = unserialize($stats->text);
		} else {

			if ($globals['bot'] && $current_user->user_id == 0) return; // Don't calculate stats por bots

			$obj = new stdClass;
			$obj->total_votes = (int) $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_user_id = $this->id");
			$obj->total_links = (int) $db->get_var("SELECT count(*) FROM links WHERE link_author = $this->id and link_votes > 0");
			$obj->published_links = (int) $db->get_var("SELECT count(*) FROM links WHERE link_author = $this->id AND link_status = 'published'");
			$obj->total_comments = (int) $db->get_var("SELECT count(*) FROM comments WHERE comment_user_id = $this->id");
			$obj->total_posts = (int) $db->get_var("SELECT count(*) FROM posts WHERE post_user_id = $this->id");
			$obj->total_friends = (int) $db->get_var("select count(*) from friends where friend_to = $this->id");
			$obj->total_images = Upload::user_uploads($this->id);
			if ($do_cache) {
				$stats->text = serialize($obj);
				$stats->store($globals['now']+86400*90); // Expires in 90 days
			}
		}
		foreach(get_object_vars($obj) as $var => $value) $this->$var = $value;

		$this->stats = true;
	}

	function print_medals() {
		global $globals, $db;

		if ($this->level == 'disabled' || $this->level == 'autodisabled') return;

		// Credits: using some famfamfam silk free icons
		$medals = array('gold' => 'medal_gold_1.png', 'silver' => 'medal_silver_1.png', 'bronze' => 'medal_bronze_1.png');

		$this->all_stats();
		// Users "seniority"
		if ($this->total_votes > 20 || $this->total_links > 20) {
			$medal = '';
			$years = intval(($globals['now'] - $this->date) / (86400*365));
			if ($years > 2) $medal = $medals['gold'];
			elseif ($years > 1) $medal = $medals['silver'];
			elseif ($years > 0) $medal = $medals['bronze'];
			if ($medal) echo '<img src="'.$globals['base_static'].'img/common/'.$medal.'" alt="" title="'._('antigüedad')." > $years "._('años').'"/>';
		}

		// Published ratio links
		if ($this->total_links > 20 && $this->published_links > 2) {
			$medal = '';
			$ratio = round($this->published_links / $this->total_links, 2);
			if ($ratio > 0.15) $medal = $medals['gold'];
			elseif ($ratio > 0.10) $medal = $medals['silver'];
			elseif ($ratio > 0.08) $medal = $medals['bronze'];
			if ($medal) echo '<img src="'.$globals['base_static'].'img/common/'.$medal.'" alt="" title="'._('porcentaje publicadas')." ($ratio)".'"/>';
		}

		// Published links
		$medal = '';
		if ($this->published_links > 50) $medal = $medals['gold'];
		elseif ($this->published_links > 20) $medal = $medals['silver'];
		elseif ($this->published_links > 2 || ($this->published_links > 10 && $ratio > 0.05)) $medal = $medals['bronze'];
		if ($medal) echo '<img src="'.$globals['base_static'].'img/common/'.$medal.'" alt="" title="'._('publicadas')." ($this->published_links)".'"/>';

		// Number of friends
		$medal = '';
		if ($this->total_friends > 200) $medal = $medals['gold'];
		elseif ($this->total_friends > 100) $medal = $medals['silver'];
		elseif ($this->total_friends > 50) $medal = $medals['bronze'];
		if ($medal) echo '<img src="'.$globals['base_static'].'img/common/'.$medal.'" alt="" title="'._('amigos')." ($this->total_friends)".'"/>';
	}

	function ranking() {
		global $db;

		if(!$this->read) $this->read();
		return (int) $db->get_var("SELECT SQL_CACHE count(*) FROM users WHERE user_karma > $this->karma") + 1;
	}

	function blogs() {
		global $db;
		return $db->get_var("select  count(distinct link_blog) from links where link_author=$this->id");
	}

	function get_api_key() {
		global $site_key;

		return substr(md5($this->user.$this->date.$this->pass.$site_key), 0, 10);
	}

	function get_latlng() {
		require_once(mnminclude.'geo.php');
		return geo_latlng('user', $this->id);
	}

	function add_karma($inc, $log = false) {
		global $globals;

		$this->karma = min($globals['max_karma'], $this->karma + $inc);
		$this->karma = max($globals['min_karma'], $this->karma);
		if (! empty($log) && mb_strlen($log) > 5) {
			$this->karma_log .= "$log: $inc, " . _('nuevo karma') . ": $this->karma\n";
			$this->karma_calculated = time();
		}
		$this->store();

	}

	static function friend_exists($from, $to) {
		global $db;
		if ($from == $to) return 0;
		return round($db->get_var("SELECT SQL_NO_CACHE friend_value FROM friends WHERE friend_type='manual' and friend_from = $from and friend_to = $to"));
	}

	static function friend_insert($from, $to, $value = 1) {
		global $db;
		if ($from == $to) return 0;
		if (intval($db->get_var("SELECT SQL_NO_CACHE count(*) from users where user_id in ($from, $to)")) != 2) return false;
		return $db->query("REPLACE INTO friends (friend_type, friend_from, friend_to, friend_value) VALUES ('manual', $from, $to, $value)");
	}

	static function friend_delete($from, $to) {
		global $db;
		return $db->query("DELETE FROM friends WHERE friend_type='manual' and friend_from = $from and friend_to = $to");
	}

	static function friend_add_delete($from, $to) {
		if ($from == $to) return '';
		switch (self::friend_exists($from, $to)) {
			case 0:
				self::friend_insert($from, $to);
				if (self::friend_exists($to, $from) > 0) return FRIEND_BOTH;
				else return FRIEND_YES;
			case 1:
				self::friend_insert($from, $to, -1);
				return FRIEND_IGNORE;
			case -1:
				self::friend_delete($from, $to);
				if (self::friend_exists($to, $from) > 0) return FRIEND_OTHER;
				else return FRIEND_NO;
		}
	}


	static function friend_teaser($from, $to) {
		if ($from == $to) return '';
		switch (self::friend_exists($from, $to)) {
			case 0:
				if (self::friend_exists($to, $from) > 0) return FRIEND_OTHER;
				else return FRIEND_NO;
			case 1:
				if (self::friend_exists($to, $from) > 0) return FRIEND_BOTH;
				else return FRIEND_YES;
			case -1:
				return FRIEND_IGNORE;
		}
	}

	static function get_pref($user, $key) {
		global $db, $current_user;

		if (!$user && $current_user->user_id > 0) $user = $current_user->user_id;

		return intval($db->get_var("select pref_value from prefs where pref_user_id = $user and pref_key = '$key' limit 1"));
	}

	static function set_pref($user, $key, $value) {
		global $db, $current_user;

		if (!$user && $current_user->user_id > 0) $user = $current_user->user_id;
		$value = intval($value);
		$key = $db->escape($key);

		if ($value == 0) {
			return $db->query("delete from prefs where pref_user_id = $user and pref_key = '$key'");
		} else {
			return $db->query("replace into prefs set pref_value = $value, pref_user_id = $user, pref_key = '$key'");
		}
	}
}

?>
