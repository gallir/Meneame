<?php
/*
 * Copyright 2010 Google Inc.
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

// Check for the required json and curl extensions, the google api php client won't function without
if (! function_exists('curl_init')) {
  throw new Exception('The Google PHP API Library needs the CURL PHP extension');
}
if (! function_exists('json_decode')) {
  throw new Exception('The Google PHP API Library needs the JSON PHP extension');
}

// hack around with the include paths a bit so the library 'just works'
$cwd = dirname(__FILE__);
set_include_path("$cwd" . PATH_SEPARATOR . ":" . get_include_path());

require_once "config.php";
// If a local configuration file is found, merge it's values with the default configuration
if (file_exists($cwd . '/local_config.php')) {
  $defaultConfig = $apiConfig;
  require_once ($cwd . '/local_config.php');
  $apiConfig = array_merge($defaultConfig, $apiConfig);
}

// Include the top level classes, they each include their own dependencies
require_once "auth/apiAuth.php";
require_once "cache/apiCache.php";
require_once "io/apiIO.php";
require_once "service/apiService.php";

// Exceptions that the Google PHP API Library can throw
class apiException extends Exception {}
class apiAuthException extends apiException {}
class apiCacheException extends apiException {}
class apiIOException extends apiException {}
class apiServiceException extends apiException {}

/* global array of type handlers, used by the api request executers to parse results
 * maps the type strings ('buzz#activity') to a class representation (buzzAcvitityModel) which will be automatically triggered on input
 */
global $apiTypeHandlers;
$apiTypeHandlers = array();

/**
 * The Google API Client
 * http://code.google.com/p/google-api-php-client/
 *
 * @author Chris Chabot <chabotc@google.com>
 */
class apiClient {

  // the version of the discovery mechanism this class is meant to work with
  const discoveryVersion = 'v0.3';

  // worker classes
  protected $auth;
  protected $io;
  protected $cache;
  protected $scopes = array();
  protected $useObjects = false;

  // definitions of services that are discover()'rd
  protected $services = array();

  // Used to track authenticated state, can't discover services after doing authenticate()
  private $authenticated = false;

  private $defaultService = array(
      'authorization_token_url' => 'https://www.google.com/accounts/OAuthAuthorizeToken',
      'request_token_url' => 'https://www.google.com/accounts/OAuthGetRequestToken',
      'access_token_url' => 'https://www.google.com/accounts/OAuthGetAccessToken');

  public function __construct() {
    global $apiConfig;
    // Create our worker classes
    $this->cache = new $apiConfig['cacheClass']();
    $this->auth = new $apiConfig['authClass']();
    $this->io = new $apiConfig['ioClass']($this->cache, $this->auth);
    $this->auth->setIo($this->io);
  }

  public function discover($service, $version = 'v1') {
    $this->addService($service, $version);
    $this->$service = $this->discoverService($service, $this->services[$service]['discoveryURI']);
    return $this->$service;
  }

  /**
   * Add a service
   */
  public function addService($service, $version) {
    global $apiConfig;
    if ($this->authenticated) {
      // Adding services after being authenticated, since the oauth scope is already set (so you wouldn't have access to that data)
      throw new apiException("Can't add services after having authenticated");
    }
    $this->services[$service] = $this->defaultService;
    if (isset($apiConfig['services'][$service])) {
      // Merge the service descriptor with the default values
      $this->services[$service] = array_merge($this->services[$service], $apiConfig['services'][$service]);
    }
    $this->services[$service]['discoveryURI'] = $apiConfig['basePath'] . '/discovery/' . self::discoveryVersion . '/describe/' . urlencode($service) . '/' . urlencode($version);
  }

  /**
   * Set the type of Auth class the client should use.
   * @param string $authClassName
   */
  public function setAuthClass($authClassName) {
    $this->auth = null;
    $this->auth = new $authClassName();
  }

  public function authenticate() {
    $service = $this->prepareService();
    $this->authenticated = true;
    return $this->auth->authenticate($service);
  }

  
  public function createAuthUrl() {
    $service = $this->prepareService();
    return $this->auth->createAuthUrl($service);
  }

