<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class User {
	var $read = false;
	var $id = 0;
	var $username = '';
	var $level = 'normal';
	var $modification = false;
	var $date = false;
	var $ip = '';
	var $pass = '';
	var $email = '';
	var $avatar = 0;
	var $names = '';
	var $lang = 1;
	var $comment_pref = 0;
	var $karma = 10;
	var $url = '';
	// For stats
	var $total_votes = 0;
	var $total_links = 0;
	var $published_links = 0;
	var $positive_votes_received = 0;
	var $negative_votes_received = 0;
	

	function User($id=0) {
		if ($id>0) {
			$this->id = $id;
			$this->read();
		}
	}

	function disable() {
		require_once(mnminclude.'avatars.php');
		require_once(mnminclude.'geo.php');
		avatars_db_remove($this->id);
		avatars_remove_user_files($this->id);
		geo_delete('user', $this->id);

		// Delete relationships
		$db->query("DELETE FROM friends WHERE friend_type='manual' and (friend_from = $this->id or friend_to = $this->id)");

		$this->username = '__'.$this->id.'__';
		$this->email = "$this->id@disabled";
		$this->url = '';
		$this->level = 'disabled';
		$this->names = 'disabled';
		$this->public_info = '';
		$this->adcode = '';
		$this->adchannel = '';
		$this->phone = '';
		$this->avatar = 0;
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
		$user_level = $this->level;
		$user_comment_pref = $this->comment_pref;
		$user_karma = $this->karma;
		$user_avatar = $this->avatar;
		$user_date = $this->date;
		$user_ip = $this->ip;
		$user_pass = $db->escape($this->pass);
		$user_lang = $this->lang;
		$user_email = $db->escape($this->email);
		$user_names = $db->escape($this->names);
		$user_public_info = $db->escape(htmlentities($this->public_info));
		$user_url = $db->escape(htmlentities($this->url));
		$user_adcode = $db->escape($this->adcode);
		$user_adchannel = $db->escape($this->adchannel);
		$user_phone = $db->escape($this->phone);
		if($this->id===0) {
			$db->query("INSERT INTO users (user_login, user_level, user_karma, user_date, user_ip, user_pass, user_lang, user_email, user_names, user_public_info, user_url, user_adcode, user_adchannel, user_phone) VALUES ('$user_login', '$user_level', $user_karma, FROM_UNIXTIME($user_date), '$user_ip', '$user_pass', $user_lang, '$user_email', '$usr_names',  '$user_url', '$user_adcode', '$user_phone'");
			$this->id = $db->insert_id;
		} else {
			if ($full_save) $modification = ', user_modification = now() ' ;
			$db->query("UPDATE users set user_login='$user_login', user_level='$user_level', user_karma=$user_karma, user_avatar=$user_avatar, user_date=FROM_UNIXTIME($user_date), user_ip='$user_ip', user_pass='$user_pass', user_lang=$user_lang, user_comment_pref=$user_comment_pref, user_email='$user_email', user_names='$user_names', user_public_info='$user_public_info', user_url='$user_url', user_adcode='$user_adcode', user_adchannel='$user_adchannel', user_phone='$user_phone' $modification  WHERE user_id=$this->id");
		}
	}
	
	function read() {
		global $db, $current_user;
		$id = $this->id;
		if($this->id>0) $where = "user_id = $id";
		else if(!empty($this->username)) $where = "user_login='".$db->escape(mb_substr($this->username,0,64))."'";

		if(!empty($where) && ($user = $db->get_row("SELECT * FROM users WHERE $where"))) {
			$this->id =$user->user_id;
			$this->username = $user->user_login;
			$this->username_register = $user->user_login_register;
			$this->level = $user->user_level;
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

		if(!$this->read) $this->read();

		$this->total_votes = $db->get_var("SELECT count(*) FROM votes WHERE vote_type='links' and vote_user_id = $this->id");
		$this->total_links = $db->get_var("SELECT count(*) FROM links WHERE link_author = $this->id and link_votes > 0");
		$this->published_links = $db->get_var("SELECT count(*) FROM links WHERE link_author = $this->id AND link_status = 'published'");
		$this->total_comments = $db->get_var("SELECT count(*) FROM comments WHERE comment_user_id = $this->id");
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


}

// Following functions are related to users but not done as a class so can be easily used with User and UserAuth
define('FRIEND_YES', '<img src="'.$globals['base_url'].'img/common/icon_heart.gif" alt="del" width="16" height="16" title="'._('amigo').'"/>');
define('FRIEND_NO', '<img src="'.$globals['base_url'].'img/common/icon_heart_no.gif" alt="add" width="16" height="16" title="'._('agregar lista amigos').'"/>');
define('FRIEND_IGNORE', '<img src="'.$globals['base_url'].'img/common/icon_heart_ignore.gif" alt="add" width="16" height="16" title="'._('ignorar').'"/>');


function friend_exists($from, $to) {
	global $db;
	if ($from == $to) return 0;
	return intval($db->get_var("SELECT SQL_NO_CACHE friend_value FROM friends WHERE friend_type='manual' and friend_from = $from and friend_to = $to"));
}

function friend_insert($from, $to, $value = 1) {
	global $db;
	if ($from == $to) return 0;
	if (intval($db->get_var("SELECT SQL_NO_CACHE count(*) from users where user_id in ($from, $to)")) != 2) return false;
	return $db->query("REPLACE INTO friends (friend_type, friend_from, friend_to, friend_value) VALUES ('manual', $from, $to, $value)");
}

function friend_delete($from, $to) {
	global $db;
	return $db->query("DELETE FROM friends WHERE friend_type='manual' and friend_from = $from and friend_to = $to");
}

function friend_add_delete($from, $to) {
	if ($from == $to) return '';
	switch (friend_exists($from, $to)) {
		case 0:
			friend_insert($from, $to);
			return FRIEND_YES;
		case 1:
			friend_insert($from, $to, -1);
			return FRIEND_IGNORE;
		case -1:
			friend_delete($from, $to);
			return FRIEND_NO;
	}
}


function friend_teaser($from, $to) {
	if ($from == $to) return '';
	switch (friend_exists($from, $to)) {
		case 0:
			return FRIEND_NO;
		case 1:
			return FRIEND_YES;
		case -1:
			return FRIEND_IGNORE;
	}
}
?>
