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

require_once "apiVerifier.php";
require_once "apiLoginTicket.php";
require_once "service/apiUtils.php";

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
  public $state;
  public $accessType = 'offline';
  public $approvalPrompt = 'force';

  const OAUTH2_TOKEN_URI = "https://accounts.google.com/o/oauth2/token";
  const OAUTH2_AUTH_URL = "https://accounts.google.com/o/oauth2/auth";
  const OAUTH2_FEDERATED_SIGNON_CERTS_URL = "https://www.googleapis.com/oauth2/v1/certs";
  const CLOCK_SKEW_SECS = 300; // five minutes in seconds
  const AUTH_TOKEN_LIFETIME_SECS = 300; // five minutes in seconds
  const MAX_TOKEN_LIFETIME_SECS = 86400; // one day in seconds

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
    
    if (! empty($apiConfig['oauth2_access_type'])) {
      $this->accessType = $apiConfig['oauth2_access_type'];
    }

    if (! empty($apiConfig['oauth2_approval_prompt'])) {
      $this->approvalPrompt = $apiConfig['oauth2_approval_prompt'];
    }
  }

  // Initializes IO handler.
  private function initIo() {
    if ($this->io == null) {
      global $apiClient;
      $this->io = $apiClient->getIo();
    }
    return $this->io;
  }

  public function authenticate($service) {
    $this->initIo();

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
        'scope=' . urlencode($service['scope']),
        'access_type=' . urlencode($this->accessType),
        'approval_prompt=' . urlencode($this->approvalPrompt)
    );

    if (isset($this->state)) {
      $params[] = 'state=' . urlencode($this->state);
    }
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

  public function setState($state) {
    $this->state = $state;
  }

  public function setAccessType($accessType) {
    $this->accessType = $accessType;
  }

  public function setApprovalPrompt($approvalPrompt) {
    $this->approvalPrompt = $approvalPrompt;
  }

  public function sign(apiHttpRequest $request) {
    $this->initIo();
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

  // Gets federated sign-on certificates to use for verifying identity tokens.
  // Returns certs as array structure, where keys are key ids, and values
  // are PEM encoded certificates.
  private function getFederatedSignOnCerts() {
    $this->initIo();
    // This relies on makeRequest caching certificate responses.
    $request = $this->io->makeRequest(new apiHttpRequest(
        self::OAUTH2_FEDERATED_SIGNON_CERTS_URL));
    if ((int)$request->getResponseHttpCode() == 200) {
      $certs = json_decode($request->getResponseBody(), true);
      if ($certs) {
        return $certs;
      }
    }
    throw new apiAuthException(
        "Failed to retrieve verification certificates: '" . 
            $request->getResponseBody() . "'.",
        $request->getResponseHttpCode());
  }

  /**
   * Verifies an id token and returns the authenticated apiLoginTicket.
   * Throws an exception if the id token is not valid.
   * The audience parameter can be used to control which id tokens are
   * accepted.  By default, the id token must have been issued to this OAuth2 client.
   *
   * @param $id_token
   * @param $audience
   * @return apiLoginTicket
   */
  function verifyIdToken($id_token, $audience = null) {
    $certs = $this->getFederatedSignonCerts();
    if (!$audience) {
      $audience = $this->clientId;
    }
    return $this->verifySignedJwtWithCerts($id_token, $certs, $audience);
  }

  // Verifies the id token, returns the verified token contents.
  //
  // Visible for testing.
  function verifySignedJwtWithCerts($jwt, $certs, $required_audience) {
    $segments = explode(".", $jwt);
    if (count($segments) != 3) {
      throw new apiAuthException("Wrong number of segments in token: $jwt");
    }
    $signed = $segments[0] . "." . $segments[1];
    $signature = apiUtils::urlSafeB64Decode($segments[2]);

    // Parse envelope.
    $envelope = json_decode(apiUtils::urlSafeB64Decode($segments[0]), true);
    if (!$envelope) {
      throw new apiAuthException("Can't parse token envelope: " . $segments[0]);
    }

    // Parse token
    $json_body = apiUtils::urlSafeB64Decode($segments[1]);
    $payload = json_decode($json_body, true);
    if (!$payload) {
      throw new apiAuthException("Can't parse token payload: " . $segments[1]);
    }

    // Check signature
    $verified = false;
    foreach ($certs as $keyName => $pem) {
      $public_key = new apiPemVerifier($pem);
      if ($public_key->verify($signed, $signature)) {
        $verified = true;
        break;
      }
    }

    if (!$verified) {
      throw new apiAuthException("Invalid token signature: $jwt");
    }

    // Check issued-at timestamp
    $iat = 0;
    if (array_key_exists("iat", $payload)) {
      $iat = $payload["iat"];
    }
    if (!$iat) {
      throw new apiAuthException("No issue time in token: $json_body");
    }
    $earliest = $iat - self::CLOCK_SKEW_SECS;

    // Check expiration timestamp
    $now = time();
    $exp = 0;
    if (array_key_exists("exp", $payload)) {
      $exp = $payload["exp"];
    }
    if (!$exp) {
      throw new apiAuthException("No expiration time in token: $json_body");
    }
    if ($exp >= $now + self::MAX_TOKEN_LIFETIME_SECS) {
      throw new apiAuthException(
          "Expiration time too far in future: $json_body");
    }

    $latest = $exp + self::CLOCK_SKEW_SECS;
    if ($now < $earliest) {
      throw new apiAuthException(
          "Token used too early, $now < $earliest: $json_body");
    }
    if ($now > $latest) {
      throw new apiAuthException(
          "Token used too late, $now > $latest: $json_body");
    }

    // TODO(beaton): check issuer field?

    // Check audience
    $aud = $payload["aud"];
    if ($aud != $required_audience) {
      throw new apiAuthException("Wrong recipient, $aud != $required_audience: $json_body");
    }

    // All good.
    return new apiLoginTicket($envelope, $payload);
  }
}