  private function prepareService() {
    $service = $this->defaultService;
    $scopes = array();
    if ($this->scopes) {
      $scopes = $this->scopes;
    } else {
      foreach ($this->services as $key => $val) {
        if (isset($val['scope'])) {
          if (is_array($val['scope'])) {
            $scopes = array_merge($val['scope'], $scopes);
          } else {
            $scopes[] = $val['scope'];
          }
        } else {
          $scopes[] = 'https://www.googleapis.com/auth/' . $key;
        }
        unset($val['discoveryURI']);
        unset($val['scope']);
        $service = array_merge($service, $val);
      }
    }
    $service['scope'] = implode(' ', $scopes);
    return $service;
  }

  /**
   * Set the OAuth access token using the string that resulted from calling authenticate()
   * @param (serialized) string $accessToken
   */
  public function setAccessToken($accessToken) {
    if ($accessToken == null || 'null' == $accessToken) {
      $accessToken = null;
    }
    $this->auth->setAccessToken($accessToken);
  }

  public function getAccessToken() {
    $token = $this->auth->getAccessToken();
    return (null == $token || 'null' == $token) ? null : $token;
  }

  /**
   * Set the developer key to use, these are obtained through the API Console
   * @param string $developerKey
   */
  public function setDeveloperKey($developerKey) {
    $this->auth->setDeveloperKey($developerKey);
  }

  /**
   * Set the application name, this is included in the User-Agent HTTP header.
   * @param string $applicationName
   */
  public function setApplicationName($applicationName) {
    global $apiConfig;
    $apiConfig['application_name'] = $applicationName;
  }

  /**
   * Set the OAuth2 Client ID.
   * @param string $clientId
   */
  public function setClientId($clientId) {
    global $apiConfig;
    $apiConfig['oauth2_client_id'] = $clientId;
    $this->auth->clientId = $clientId;
  }
  
  /**
   * Set the OAuth2 Client Secret.
   * @param string $clientSecret
   */
  public function setClientSecret($clientSecret) {
    global $apiConfig;
    $apiConfig['oauth2_client_secret'] = $clientSecret;
    $this->auth->clientSecret = $clientSecret;
  }

  /**
   * Set the OAuth2 Redirect URI.
   * @param string $redirectUri
   */
  public function setRedirectUri($redirectUri) {
    global $apiConfig;
    $apiConfig['oauth2_redirect_uri'] = $redirectUri;
    $this->auth->redirectUri = $redirectUri;
  }

  /**
   * This function allows you to overrule the automatically generated scopes, so that you can ask for more or less permission in the auth flow
   * Set this before you call authenticate() though!
   * @param array $scopes, ie: array('https://www.googleapis.com/auth/buzz', 'https://www.googleapis.com/auth/moderator')
   */
  public function setScopes($scopes) {
    $this->scopes = is_string($scopes) ? explode(" ", $scopes) : $scopes;
  }

  /**
   * Declare if objects should be returned by the api service classes.
   *
   * @param boolean $useObjects True if objects should be returned by the service classes.
   * False if associative arrays should be returned (default behavior).
   */
  public function setUseObjects($useObjects) {
    global $apiConfig;
    $apiConfig['use_objects'] = $useObjects;
  }

  private function discoverService($serviceName, $serviceURI) {
    $request = $this->io->makeRequest(new apiHttpRequest($serviceURI));
    if ($request->getResponseHttpCode() != 200) {
      throw new apiException("Could not fetch discovery document for $serviceName, http code: " . $request->getResponseHttpCode() . ", response body: " . $request->getResponseBody());
    }
    $discoveryResponse = $request->getResponseBody();
    $discoveryDocument = json_decode($discoveryResponse, true);
    if ($discoveryDocument == NULL) {
      throw new apiException("Invalid json returned for $serviceName");
    }
    return new apiService($serviceName, $discoveryDocument, $this->io);
  }

  public function registerTypeHandler($type, $handlerClass) {
    global $apiTypeHandlers;
    $apiTypeHandlers[$type] = $handlerClass;
  }

  /*
   * @return apiIo the implementation of apiIo.
   */
  public function getIo() {
    return $this->io;
  }

  /*
   * @return apiCache the implementation of apiCache.
   */
  public function getCache() {
    return $this->cache;
  }
}
