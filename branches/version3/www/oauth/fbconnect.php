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

include('../config.php');
include('base.php');
include_once(mnminclude.'fbconnect/facebook.php');


class FBConnect extends OAuthBase {

	function __construct() {
		global $globals;
		$this->service = 'facebook';

		// Store de FB URL for login
		$location_ok = urlencode('http://'.  get_server_name() . $globals['base_url'] . 'oauth/fbconnect.php?op=ok'.'&t='.time());
		$location_cancel = urlencode('http://'.  get_server_name() . $globals['base_url'] . 'oauth/fbconnect.php?op=cancel'.'&t='.time());
		$this->authorize_url = 'http://www.facebook.com/login.php?api_key='.$globals['facebook_key'].'&extern=1&fbconnect=1&return_session=1&v=1.0&next='.$location_ok.'&cancel_url='.$location_ok;
		parent::__construct();
	}

	function authRequest() {
		global $globals;

		// Print html needed for FB Connect API
		echo "<html><head>\n";
		echo '<script src="http://static.new.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php" type="text/javascript"></script>'."\n";
		echo '<script type="text/javascript">'."\n";
		echo 'FB.init("'.$globals['facebook_key'].'", "'.$globals['base_url'].'libs/fbconnect/xd_receiver.php",{"reloadIfSessionStateChanged":true});'."\n";
		echo 'self.location = "'.$this->authorize_url.'";'."\n";
		echo '</script>'."\n";
		echo '</head><body></body></html>'."\n";
		exit;
	}

	function authorize() {
		global $globals, $db;

		$fb = new Facebook($globals['facebook_key'], $globals['facebook_secret']);
		$fb->require_login();
		$fb_user = $fb->get_loggedin_user();

		if ($_GET['op'] != 'ok' || ! $fb_user) {
			$this->user_return();
		}

		$user_details = $fb->api_client->users_getInfo($fb_user, array('uid', 'name', 'profile_url', 'pic_square')); 

		$this->token = $user_details[0]['uid'];
		$this->secret = $user_details[0]['uid'];
		$this->uid = $user_details[0]['uid'];
		$this->username = preg_replace('/.+?\/.*?([\w\.\-_]+)$/', '$1', $user_details[0]['profile_url']);
		// Most Facebook users don't have a name, only profile number
		if (!$this->username || preg_match('/^\d+$/', $this->username)) {
			// Create a name like a uri used in stories
			if (strlen($user_details[0]['name']) > 2) {
				$this->username = User::get_valid_username($user_details[0]['name']);
			} else {
				$this->username = 'fb'.$this->username;
			}
		}
		$db->transaction();
		if (!$this->user_exists()) {
			$this->url = $user_details[0]['profile_url'];
			$this->names = $user_details[0]['name'];
			$this->avatar = $user_details[0]['pic_square'];
			$this->store_user();
		}
		$this->store_auth();
		$db->commit();
		$this->user_login();
	}
}


$auth = new FBConnect();

switch ($_GET['op']) {
	case 'ok':
	case 'cancel':
		$auth->authorize();
		break;
	case '':
		$auth->authRequest();
		break;
	default:
		die;
}
		
?>
