<?PHP
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".



class UserAuth {
	var $user_id  = 0;
	var $user_login = '';
	var $user_email = '';
	var $md5_pass = '';
	var $authenticated = FALSE;
	var $user_level='';
	var $admin = false;
	var $user_karma=0;
	var $user_avatar=0;
	var $user_comment_pref=0;
	var $mnm_user = False;


	function UserAuth() {
		global $db, $site_key, $globals;

		$this->now = $globals['now'];
		if(!empty($_COOKIE['mnm_user'])) {
			$this->mnm_user=explode(":", $_COOKIE['mnm_user']);
		}

		if($this->mnm_user[0] && !empty($_COOKIE['mnm_key'])) {
			$userInfo=explode(":", base64_decode($_COOKIE['mnm_key']));
			if($this->mnm_user[0] == $userInfo[0]) {
				$cookietime = intval($userInfo[3]);
				if (($this->now - $cookietime) > 864000) $cookietime = 'expired'; // after 10 days expiration is forced

				switch ($userInfo[2]) {
					case '4':
						$user_id = intval($this->mnm_user[0]);
						$user=$db->get_row("SELECT SQL_CACHE user_id, user_login, user_pass as md5_pass, user_level, UNIX_TIMESTAMP(user_validated_date) as user_date, user_karma, user_email, user_avatar, user_comment_pref FROM users WHERE user_id = $user_id");
						break;
					default:
						$dbusername = $db->escape($this->mnm_user[0]);
						$user=$db->get_row("SELECT SQL_CACHE user_id, user_login, user_pass as md5_pass, user_level, UNIX_TIMESTAMP(user_validated_date) as user_date, user_karma, user_email, user_avatar, user_comment_pref FROM users WHERE user_login = '$dbusername'");
				}
				$key = md5($user->user_email.$site_key.$user->user_login.$user->user_id.$cookietime);

				if ( !$user || !$user->user_id > 0 || $key !== $userInfo[1] || 
					$user->user_level == 'disabled' || $user->user_level == 'autodisabled' || 
					empty($user->user_date)) {
						$this->Logout();
						return;
				}

				foreach(get_object_vars($user) as $var => $value) $this->$var = $value;
				if ($this->user_level == 'admin' || $this->user_level == 'god') $this->admin = true;
				$this->authenticated = true;

				if ($userInfo[2] != '4') { // Update the key
					$this->SetIDCookie(2, true);
					$this->SetUserCookie(true);
				}

				if ($this->now - $cookietime > 3600) { // Update the time each hour
					$this->SetIDCookie(2, $userInfo[4] > 0 ? true : false);
				}
			}
		}
	}


	function SetIDCookie($what, $remember) {
		global $site_key, $globals;
		switch ($what) {
			case 0:	// Borra cookie, logout
				setcookie ("mnm_key", '', $this->now - 3600, $globals['base_url']); // Expiramos el cookie
				$this->SetUserCookie(false);
				break;
			case 1: // Usuario logeado, actualiza el cookie
				$this->AddClone();
				$this->SetUserCookie(true);
			case 2: // Only update the key
				if($remember) $time = $this->now + 3600000; // Valid for 1000 hours
				else $time = 0;
				$strCookie=base64_encode(
						$this->user_id.':'
						.md5($this->user_email.$site_key.$this->user_login.$this->user_id.$this->now).':'
						.'4'.':' // Version number
						.$this->now.':'
						.$time);
				setcookie("mnm_key", $strCookie, $time, $globals['base_url']);
				break;
		}
	}

	function Authenticate($username, $hash, $remember=false) {
		global $db;
		$dbusername=$db->escape($username);
		$user=$db->get_row("SELECT user_id, user_login, user_pass md5_pass, user_level, UNIX_TIMESTAMP(user_validated_date) as user_date, user_karma, user_email FROM users WHERE user_login = '$dbusername'");
		if ($user->user_level == 'disabled' || $user->user_level == 'autodisabled' || ! $user->user_date) return false;
		if ($user->user_id > 0 && $user->md5_pass == $hash) {
			foreach(get_object_vars($user) as $var => $value) $this->$var = $value;
			$this->authenticated = TRUE;
			$this->SetIDCookie(1, $remember);
			return true;
		}
		return false;
	}

	function Logout($url='./') {
		$this->user_id = 0;
		$this->user_login = "";
		$this->authenticated = FALSE;
		$this->SetIDCookie (0, false);

		//header("Pragma: no-cache");
		header("Cache-Control: no-cache, must-revalidate");
		header("Location: $url");
		header("Expires: " . gmdate("r", $this->now - 3600));
		header('ETag: "logingout' . $this->now . '"');
		die;
	}

	function Date() {
		global $db;
		return (int) $this->user_date;
	}

	function SetUserCookie($do_login) {
		global $globals;
		if ($do_login) {
			$expiration = $this->now + 86400 * 100;
		} else {
			$expiration = $this->now + 86400 * 7;
			$this->user_login = '_';
		}
		setcookie("mnm_user", 
					$this->user_id.
					':'.$this->mnm_user[1].
					':'.$this->signature($this->user_id.$this->mnm_user[1]), 
					$expiration, $globals['base_url']);
	}

	function AddClone() {
		if (!empty($this->mnm_user[1])) {
			$ids = explode("x", $this->mnm_user[1]);
			while(count($ids) > 4) {
				array_shift($ids);
			}
		} else {
			$ids = array();
		}
		array_push($ids, $this->user_id);
		$this->mnm_user[1] = implode('x', $ids);
	}

	function GetClones() {
		$clones = array();
		foreach (explode('x', $this->mnm_user[1]) as $id) {
			$id = intval($id);
			if ($id > 0) {
				array_push($clones, $id);
			}
		}
		return $clones;
	}

	static function signature($str) {
		global $site_key;
		return substr(md5($str.$site_key), 0, 8);
	}

	static function valid_user_cookie() {
		if(!empty($_COOKIE['mnm_user'])) {
			$user=explode(":", $_COOKIE['mnm_user']);
			return $user[2] == self::signature($user[0].$user[1]);
		}
		return false;
	}
}

$current_user = new UserAuth();
?>
