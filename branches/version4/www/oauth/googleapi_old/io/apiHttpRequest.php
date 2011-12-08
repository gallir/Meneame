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

/**
 * HTTP Request used to execute http requests using the apiIO classes. On execution the
 * responseHttpCode, responseHeaders and responseBody will be filled in.
 *
 * @author Chris Chabot <chabotc@google.com>
 * @author Chirag Shah <chirags@google.com>
 *
 */
class apiHttpRequest {
  const USER_AGENT_SUFFIX = "google-api-php-client/0.4.7";

  protected $url;
  protected $method;
  protected $headers;
  protected $postBody;
  protected $userAgent;

  protected $responseHttpCode;
  protected $responseHeaders;
  protected $responseBody;
  
  public $accessKey;

  public function __construct($url, $method = 'GET', $headers = array(), $postBody = null) {
    $this->url = $url;
    // force the method name to always be upper case so we can do sane comparisons on it
    $this->method = strtoupper($method);
    $this->headers = $headers;
    $this->postBody = $postBody;

    global $apiConfig;
    if (empty($apiConfig['application_name'])) {
      $this->userAgent = apiHttpRequest::USER_AGENT_SUFFIX;
    } else {
      $this->userAgent = $apiConfig['application_name'] . " " . apiHttpRequest::USER_AGENT_SUFFIX;
    }
  }

  /**
   * Misc function that returns the base url component of the $url
   * used by the OAuth signing class to calculate the base string
   * @return string The base url component of the $url.
   * @see http://oauth.net/core/1.0a/#anchor13
   */
  public function getBaseUrl() {
    if ($pos = strpos($this->url, '?')) {
      return substr($this->url, 0, $pos);
    }
    return $this->url;
  }

  /**
   * Misc function that returns an array of the query parameters of the current url
   * used by the OAuth signing class to calculate the signature
   * @return array Query parameters in the query string.
   */
  public function getQueryParams() {
    if ($pos = strpos($this->url, '?')) {
      $queryStr = substr($this->url, $pos + 1);
      $params = array();
      parse_str($queryStr, $params);
      return $params;
    }
    return array();
  }

  /**
   * @return string HTTP Response Code.
   */
  public function getResponseHttpCode() {
    return $this->responseHttpCode;
  }

  /**
   * @param int $responseHttpCode HTTP Response Code.
   */
  public function setResponseHttpCode($responseHttpCode) {
    $this->responseHttpCode = $responseHttpCode;
  }

  /**
   * @return $responseHeaders (array) HTTP Response Headers.
   */
  public function getResponseHeaders() {
    return $this->responseHeaders;
  }

  /**
   * @return string HTTP Response Body
   */
  public function getResponseBody() {
    return $this->responseBody;
  }

  /**
   * @param array $responseHeaders The HTTP Response Headers.
   */
  public function setResponseHeaders($responseHeaders) {
    $this->responseHeaders = $responseHeaders;
  }

  /**
   * @param string $responseBody $responseBody to set.
   */
  public function setResponseBody($responseBody) {
    $this->responseBody = $responseBody;
  }

  /**
   * @return string $url The request URL.
   */

  public function getUrl() {
    return $this->url;
  }

  /**
   * @return string $method
   */
  public function getMethod() {
    return $this->method;
  }

  /**
   * @return array the $headers
   */
  public function getHeaders() {
    return $this->headers;
  }

  /**
   * @return string the $postBody
   */
  public function getPostBody() {
    return $this->postBody;
  }

  /**
   * @param string $url the url to set
   */
  public function setUrl($url) {
    $this->url = $url;
  }

  /**
   * @param string $method the method to set
   */
  public function setMethod($method) {
    $this->method = $method;
  }

  /**
   * @param array $headers the headers to set
   */
  public function setHeaders($headers) {
    $this->headers = $headers;
  }

  /**
   * @param string $header the header to add.
   */
  public function addHeader($header) {
    if (null == $this->headers) {
      $this->headers = array();
    }

    $this->headers[] = $header;
  }

  /**
   * @param string $postBody the postBody to set
   */
  public function setPostBody($postBody) {
    $this->postBody = $postBody;
  }

  /**
   * Set the User-Agent Header.
   * @param string $userAgent The User-Agent.
   */
  public function setUserAgent($userAgent) {
    $this->userAgent = $userAgent;
  }

  /**
   * @return string The User-Agent.
   */
  public function getUserAgent() {
    return $this->userAgent;
  }
}
