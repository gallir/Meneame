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


$base = dirname(dirname($_SERVER["SCRIPT_FILENAME"])); // Get parent dir that works with symbolic links
include("$base/config.php");

include('base.php');
include_once(mnminclude.'facebook/facebook.php');


class FBConnect extends OAuthBase {

	function __construct() {
		global $globals;
		$this->service = 'facebook';

		if ($globals['mobile_version']) $server = 'm.facebook.com';
		else $server = 'www.facebook.com';

		$this->facebook = new Facebook(array(
					'appId' => $globals['facebook_key'],
					'secret' => $globals['facebook_secret'],
					));
		$this->user = $this->facebook->getUser();
		
		parent::__construct();
	}

	function authRequest() {
		global $globals;

		// Print html needed for FB Connect API
		$loginUrl = $this->facebook->getLoginUrl();

		echo "<html><head>\n";
		echo '<script type="text/javascript">'."\n";
		echo 'self.location = "'.$loginUrl.'";'."\n";
		echo '</script>'."\n";
		echo '</head><body></body></html>'."\n";
		exit;
	}

	function authorize() {
		global $globals, $db;

		try {
			$user_profile = $this->facebook->api('/me');
		} catch (FacebookApiException $e) {
			$this->user = null;
			$this->user_return();
			die;
		}


		$this->token = $user_profile['id'];
		$this->secret = $user_profile['id'];
		$this->uid = $user_profile['id'];
		$this->username = preg_replace('/.+?\/.*?([\w\.\-_]+)$/', '$1', $user_profile['username']);
		// Most Facebook users don't have a name, only profile number
		if (!$this->username || preg_match('/^\d+$/', $this->username)) {
			// Create a name like a uri used in stories
			if (strlen($user_profile['name']) > 2) {
				$this->username = User::get_valid_username($user_profile['name']);
			} else {
				$this->username = 'fb'.$this->username;
			}
		}
		$db->transaction();
		if (!$this->user_exists()) {
			$this->url = $user_profile['link'];
			$this->names = $user_profile['name'];
			if ($user_profile['username']) {
				$this->avatar = "http://graph.facebook.com/".$user_profile['username']."/picture";
			}
			$this->store_user();
		}
		$this->store_auth();
		$db->commit();
		$this->user_login();
	}
}


$auth = new FBConnect();

if ($auth->user) {
	$auth->authorize();
} else {
	$auth->authRequest();
}

?>
