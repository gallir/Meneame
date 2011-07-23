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

require_once('base.php');

class TwitterOAuth extends OAuthBase {

	function __construct() {
		global $globals;

		$server = 'twitter.com';
		$this->request_token_url = "http://$server/oauth/request_token";
		$this->access_token_url = "http://$server/oauth/access_token";
		$this->authorize_url =  "http://$server/oauth/authenticate";
		$this->credentials_url = "http://$server/account/verify_credentials.json";

		if (! $globals['oauth']['twitter']['consumer_key'] || ! $globals['oauth']['twitter']['consumer_secret']) {
			$oauth = null;
		}
		$this->service = 'twitter';
		$this->oauth = new OAuth($globals['oauth']['twitter']['consumer_key'], $globals['oauth']['twitter']['consumer_secret'], OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_URI);
		parent::__construct();
	}

	function authRequest() {
		global $globals;
		try {
			if (($request_token_info = 
					$this->oauth->getRequestToken($this->request_token_url,
						'http://'.get_server_name().$globals['base_url'].'oauth/signin.php?service=twitter'))) {
				// if [oauth_callback_confirmed] => true then is oauth 1.0a
				setcookie('oauth_token', $request_token_info['oauth_token'], 0);
				setcookie('oauth_token_secret', $request_token_info['oauth_token_secret'], 0);
				$this->token_secret = $request_token_info['oauth_token_secret'];
				$this->token = $request_token_info['oauth_token'];
				header("Location: ".$this->authorize_url."?oauth_token=$this->token");
				exit;
			} else {
				do_error(_('error obteniendo tokens'), false, false);	
			}
		} catch (Exception $e) {
				do_error(_('error de conexión a') . " $this->service (authRequest)", false, false);	
		}
	}

	function authorize() {
		global $globals, $db;

		$oauth_token = clean_input_string($_GET['oauth_token']);
		$request_token_secret = $_COOKIE['oauth_token_secret'];

		if(!empty($oauth_token) && !empty($request_token_secret) ){
			$this->oauth->setToken($oauth_token, $request_token_secret);
			try {
				$access_token_info = $this->oauth->getAccessToken($this->access_token_url);
			} catch (Exception $e) {
				do_error(_('error de conexión a') . " $this->service  (authorize1)", false, false);	
			}
		} else {
			do_error(_('acceso denegado'), false, false);	
		}

		$this->token = $access_token_info['oauth_token'];
		$this->secret = $access_token_info['oauth_token_secret'];
		$this->uid = $access_token_info['user_id'];
		$this->username = User::get_valid_username($access_token_info['screen_name']);
		if (!$this->user_exists()) {
			$this->oauth->setToken($access_token_info['oauth_token'], $access_token_info['oauth_token_secret']);
			try {
				$data = $this->oauth->fetch($this->credentials_url);
			} catch (Exception $e) {
				do_error(_('error de conexión a') . " $this->service (authorize2)", false, false);	
			}

			if($data){
				$response_info = $this->oauth->getLastResponse();
				$response = json_decode($response_info);
				$this->url = $response->url;
				$this->names = $response->name;
				$this->avatar = $response->profile_image_url;
			}
			$db->transaction();
			$this->store_user();
		} else {
			$db->transaction();
		}
		$this->store_auth();
		$db->commit();
		$this->user_login();
	}
}

?>
