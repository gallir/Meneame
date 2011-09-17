<?php
/*
 * Copyright 2008 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Authentication class that deals with the OAuth 2 web-server authentication flow
 *
 * @author Chris Chabot <chabotc@google.com>
 *
 */
class apiOAuth2 extends apiAuth {
  public $clientId;
  public $clientSecret;
  public $developerKey;
  public $accessToken;
  public $redirectUri;

  const OAUTH2_TOKEN_URI = "https://accounts.google.com/o/oauth2/token";
  const OAUTH2_AUTH_URL = "https://accounts.google.com/o/oauth2/auth";

  /**
   * Instantiates the class, but does not initiate the login flow, leaving it
   * to the discretion of the caller (which is done by calling authenticate()).
   */
  public function __construct() {
    global $apiConfig;
    
    if (! empty($apiConfig['developer_key'])) {
      $this->developerKey = $apiConfig['developer_key'];
    }

    if (! empty($apiConfig['oauth2_client_id'])) {
      $this->clientId = $apiConfig['oauth2_client_id'];
    }

    if (! empty($apiConfig['oauth2_client_secret'])) {
      $this->clientSecret = $apiConfig['oauth2_client_secret'];
    }

    if (! empty($apiConfig['oauth2_redirect_uri'])) {
      $this->redirectUri = $apiConfig['oauth2_redirect_uri'];
    }
  }

  public function authenticate($service) {
    if ($this->io == null) {
      global $apiClient;
      $this->io = $apiClient->getIo();
    }

    if (isset($_GET['code'])) {
      // We got here from the redirect from a successful authorization grant, fetch the access token
      $request = $this->io->makeRequest(new apiHttpRequest(self::OAUTH2_TOKEN_URI, 'POST', array(), array(
          'code' => $_GET['code'],
          'grant_type' => 'authorization_code',
          'redirect_uri' => $this->redirectUri,
          'client_id' => $this->clientId,
          'client_secret' => $this->clientSecret
      )));
      if ((int)$request->getResponseHttpCode() == 200) {
        $this->setAccessToken($request->getResponseBody());
        $this->accessToken['created'] = time();
        return $this->getAccessToken();
      } else {
        $response = $request->getResponseBody();
        $decodedResponse = json_decode($response, true);
        if ($decodedResponse != $response && $decodedResponse != null && $decodedResponse['error']) {
          $response = $decodedResponse['error'];
        }
        throw new apiAuthException("Error fetching OAuth2 access token, message: '$response'", $request->getResponseHttpCode());
      }
    }

    $authUrl = $this->createAuthUrl($service);
    header('Location: ' . $authUrl);
  } 

  public function createAuthUrl($service) {
    $params = array(
        'response_type=code',
        'redirect_uri=' . urlencode($this->redirectUri),
        'client_id=' . urlencode($this->clientId),
        'scope=' . urlencode($service['scope'])
    );
    $params = implode('&', $params);
    return self::OAUTH2_AUTH_URL . "?$params";
  }

  public function setAccessToken($accessToken) {
    $accessToken = json_decode($accessToken, true);
    if ($accessToken == null) {
      throw new apiAuthException("Could not json decode the access token");
    }
    if (! isset($accessToken['access_token']) || ! isset($accessToken['expires_in']) || ! isset($accessToken['refresh_token'])) {
      throw new apiAuthException("Invalid token format");
    }
    $this->accessToken = $accessToken;
  }

  public function getAccessToken() {
    return json_encode($this->accessToken);
  }

  public function setDeveloperKey($developerKey) {
    $this->developerKey = $developerKey;
  }

  public function sign(apiHttpRequest $request) {
    // add the developer key to the request before signing it
    if ($this->developerKey) {
      $request->setUrl($request->getUrl() . ((strpos($request->getUrl(), '?') === false) ? '?' : '&') . 'key=' . urlencode($this->developerKey));
    }

    // Cannot sign the request without an OAuth access token.
    if (null == $this->accessToken) {
      return $request;
    }

    if (($this->accessToken['created'] + ($this->accessToken['expires_in'] - 30)) < time()) {
      // if the token is set to expire in the next 30 seconds (or has already expired), refresh it and set the new token
      //FIXME this is mostly a copy and paste mashup from the authenticate and setAccessToken functions, should generalize them into a function instead of this mess
      $refreshRequest = $this->io->makeRequest(new apiHttpRequest(self::OAUTH2_TOKEN_URI, 'POST', array(), array(
          'client_id' => $this->clientId,
          'client_secret' => $this->clientSecret,
          'refresh_token' => $this->accessToken['refresh_token'],
          'grant_type' => 'refresh_token'
      )));
      
      if ((int)$refreshRequest->getResponseHttpCode() == 200) {
        $token = json_decode($refreshRequest->getResponseBody(), true);
        if ($token == null) {
          throw new apiAuthException("Could not json decode the access token");
        }
        if (! isset($token['access_token']) || ! isset($token['expires_in'])) {
          throw new apiAuthException("Invalid token format");
        }
        $this->accessToken['access_token'] = $token['access_token'];
        $this->accessToken['expires_in'] = $token['expires_in'];
        $this->accessToken['created'] = time();
      } else {
        $response = $refreshRequest->getResponseBody();
        $decodedResponse = json_decode($response, true);
        if ($decodedResponse != $response && $decodedResponse != null && $decodedResponse['error']) {
          $response = $decodedResponse['error'];
        }
        throw new apiAuthException("Error refreshing the OAuth2 token, message: '$response'", $refreshRequest->getResponseHttpCode());
      }
    }

    // Add the OAuth2 header to the request
    $headers = $request->getHeaders();
    $headers[] = "Authorization: OAuth " . $this->accessToken['access_token'];
    $request->setHeaders($headers);

    return $request;
  }

}
