<?PHP
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".



class UserAuth {
	const CURRENT_VERSION = '5';
	const KEY_MAX_TTL = 2592000; // Expire key in 30 days
	const KEY_TTL = 86400; // Renew every 24 hours

	function __construct() {
		global $db, $site_key, $globals;

		$this->user_id = 0;
		$this->user_login = '';
		$this->authenticated = false;
		$this->admin = false;

		if(!isset($globals['no_auth']) && isset($_COOKIE['mnm_key']) && isset($_COOKIE['mnm_user'])
					&& ($this->mnm_user = explode(":", $_COOKIE['mnm_user']))
					&& $this->mnm_user[0] > 0
					) {
			$userInfo=explode(":", base64_decode($_COOKIE['mnm_key']));
			if($this->mnm_user[0] == $userInfo[0]) {
				$this->version = $userInfo[2];
				$cookietime = intval($userInfo[3]);
				if (($globals['now'] - $cookietime) > UserAuth::KEY_MAX_TTL) $cookietime = 'expired'; // expiration is forced

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
				elseif ($this->user_level == 'special' || $this->user_level == 'blogger') $this->special = true;
				$this->authenticated = true;

				$remember = $userInfo[4] > 0;

				if ($this->version != self::CURRENT_VERSION) { // Update the key
					$this->SetIDCookie(2, $remember);
					$this->SetUserCookie();
				} elseif ($globals['now'] - $cookietime >  UserAuth::KEY_TTL) {
					$this->SetIDCookie(2, $remember);
				}
			}
		}
		// Mysql variables to use en join queries
		$db->query("set @user_id = $this->user_id, @ip_int = ".$globals['user_ip_int'].
			", @ip_int = ".$globals['user_ip_int'].
			", @enabled_votes = date_sub(now(), interval ". intval($globals['time_enabled_votes']/3600). " hour)");
	}


	function SetIDCookie($what, $remember = false) {
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
				if($remember) $time = $globals['now'] + UserAuth::KEY_MAX_TTL;
				else $time = 0;
				$strCookie=base64_encode(
						$this->user_id.':'
						.md5($this->user_email.$site_key.$this->user_login.$this->user_id.$globals['now']).':'
						.self::CURRENT_VERSION.':' // Version number
						.$globals['now'].':'
						.$time);
				setcookie("mnm_key", $strCookie, $time, $globals['base_url'], null, false, true);
				break;
		}
	}

	function Authenticate($username, $hash, $remember = false/* Just this session */) {
		global $db, $globals;

		$dbusername=$db->escape($username);
		if (preg_match('/.+@.+\..+/', $username)) {
			// It's an email address, get
			$user=$db->get_row("SELECT user_id, user_login, user_pass md5_pass, user_level, UNIX_TIMESTAMP(user_validated_date) as user_date, user_karma, user_email FROM users WHERE user_email = '$dbusername'");
		} else {
			$user=$db->get_row("SELECT user_id, user_login, user_pass md5_pass, user_level, UNIX_TIMESTAMP(user_validated_date) as user_date, user_karma, user_email FROM users WHERE user_login = '$dbusername'");
		}
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
					$expiration, $globals['base_url'], null, false, true);
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
		$ids[] = $this->user_id;
		$this->mnm_user[1] = implode('x', $ids);
	}

	function GetClones() {
		$clones = array();
		foreach (explode('x', $this->mnm_user[1]) as $id) {
			$id = intval($id);
			if ($id > 0) {
				$clones[] = $id;
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

	function get_clones($hours=24, $all = false) {
		// Return the count of cookies clones that voted before a given link, comment, note
		global $db;

		if (! $all) $extra = "and clon_ip like 'COOK:%'";
		else $extra = '';

		// This as from
		$a = $db->get_col("select clon_to as clon from clones where clon_from = $this->user_id and clon_date > date_sub(now(), interval $hours hour) $extra");
		// This as to
		$b = $db->get_col("select clon_from as clon from clones where clon_to = $this->user_id and clon_date > date_sub(now(), interval $hours hour) $extra");
		return array_unique(array_merge($a, $b));
	}

	static function check_clon_from_cookies() {
		global $current_user, $globals;
		// Check the cookies and store clones
		$clones = array_reverse($current_user->GetClones()); // First item is the current login, second is the previous
		if (count($clones) > 1 && $clones[0] != $clones[1]) { // Ignore if last two logins are the same user
			$visited = array();
			foreach ($clones as $id) {
				if ($current_user->user_id != $id && !in_array($id, $visited)) {
					$visited[] = $id;
					if ($globals['form_user_ip']) $ip = $globals['form_user_ip']; // Used in SSL forms
					else $ip = $globals['user_ip'];
					UserAuth::insert_clon($current_user->user_id, $id, 'COOK:'.$ip);
				}
			}
		}
	}

	static function insert_clon($last, $previous, $ip='') {
		global $globals, $db;
		if ($last == $previous) return false;
		$db->query("REPLACE INTO clones (clon_from, clon_to, clon_ip) VALUES ($last, $previous, '$ip')");
		$db->query("INSERT IGNORE INTO clones (clon_to, clon_from, clon_ip) VALUES ($last, $previous, '$ip')");
	}

	static function check_clon_votes($from, $id, $days=7, $type='links') {
		// Return the count of cookies clones that voted before a given link, comment, note
		global $db;

		$c = (int) $db->get_var("select count(*) from votes, clones where vote_type='$type' and vote_link_id = $id and clon_from = $from and clon_to = vote_user_id and clon_date > date_sub(now(), interval $days day) and clon_ip like 'COOK:%'");
		if ($c > 0) {
			syslog(LOG_INFO, "Meneame: clon vote $type, id: $id, user: $from ");
		}
		return $c;
	}



}

$current_user = new UserAuth();
?>
