<?PHP
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".



class UserAuth {
	const CURRENT_VERSION = '5';

	function __construct() {
		global $db, $site_key, $globals;

		if($_COOKIE['mnm_key'] && $_COOKIE['mnm_user']
					&& ($this->mnm_user = explode(":", $_COOKIE['mnm_user']))
					&& $this->mnm_user[0] > 0
					) {
			$userInfo=explode(":", base64_decode($_COOKIE['mnm_key']));
			if($this->mnm_user[0] == $userInfo[0]) {
				$this->version = $userInfo[2];
				$cookietime = intval($userInfo[3]);
				if (($globals['now'] - $cookietime) > 864000) $cookietime = 'expired'; // after 10 days expiration is forced

				$user_id = intval($this->mnm_user[0]);
				$user=$db->get_row("SELECT SQL_CACHE user_id, user_login, user_pass as md5_pass, user_level, UNIX_TIMESTAMP(user_validated_date) as user_date, user_karma, user_email, user_avatar, user_comment_pref FROM users WHERE user_id = $user_id");

				$key = md5($user->user_email.$site_key.$user->user_login.$user->user_id.$cookietime);

				if ( !$user || !$user->user_id > 0 || $key !== $userInfo[1] || 
					$user->user_level == 'disabled' || $user->user_level == 'autodisabled' || 
					empty($user->user_date)) {
						$this->Logout();
						// Make sure mysql @user_id is reset
						$db->query("set @user_id = 0");
						return;
				}

				foreach(get_object_vars($user) as $var => $value) $this->$var = $value;
				if ($this->user_level == 'admin' || $this->user_level == 'god') $this->admin = true;
				$this->authenticated = true;

				if ($userInfo[4] > 0) $expiration = min(864000, $userInfo[4] - $globals['now']);
				else $expiration = 0;

				if ($this->version != self::CURRENT_VERSION) { // Update the key
					$this->SetIDCookie(2, $expiration);
					$this->SetUserCookie();
				} elseif ($globals['now'] - $cookietime > 7200) { // Update the time each 2 hours
					$this->SetIDCookie(2, $expiration);
				}
			}
		} else {
			$this->user_id = 0;
			$this->user_login = '';
			$this->authenticated = false;
			$this->admin = false;
		}
		// Mysql variable to use en join queries
		$db->query("set @user_id = $this->user_id");
	}


	function SetIDCookie($what, $remember) {
		global $site_key, $globals;
		switch ($what) {
			case 0:	// Borra cookie, logout
				$this->user_id = 0;
				setcookie ("mnm_key", '', $globals['now'] - 3600, $globals['base_url']); // Expiramos el cookie
				$this->SetUserCookie();
				break;
			case 1: // Usuario logeado, actualiza el cookie
				$this->AddClone();
				$this->SetUserCookie();
			case 2: // Only update the key
				if($remember > 0) $time = $globals['now'] + $remember; // Valid for 1000 hours
				else $time = 0;
				$strCookie=base64_encode(
						$this->user_id.':'
						.md5($this->user_email.$site_key.$this->user_login.$this->user_id.$globals['now']).':'
						.self::CURRENT_VERSION.':' // Version number
						.$globals['now'].':'
						.$time);
				setcookie("mnm_key", $strCookie, $time, $globals['base_url']);
				break;
		}
	}

	function Authenticate($username, $hash, $remember=0/* Just this session */) {
		global $db, $globals;
		$dbusername=$db->escape($username);
		$user=$db->get_row("SELECT user_id, user_login, user_pass md5_pass, user_level, UNIX_TIMESTAMP(user_validated_date) as user_date, user_karma, user_email FROM users WHERE user_login = '$dbusername'");
		if ($user->user_level == 'disabled' || $user->user_level == 'autodisabled' || ! $user->user_date) return false;
		if ($user->user_id > 0 && $user->md5_pass == $hash) {
			foreach(get_object_vars($user) as $var => $value) $this->$var = $value;
			$this->authenticated = true;
			$this->SetIDCookie(1, $remember);
			return true;
		}
		return false;
	}

	function Logout($url='./') {
		$this->user_id = 0;
		$this->user_login = '';
		$this->admin = false;
		$this->authenticated = false;
		$this->SetIDCookie (0, false);

		//header("Pragma: no-cache");
		header("Cache-Control: no-cache, must-revalidate");
		header("Location: $url");
		header("Expires: " . gmdate("r", $globals['now'] - 3600));
		header('ETag: "logingout' . $globals['now'] . '"');
		die;
	}

	function Date() {
		global $db;
		return (int) $this->user_date;
	}

	function SetUserCookie() {
		global $globals;
		$expiration = $globals['now'] + 86400 * 1000;
		setcookie("mnm_user", 
					$this->user_id.
					':'.$this->mnm_user[1].
					':'.$globals['now'].
					':'.$this->signature($this->user_id.$this->mnm_user[1].$globals['now']), 
					$expiration, $globals['base_url']);
	}

	function AddClone() {
		global $globals;

		$this->mnm_user = self::user_cookie_data(); // Get the previous user cookie which shouldn't be modified at this time
		if ($this->mnm_user && $globals['now'] - $this->mnm_user[2] < 86400 * 5) { // Only if it was stored recently
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

	function GetOAuthIds($service = false) {
		global $db;
		if (! $this->user_id) return false;
		if (! $service) {
			$sql = "select service, uid from auths where user_id = $this->user_id";
			$res = $db->get_results($sql);
		} else {
			$sql = "select uid from auths where user_id = $this->user_id and service = '$service'";
			$res = $db->get_var($sql);
		}
		return $res;
	}

	static function signature($str) {
		global $site_key;
		return substr(md5($str.$site_key), 0, 8);
	}

	static function user_cookie_data() {
		// Return an array with mnm_user only if the signature is valid
		if ($_COOKIE['mnm_user'] && ($mnm_user = explode(":", $_COOKIE['mnm_user']))
			&&  $mnm_user[3] == self::signature($mnm_user[0].$mnm_user[1].$mnm_user[2]) ) {
			return $mnm_user;
		}
		return false;
	}
}

$current_user = new UserAuth();
?>
