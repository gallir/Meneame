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
 * Do-nothing authentication implementation, use this if you want to make un-authenticated calls
 * @author Chris Chabot <chabotc@google.com>
 *
 */
class apiAuthNone extends apiAuth {

  public $developerKey = null;

  public function __construct() {
    global $apiConfig;
    if (!empty($apiConfig['developer_key'])) {
      $this->setDeveloperKey($apiConfig['developer_key']);
    }
  }

  public function authenticate($service) {
    // noop
  }

  public function setAccessToken($accessToken) {
    // noop
  }

  public function getAccessToken() {
    // noop
    return null;
  }

  /**
   * Set the developer key to use, these are obtained through the API Console
   */
  public function setDeveloperKey($developerKey) {
    $this->developerKey = $developerKey;
  }

  public function sign(apiHttpRequest $request) {
    if ($this->developerKey) {
      $request->setUrl($request->getUrl() . ((strpos($request->getUrl(), '?') === false) ? '?' : '&') . 'key='.urlencode($this->developerKey));
    }
    return $request;
  }
}
