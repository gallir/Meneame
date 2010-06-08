<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

global $globals;
// Following functions are related to users but not done as a class so can be easily used with User and UserAuth
define('FRIEND_YES', '<img src="'.$globals['base_static'].'img/common/icon_heart.gif" alt="del" width="16" height="16" title="'._('amigo').'"/>');
define('FRIEND_NO', '<img src="'.$globals['base_static'].'img/common/icon_heart_no.gif" alt="add" width="16" height="16" title="'._('agregar lista amigos').'"/>');
define('FRIEND_IGNORE', '<img src="'.$globals['base_static'].'img/common/icon_heart_ignore.gif" alt="add" width="16" height="16" title="'._('ignorar').'"/>');

class User {
	static function get_valid_username($name) {
		$name = strip_tags($name);
		$name = preg_replace('/&.+?;/', '', $name); // kill entities
		$name = preg_replace('/\s/', '_', $name); 
		return substr($name, 0, 24);
	}

	static function calculate_affinity($uid, $min_karma = 200) {
		global $globals, $db;

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

	// $user_id is the key in annotations
	static function get_affinity($id, $from = false) {
		global $current_user;

		$log = new Annotation("affinity-$id");
		if (!$log->read()) return false;
		$dict = unserialize($log->text);
		if (!$dict || ! is_array($dict)) return false; // Failed to unserialize
		if (!$from) return $dict; // Asked for the whole dict
		if (abs($dict[$from]) <= 100) return intval($dict[$from]); // Asked just a value;
		return false; // Nothing found
	}

	function __construct($id = 0) {
		$this->read = false;
		$this->id = 0;
		$this->username = '';
		$this->level = 'normal';
		$this->admin = false; 
		$this->modification = false;
		$this->date = false;
		$this->ip = '';
		$this->pass = '';
		$this->email = '';
		$this->avatar = 0;
		$this->names = '';
		$this->lang = 1;
		$this->comment_pref = 0;
		$this->karma = 6;
		$this->url = '';
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
		// Delete posts
		$db->query("delete from posts where post_user_id = $this->id");

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
		$user_url = $db->escape(htmlspecialchars($this->url));
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
	}
	
	function read() {
		global $db, $current_user;
		$id = $this->id;
		if($this->id>0) $where = "user_id = $id";
		elseif(!empty($this->username)) $where = "user_login='".$db->escape(mb_substr($this->username,0,64))."'";
		elseif(!empty($this->email)) $where = "user_email='".$db->escape(mb_substr($this->email,0,64))."' and user_level != 'disabled'";

		$this->stats = false;
		if(!empty($where) && ($user = $db->get_row("SELECT SQL_CACHE * FROM users WHERE $where limit 1"))) {
			$this->id =$user->user_id;
			$this->username = $user->user_login;
			$this->username_register = $user->user_login_register;
			$this->level = $user->user_level;
			if ($this->level == 'admin' || $this->level == 'god') $this->admin = true;
			$this->comment_pref = $user->user_comment_pref;
			$date=$user->user_date;
			$this->date=$db->get_var("SELECT UNIX_TIMESTAMP('$date')");
			$this->ip = $user->user_ip;
			$date=$user->user_modification;
			$this->modification=$db->get_var("SELECT UNIX_TIMESTAMP('$date')");
			$this->pass = $user->user_pass;
			$this->email = $user->user_email;
			$this->email_register = $user->user_email_register;
			$this->names = $user->user_names;
			$this->lang = $user->user_lang;
			$this->karma = $user->user_karma;
			$this->avatar = $user->user_avatar;
			$this->public_info = $user->user_public_info;
			$this->url = $user->user_url;
			$this->adcode = $user->user_adcode;
			$this->adchannel = $user->user_adchannel;
			$this->phone = $user->user_phone;
			$this->read = true;
			return true;
		}
		$this->read = false;
		return false;
	}

	function all_stats() {
		global $db;

		if ($this->stats) return;
		if(!$this->read) $this->read();

		$this->total_votes = (int) $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_user_id = $this->id");
		$this->total_links = (int) $db->get_var("SELECT count(*) FROM links WHERE link_author = $this->id and link_votes > 0");
		$this->published_links = (int) $db->get_var("SELECT count(*) FROM links WHERE link_author = $this->id AND link_status = 'published'");
		$this->total_comments = (int) $db->get_var("SELECT count(*) FROM comments WHERE comment_user_id = $this->id");
		$this->total_posts = (int) $db->get_var("SELECT count(*) FROM posts WHERE post_user_id = $this->id");
		$this->stats = true;
	}

	function print_medals() {
		global $globals, $db;

		if ($this->level == 'disabled' || $this->level == 'autodisabled') return;

		echo "\n<!-- Credits: using some famfamfam silk free icons -->\n";
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
		if ($this->published_links > 200) $medal = $medals['gold'];
		elseif ($this->published_links > 50) $medal = $medals['silver'];
		elseif ($this->published_links > 20 || ($this->published_links > 10 && $ratio > 0.05)) $medal = $medals['bronze'];
		if ($medal) echo '<img src="'.$globals['base_static'].'img/common/'.$medal.'" alt="" title="'._('publicadas')." ($this->published_links)".'"/>';

		// Number of friends
		$medal = '';
		$friends = $db->get_var("select count(*) from friends where friend_to = $this->id");
		if ($friends > 200) $medal = $medals['gold'];
		elseif ($friends > 100) $medal = $medals['silver'];
		elseif ($friends > 50) $medal = $medals['bronze'];
		if ($medal) echo '<img src="'.$globals['base_static'].'img/common/'.$medal.'" alt="" title="'._('amigos')." ($friends)".'"/>';
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
				return FRIEND_YES;
			case 1:
				self::friend_insert($from, $to, -1);
				return FRIEND_IGNORE;
			case -1:
				self::friend_delete($from, $to);
				return FRIEND_NO;
		}
	}


	static function friend_teaser($from, $to) {
		if ($from == $to) return '';
		switch (self::friend_exists($from, $to)) {
			case 0:
				return FRIEND_NO;
			case 1:
				return FRIEND_YES;
			case -1:
				return FRIEND_IGNORE;
		}
	}


}

?>
