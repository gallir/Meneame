<?php
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


class FBConnect extends OAuthBase
{
    public function __construct()
    {
        global $globals;

        $this->service = 'facebook';

        if (!session_id()) {
            session_start();
        }

        parent::__construct();

        $this->facebook = new Facebook\Facebook([
            'app_id' => $globals['facebook_key'], // Replace {app-id} with your app id
            'app_secret' => $globals['facebook_secret'],
            'default_graph_version' => 'v2.9'
        ]);
    }

    public function authRequest()
    {
        global $globals;

        $helper = $this->facebook->getRedirectLoginHelper();
        $loginUrl = $helper->getLoginUrl($globals['scheme']. '//' . $globals['server_name'] . $globals['base_url']. 'oauth/fbconnect.php', ['email']);
        echo "<html><head>\n";
        echo '<script type="text/javascript">' . "\n";
        echo 'self.location = "' . $loginUrl . '";' . "\n";
        echo '</script>' . "\n";
        echo '</head><body></body></html>' . "\n";
        exit;
    }

    public function authorize()
    {
        global $globals, $db;

        $helper = $this->facebook->getRedirectLoginHelper();
        try {
            $accessToken = $helper->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        $oAuth2Client = $this->facebook->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $tokenMetadata->validateAppId($globals['facebook_key']);

        $tokenMetadata->validateExpiration();

        if (!$accessToken->isLongLived()) {
            try {
                $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (Facebook\Exceptions\FacebookSDKException $e) {
                echo "<p>Error getting long-lived access token: " . $this->helper->getMessage() . "</p>\n\n";
                exit;
            }
        }

        $this->facebook->setDefaultAccessToken((string)$accessToken);

        try {
            $user_profile = $this->facebook->get('/me?fields=id,email,name,short_name,link,picture.type(large),website')->getGraphUser()->asArray();
        } catch (FacebookApiException $e) {
            $this->user = null;
            $this->user_return();
            die;
        }

        $this->token = $user_profile['id'];
        $this->secret = $user_profile['id'];
        $this->uid = $user_profile['id'];

        $this->username = preg_replace('/.+?\/.*?([\w\.\-_]+)$/', '$1', $user_profile['name']);

        $this->username = User::get_valid_username($user_profile['name']);

        $db->transaction();

        if (!$this->user_exists()) {
            $this->url = $user_profile['link'];
            $this->names = $this->username;
            $this->avatar = $user_profile['picture']['url'];
            $this->email = $user_profile['email'];
            $this->store_user();
        }
        $this->store_auth();
        $db->commit();
        $this->user_login();
    }
}

$auth = new FBConnect();

if (empty($_SESSION['FBRLH_state'])) {
    $auth->authRequest();
} else {
    $auth->authorize();
}