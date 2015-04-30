<?php
/**
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
 * Implements the actual methods/resources of the discovered Google API using magic function
 * calling overloading (__call()), which on call will see if the method name (plus.activities.list)
 * is available in this service, and if so construct an apiServiceRequest representing it.
 *
 * @author Chris Chabot <chabotc@google.com>
 *
 */
class apiServiceResource {
  // Valid query parameters that work, but don't appear in discovery.
  private $stackParameters = array(
      'fields' => array('type' => 'string', 'location' => 'query'),
      'trace' => array('type' => 'string', 'location' => 'query'),
      'userIp' => array('type' => 'string', 'location' => 'query'),
      'userip' => array('type' => 'string', 'location' => 'query')
  );

  /** @var apiService $service */
  private $service;
  private $serviceName;
  private $resourceName;

  /** @var array $methods */
  private $methods;

  public function __construct($service, $serviceName, $resourceName, $resource) {
    $this->service = $service;
    $this->serviceName = $serviceName;
    $this->resourceName = $resourceName;
    $this->methods = $resource['methods'];
  }

  public function __call($name, $arguments) {
    if (count($arguments) != 1 && count($arguments) != 2) {
      throw new apiException("apiClient method calls expect 1 or 2 parameter (for example: \$client->plus->activities->list(array('userId' => 'me'))");
    }
    if (! is_array($arguments[0])) {
      throw new apiException("apiClient method parameter should be an array (for example: \$client->plus->activities->list(array('userId' => 'me'))");
    }
    $batchKey = false;
    if (isset($arguments[1])) {
      if (! is_string($arguments[1])) {
        throw new apiException("The batch key parameter should be a string, for example: \$client->buzz->activities->list( array('userId' => '@me'), 'batchKey')");
      }
      $batchKey = $arguments[1];
    }
    if (! isset($this->methods[$name])) {
      throw new apiException("Unknown function: {$this->serviceName}->{$this->resourceName}->{$name}()");
    }
    $method = $this->methods[$name];
    $parameters = $arguments[0];
    // postBody is a special case since it's not defined in the discovery document as parameter, but we abuse the param entry for storing it
    $postBody = null;
    if (isset($parameters['postBody'])) {
      if (is_object($parameters['postBody'])) {
        $this->stripNull($parameters['postBody']);
      }

      // Some APIs require the postBody to be set under the data key.
      if (is_array($parameters['postBody']) && 'buzz' == $this->serviceName) {
        if (!isset($parameters['postBody']['data'])) {
          $rawBody = $parameters['postBody'];
          unset($parameters['postBody']);
          $parameters['postBody']['data'] = $rawBody;
        }
      }

      $postBody = is_array($parameters['postBody']) || is_object($parameters['postBody']) ? json_encode($parameters['postBody']) : $parameters['postBody'];
      // remove from the parameter list so not to trip up the param entry checking & make sure it doesn't end up on the query
      unset($parameters['postBody']);

      if (isset($parameters['optParams'])) {
        $optParams = $parameters['optParams'];
        unset($parameters['optParams']);

        $parameters = array_merge($parameters, $optParams);
      }
    }

    if (!isset($method['parameters'])) {
      $method['parameters'] = array();
    }
    
    $method['parameters'] = array_merge($method['parameters'], $this->stackParameters);
    foreach ($parameters as $key => $val) {
      if ($key != 'postBody' && ! isset($method['parameters'][$key])) {
        throw new apiException("($name) unknown parameter: '$key'");
      }
    }
    if (isset($method['parameters'])) {
      foreach ($method['parameters'] as $paramName => $paramSpec) {
        if (isset($paramSpec['required']) && $paramSpec['required'] && ! isset($parameters[$paramName])) {
          throw new apiException("($name) missing required param: '$paramName'");
        }
        if (isset($parameters[$paramName])) {
          $value = $parameters[$paramName];
          // check to see if the param value matches the required pattern
          if (isset($parameters[$paramName]['pattern']) && ! empty($parameters[$paramName]['pattern'])) {
            if (preg_match('|' . $parameters[$paramName]['pattern'] . '|', $value) == 0) {
              throw new apiException("($name) invalid parameter format for $paramName: $value doesn't match \"{$parameters[$paramName]['pattern']}\"");
            }
          }
          $parameters[$paramName] = $paramSpec;
          $parameters[$paramName]['value'] = $value;
          // remove all the bits that were already validated in this function & are no longer relevant within the execution chain
          unset($parameters[$paramName]['pattern']);
          unset($parameters[$paramName]['required']);
        } else {
          unset($parameters[$paramName]);
        }
      }
    }

    // Discovery v1.0 puts the canonical method id under the 'id' field.
    if (! isset($method['id'])) {
      $method['id'] = $method['rpcMethod'];
    }

    // Discovery v1.0 puts the canonical path under the 'path' field.
    if (! isset($method['path'])) {
      $method['path'] = $method['restPath'];
    }

    $request = new apiServiceRequest(
        $this->service->getIo(), $this->service->getRestBasePath(), $this->service->getRpcPath(),
        $method['path'], $method['id'], $method['httpMethod'], $parameters, $postBody);
    if ($batchKey) {
      $request->setBatchKey($batchKey);
      return $request;
    } else {
      return apiREST::execute($request);
    }
  }

  protected function useObjects() {
    global $apiConfig;
    return (isset($apiConfig['use_objects']) && $apiConfig['use_objects']);
  }

  protected function stripNull(&$o) {
    $o = (array) $o;
    foreach ($o as $k => $v) {
      if ($v === null) {
        unset($o[$k]);
      }
      elseif (is_object($v) || is_array($v)) {
        $this->stripNull($o[$k]);
      }
    }
  }
}
