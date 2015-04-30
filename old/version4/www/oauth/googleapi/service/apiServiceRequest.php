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
 * Internal representation of a Google API request, used by the apiServiceResource class to
 * construct API function calls and passing them to the IO layer who knows how to execute
 * the request
 *
 * @author Chris Chabot <chabotc@google.com>
 * @author Chirag Shah <chirags@google.com>
 *
 */
class apiServiceRequest {

  protected $io;
  protected $restBasePath;
  protected $restPath;
  protected $rpcPath;
  protected $rpcName;
  protected $httpMethod;
  protected $parameters;
  protected $postBody;
  protected $batchKey;

  /**
   * @param apiIO $io
   * @param string $restBasePath
   * @param string $rpcPath
   * @param string $restPath
   * @param string $rpcName
   * @param string $httpMethod
   * @param $parameters
   * @param $postBody
   */
  public function __construct(apiIO $io, $restBasePath, $rpcPath, $restPath, $rpcName, $httpMethod, $parameters, $postBody = null) {
    $this->io = $io;

    if (substr($restBasePath, 0, 4) == 'http') {
      $this->restBasePath = $restBasePath;
    } else {
      global $apiConfig;
      $this->restBasePath = $apiConfig['basePath'] . $restBasePath;
    }

    $this->restPath = $restPath;
    $this->rpcPath = $rpcPath;
    $this->rpcName = $rpcName;
    $this->httpMethod = $httpMethod;
    $this->parameters = $parameters;
    $this->postBody = $postBody;
  }

  /**
   * @return the $postBody
   */
  public function getPostBody() {
    return $this->postBody;
  }

  /**
   * @param $postBody the $postBody to set
   */
  public function setPostBody($postBody) {
    $this->postBody = $postBody;
  }

  /**
   * @return apiIo $io
   */
  public function getIo() {
    return $this->io;
  }

  /**
   * @param apiIo $io
   */
  public function setIo($io) {
    $this->io = $io;
  }

  /**
   * @param $baseUrl the $baseUrl to set
   */
  public function setBaseUrl($baseUrl) {
    $this->baseUrl = $baseUrl;
  }

  /**
   * @return the $restBasePath
   */
  public function getRestBasePath() {
    return $this->restBasePath;
  }

  /**
   * @return the restPath
   */
  public function getRestPath() {
    return $this->restPath;
  }

  /**
   * @return string $rpcPath
   */
  public function getRpcPath() {
    return $this->rpcPath;
  }

  /**
   * @return string $rpcName
   */
  public function getRpcName() {
    return $this->rpcName;
  }

  /**
   * @return string $httpMethod
   */
  public function getHttpMethod() {
    return $this->httpMethod;
  }

  /**
   * @return array $parameters
   */
  public function getParameters() {
    return $this->parameters;
  }

  /**
   * @param $restBasePath the $restBasePath to set
   */
  public function setRestBasePath($restBasePath) {
    $this->restBasePath = $restBasePath;
  }

  /**
   * @param $restPath the $restPath to set
   */
  public function setRestPath($restPath) {
    $this->restPath = $restPath;
  }

  /**
   * @param $rpcPath the $rpcPath to set
   */
  public function setRpcPath($rpcPath) {
    $this->rpcPath = $rpcPath;
  }

  /**
   * @return string $batchKey
   */
  public function getBatchKey() {
    return $this->batchKey;
  }

  /**
   * @param $batchKey the $batchKey to set
   */
  public function setBatchKey($batchKey) {
    $this->batchKey = $batchKey;
  }

}
