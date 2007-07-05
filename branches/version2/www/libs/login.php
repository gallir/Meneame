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
	var $user_karma=0;
	var $user_avatar=0;
	var $user_comment_pref=0;


	function UserAuth() {
		global $db, $site_key, $globals;

		$this->now = $globals['now'];
		if(!empty($_COOKIE['mnm_user']) && !empty($_COOKIE['mnm_key'])) {
			$userInfo=explode(":", base64_decode($_REQUEST['mnm_key']));
			if($_COOKIE['mnm_user'] === $userInfo[0]) {
				$cookietime = (int) $userInfo[3];
				$dbusername = $db->escape($_COOKIE['mnm_user']);
				$dbuser=$db->get_row("SELECT user_id, user_pass, user_level, user_validated_date, user_karma, user_email, user_avatar, user_comment_pref FROM users WHERE user_login = '$dbusername'");

				// We have two versions from now
				// The second is more strong agains brute force md5 attacks
				switch ($userInfo[2]) {
					case '3':
						if (($this->now - $cookietime) > 864000) $cookietime = 'expired'; // after 10 days expiration is forced
						$key = md5($dbuser->user_email.$site_key.$dbusername.$dbuser->user_id.$cookietime);
						break;
					case '2':
						$key = md5($dbuser->user_email.$site_key.$dbusername.$dbuser->user_id);
						$cookietime = 0;
						break;
					default:
						md5($site_key.$dbusername.$dbuser->user_id);
						$cookietime = 0;
				}

				if ( !$dbuser || !$dbuser->user_id > 0 || $key !== $userInfo[1] || 
					$dbuser->user_level == 'disabled' || 
					empty($dbuser->user_validated_date)) {
						$this->Logout();
						return;
				}

				$this->user_id = $dbuser->user_id;
				$this->user_login  = $userInfo[0];
				$this->md5_pass = $dbuser->user_pass;
				$this->user_level = $dbuser->user_level;
				$this->user_karma = $dbuser->user_karma;
				$this->user_email = $dbuser->user_email;
				$this->user_avatar = $dbuser->user_avatar;
				$this->user_comment_pref = $dbuser->user_comment_pref;
				$this->authenticated = TRUE;

				if ($userInfo[2] != '3') { // Update the cookie to version 3
					$this->SetIDCookie(2, true);
				} elseif ($this->now - $cookietime > 3600) { // Update the time each hour
					$this->SetIDCookie(2, $userInfo[4] > 0 ? true : false);
				}
			}
		}
	}


	function SetIDCookie($what, $remember) {
		global $site_key, $globals;
		switch ($what) {
			case 0:	// Borra cookie, logout
				setcookie ("mnm_user", "", $this->now - 3600, $globals['base_url']); // Expiramos el cookie
				setcookie ("mnm_key", "", $this->now - 3600, $globals['base_url']); // Expiramos el cookie
				break;
			case 1: // Usuario logeado, actualiza el cookie
			case 2: // Only update the key
				// Atencion, cambiar aquí cuando se cambie el password de base de datos a MD5
				if($remember) $time = $this->now + 3600000; // Valid for 1000 hours
				else $time = 0;
				$strCookie=base64_encode(
						$this->user_login.':'
						.md5($this->user_email.$site_key.$this->user_login.$this->user_id.$this->now).':'
						.'3'.':' // Version number
						.$this->now.':'
						.$time);
				if ($what == 1) setcookie("mnm_user", $this->user_login, $time, $globals['base_url']);
				setcookie("mnm_key", $strCookie, $time, $globals['base_url'].'; HttpOnly');
				break;
		}
	}

	function Authenticate($username, $password, $remember=false) {
		global $db;
		$dbusername=$db->escape($username);
		$user=$db->get_row("SELECT user_id, user_pass, user_level, user_validated_date, user_karma, user_email FROM users WHERE user_login = '$dbusername'");
		if ($user->user_level == 'disabled' || empty($user->user_validated_date)) return false;
		if (strlen($password) != 32) { //migrate to md5
			$password = md5($password);
		}
		$pass = $user->user_pass;
		if ($user->user_id > 0 && $pass == $password) {
			$this->user_login = $username;
			$this->user_id = $user->user_id;
			$this->authenticated = TRUE;
			$this->md5_pass = $pass;
			$this->user_level = $user->user_level;
			$this->user_email = $user->user_email;
			$this->user_karma = $user->user_karma;
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

}

$current_user = new UserAuth();
?>
