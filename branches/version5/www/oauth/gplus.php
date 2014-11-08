<?php
// The Meneame source code is Free Software, Copyright (C) 2011 by
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
require_once('googleapi/apiClient.php');
require_once('googleapi/contrib/apiPlusService.php');

class GPlusOAuth extends OAuthBase {

	function __construct() {
		global $globals;

		if (! $globals['oauth']['gplus']['consumer_key'] || ! $globals['oauth']['gplus']['consumer_secret']) {
			$oauth = null;
		}
		$this->service = 'gplus';

		$callback = $globals['scheme'].'//'.get_server_name().$globals['base_url'].'oauth/signin.php?service=gplus';

		$this->client = new apiClient();
		$this->client->setClientId($globals['oauth']['gplus']['consumer_key']);
		$this->client->setClientSecret($globals['oauth']['gplus']['consumer_secret']);
		$this->client->setRedirectUri($callback);
		// $this->client->setApprovalPrompt('auto'); // TODO: pass to auto, check token is ok
		$this->client->setApplicationName("Menéame Login");
		$this->gplus = new apiPlusService($this->client);
		parent::__construct();
	}

	function authRequest() {
		global $globals;
		try {
			$this->client->authenticate();
		} catch (Exception $e) {
			do_error(_('error de conexión a') . " $this->service (authRequest)", false, false);	
		}
	}

	function authorize() {
		global $globals, $db;

		if (empty($_GET['code'])) {
			do_error(_('acceso denegado'), false, false);	
		}

		try {
			$this->client->setAccessToken($this->client->authenticate());
			if (! ($access_token = $this->client->getAccessToken())) {
				do_error(_('acceso denegado'), false, false);	
			}
			$response = $this->gplus->people->get('me');
			$this->uid = $response['id'];
			$this->username = User::get_valid_username($response['displayName']);
		} catch (Exception $e) {
			do_error(_('error de conexión a') . " $this->service (authorize2)", false, false);	
		}

		$db->transaction();
		if (!$this->user_exists()) {
			$this->url = $response['url'];
			$this->names = $response['displayName'];
			$this->avatar = $response['image']['url'];
			$this->store_user();
		}
		$this->store_auth();
		$db->commit();
		$this->user_login();
	}
}

?>
