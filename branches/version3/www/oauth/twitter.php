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
	const request_token_url = 'http://twitter.com/oauth/request_token';
	const access_token_url = 'http://twitter.com/oauth/access_token';
	const authorize_url =  'http://twitter.com/oauth/authenticate';
	const credentials_url = 'http://twitter.com/account/verify_credentials.json';

	function __construct() {
		global $globals;
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
			if (($request_token_info = $this->oauth->getRequestToken(self::request_token_url))) {
				setcookie('oauth_token', $request_token_info['oauth_token'], $globals['now'] + 3600);
				setcookie('oauth_token_secret', $request_token_info['oauth_token_secret'], $globals['now'] + 3600);
				$this->token_secret = $request_token_info['oauth_token_secret'];
				$this->token = $request_token_info['oauth_token'];
				header("Location: ".self::authorize_url."?oauth_token=$this->token");
				exit;
			} else {
				do_error(_('error de obteniendo tokens'), false, false);	
			}
		} catch (Exception $e) {
				do_error(_('error de conexión a') . " $this->service", false, false);	
		}
	}

	function authorize() {
		$oauth_token = clean_input_string($_GET['oauth_token']);
		$request_token_secret = $_COOKIE['oauth_token_secret'];

		if(!empty($oauth_token) && !empty($request_token_secret) ){
			$this->oauth->setToken($oauth_token, $request_token_secret);
			try {
				$access_token_info = $this->oauth->getAccessToken(self::access_token_url);
			} catch (Exception $e) {
				do_error(_('error de conexión a') . " $this->service", false, false);	
			}
		} else {
			do_error(_('acceso denegado'), false, false);	
		}

		$this->token = $access_token_info['oauth_token'];
		$this->secret = $access_token_info['oauth_token_secret'];
		$this->uid = $access_token_info['user_id'];
		$this->username = $access_token_info['screen_name'];
		if (!$this->user_exists()) {
			$this->oauth->setToken($access_token_info['oauth_token'], $access_token_info['oauth_token_secret']);
			try {
				$data = $this->oauth->fetch(self::credentials_url);
			} catch (Exception $e) {
				do_error(_('error de conexión a') . " $this->service", false, false);	
			}

			if($data){
				$response_info = $this->oauth->getLastResponse();
				$response = json_decode($response_info);
				$this->url = $response->url;
				$this->names = $response->name;
				$this->avatar = $response->profile_image_url;
			}
			$this->store_user();
		}
		$this->store_auth();
		$this->user_login();
	}
}

?>
