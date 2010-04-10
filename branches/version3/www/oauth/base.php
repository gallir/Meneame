<?
// The Meneame source code is Free Software, Copyright (C) 2005-2010 by
// Ricardo Galli <gallir at gmail dot com> and Menéame Comunicacions S.L.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.

// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".


// for the do_errors
require_once(mnminclude.$globals['html_main']);

class OAuthBase {

	function __construct() {
		if ($_COOKIE['return']) $this->return = $_COOKIE['return'];
		elseif ($_GET['return']) setcookie('return', $_GET['return'], time() + 3600);
	}

	function user_exists() {
		global $db, $current_user;

		if ($this->uid) $sql = "select user_id from auths where service='$this->service' and uid = $this->uid";
		else $sql = "select user_id from auths where service='$this->service' and name = '$this->username'";
		$this->id = $db->get_var($sql);
		if ($this->id) {
			$this->user = new User($this->id);

			if ($current_user->user_id && $current_user->user_id != $this->id) {
				if (! $this->user->disabled()) {
					do_error(_('cuenta asociada a otro usuario').': '.$this->user->username, false, false);
				}
				// We read again, the previous user is another one, already disabled
				$this->user = new User($current_user->user_id);
				$this->id = $this->id = $current_user->user_id;
			} elseif (! $this->user->id || $this->user->disabled()) {
				do_error(_('usuario deshabilitado'), false, false);	
			}
		} else {
			if ($current_user->user_id) {
				$this->user = new User($current_user->user_id);
				$this->id = $current_user->user_id;
			} else {
				$this->user = new User();
			}
		}
		return $this->id;
	}

	function store_user() {
		global $db, $globals;

		$user = $this->user;
		if (!$this->secret) $this->secret = $this->service . "-" . $globals['now'];
		if (user_exists($this->username)) {
			$i = 1;
			while(user_exists($this->username . "_$i")) $i++;
			$user->username = $this->username . "_$i";
		} else {
			$user->username = $this->username;
		}
		if (! $user->pass || preg_match('/$\$/', $user->pass) ) {
			$user->pass = "\$$this->service\$$this->secret";
		}
		if (! $user->names && $this->names) $user->names = $this->names;
		if (! $user->url && $this->url) $user->url = $this->url;
		if ( $user->id == 0) {
			$user->date = $globals['now'];
			$user->ip = $globals['user_ip'];
			$user->email = $this->username.'@'.$this->service;
			$user->email_register = $this->username.'@'.$this->service;
		}
		syslog(LOG_NOTICE, "Meneame new user from $this->service: $user->username, $user->names");
		$user->store();
		$db->query("update users set user_validated_date = now() where user_id = $user->id and user_validated_date is null");
		if ($this->avatar) {
			require_once(mnminclude.'avatars.php');
			avatars_get_from_url($user->id, $this->avatar);
		}
	}

	function store_auth() {
		global $db, $globals;
		$user = $this->user;
		$db->query("REPLACE INTO auths (user_id, service, uid, username, token, secret) VALUES ($user->id, '$this->service', $this->uid, '$this->username', '$this->token', '$this->secret')");
	}

	function user_login() {
		global $current_user, $globals;
		$user = $this->user;
		//print_r($this->user);
		$current_user->Authenticate($user->username, $user->pass, false);
		check_clon_from_cookies();
		setcookie('return', '', time() - 10000);
		if(!empty($this->return)) {
			header('Location: http://'.get_server_name().$this->return);
		} else {
			header('Location: http://'.get_server_name().$globals['base_url']);
		}

	}
}
?>
