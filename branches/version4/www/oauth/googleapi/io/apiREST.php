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

require_once "external/URITemplateParser.php";

/**
 * This class implements the RESTful transport of apiServiceRequest()'s
 *
 * @author Chris Chabot <chabotc@google.com>
 */
class apiREST {

  /**
   * Executes a apiServiceRequest using a RESTful call by transforming it into a apiHttpRequest, execute it via apiIO::authenticatedRequest()
   * and returning the json decoded result
   *
   * @param apiServiceRequest $request
   * @return array decoded result
   * @throws apiServiceException on server side error (ie: not authenticated, invalid or malformed post body, invalid url, etc)
   */
  static public function execute(apiServiceRequest $request) {
    global $apiTypeHandlers;
    $result = null;
    $requestUrl = $request->getRestBasePath() . $request->getRestPath();
    $uriTemplateVars = array();
    $queryVars = array();
    foreach ($request->getParameters() as $paramName => $paramSpec) {
      // Discovery v1.0 puts the canonical location under the 'location' field.
      if (! isset($paramSpec['location'])) {
        $paramSpec['location'] = $paramSpec['restParameterType'];
      }
      
      if ($paramSpec['location'] == 'path') {
        $uriTemplateVars[$paramName] = $paramSpec['value'];
      } else {
        if ($paramSpec['type'] == 'boolean') {
          $paramSpec['value'] = ($paramSpec['value']) ? 'true' : 'false';
        }
        if (isset($paramSpec['repeated']) && is_array($paramSpec['value'])) {
          foreach ($paramSpec['value'] as $value) {
            $queryVars[] = $paramName . '=' . rawurlencode($value);
          }
        } else {
          $queryVars[] = $paramName . '=' . rawurlencode($paramSpec['value']);
        }
      }
    }
    $queryVars[] = 'alt=json';
    if (count($uriTemplateVars)) {
      $uriTemplateParser = new URI_Template_Parser($requestUrl);
      $requestUrl = $uriTemplateParser->expand($uriTemplateVars);
    }
    //FIXME work around for the the uri template lib which url encodes the @'s & confuses our servers
    $requestUrl = str_replace('%40', '@', $requestUrl);
    //EOFIX

    //FIXME temp work around to make @groups/{@following,@followers} work (something which we should really be fixing in our API)
    if (strpos($requestUrl, '/@groups') && (strpos($requestUrl, '/@following') || strpos($requestUrl, '/@followers'))) {
      $requestUrl = str_replace('/@self', '', $requestUrl);
    }
    //EOFIX

    if (count($queryVars)) {
      $requestUrl .= '?' . implode($queryVars, '&');
    }
    $httpRequest = new apiHttpRequest($requestUrl, $request->getHttpMethod(), null, $request->getPostBody());
    // Add a content-type: application/json header so the server knows how to interpret the post body
    if ($request->getPostBody()) {
      $contentTypeHeader = array('Content-Type: application/json; charset=UTF-8',
                                 'Content-Length: ' . self::getStrLen($request->getPostBody()));
      if ($httpRequest->getHeaders()) {
        $contentTypeHeader = array_merge($httpRequest->getHeaders(), $contentTypeHeader);
      }
      $httpRequest->setHeaders($contentTypeHeader);
    }
    $httpRequest = $request->getIo()->authenticatedRequest($httpRequest);
    if ($httpRequest->getResponseHttpCode() != '200' && $httpRequest->getResponseHttpCode() != '201' && $httpRequest->getResponseHttpCode() != '204') {
      $responseBody = $httpRequest->getResponseBody();
      if (($responseBody = json_decode($responseBody, true)) != null && isset($responseBody['error']['message']) && isset($responseBody['error']['code'])) {
        // if we're getting a json encoded error definition, use that instead of the raw response body for improved readability
        $errorMessage = "Error calling " . $httpRequest->getUrl() . ": ({$responseBody['error']['code']}) {$responseBody['error']['message']}";
      } else {
        $errorMessage = "Error calling " . $httpRequest->getMethod() . " " . $httpRequest->getUrl() . ": (" . $httpRequest->getResponseHttpCode() . ") " . $httpRequest->getResponseBody();
      }
      throw new apiServiceException($errorMessage);
    }
    $decodedResponse = null;
    if ($httpRequest->getResponseHttpCode() != '204') {
      // Only attempt to decode the response, if the response code wasn't (204) 'no content'
      if (($decodedResponse = json_decode($httpRequest->getResponseBody(), true)) == null) {
        throw new apiServiceException("Invalid json in service response: " . $httpRequest->getResponseBody());
      }
    }
    //FIXME currently everything is wrapped in a data envelope, but hopefully this might change some day
    $ret = isset($decodedResponse['data']) ? $decodedResponse['data'] : $decodedResponse;
    // Add a 'continuationToken' element to the response if the response contains a next link (so you can call it using the 'c' param)
    $ret = self::checkNextLink($ret);
    // if the response type has a registered type handler, call & return it instead of the raw response array
    if (isset($ret['kind']) && isset($apiTypeHandlers[$ret['kind']])) {
      $ret = new $apiTypeHandlers[$ret['kind']]($ret);
    }
    return $ret;
  }


  static private function checkNextLink($response) {
    if (isset($response['links']) && isset($response['links']['next'][0]['href'])) {
      parse_str($response['links']['next'][0]['href'], $params);
      if (isset($params['c'])) {
        $response['continuationToken'] = $params['c'];
      }
    }
    return $response;
  }


  /**
   * Misc function used to count the number of bytes in a post body, in the world of multi-byte chars
   * and the unpredictability of strlen/mb_strlen/sizeof, this is the only way to do that in a sane manner
   * at the moment.
   *
   * This algorithm was originally developed for the
   * Solar Framework by Paul M. Jones
   *
   * @link   http://solarphp.com/
   * @link   http://svn.solarphp.com/core/trunk/Solar/Json.php
   * @link   http://framework.zend.com/svn/framework/standard/trunk/library/Zend/Json/Decoder.php
   * @param  string $str
   * @return int The number of bytes in a string.
   */
  static private function getStrLen($str) {
    $strlenVar = strlen($str);
    $d = $ret = 0;
    for ($count = 0; $count < $strlenVar; ++ $count) {
      $ordinalValue = ord($str{$ret});
      switch (true) {
        case (($ordinalValue >= 0x20) && ($ordinalValue <= 0x7F)):
          // characters U-00000000 - U-0000007F (same as ASCII)
          $ret ++;
          break;

        case (($ordinalValue & 0xE0) == 0xC0):
          // characters U-00000080 - U-000007FF, mask 110XXXXX
          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
          $ret += 2;
          break;

        case (($ordinalValue & 0xF0) == 0xE0):
          // characters U-00000800 - U-0000FFFF, mask 1110XXXX
          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
          $ret += 3;
          break;

        case (($ordinalValue & 0xF8) == 0xF0):
          // characters U-00010000 - U-001FFFFF, mask 11110XXX
          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
          $ret += 4;
          break;

        case (($ordinalValue & 0xFC) == 0xF8):
          // characters U-00200000 - U-03FFFFFF, mask 111110XX
          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
          $ret += 5;
          break;

        case (($ordinalValue & 0xFE) == 0xFC):
          // characters U-04000000 - U-7FFFFFFF, mask 1111110X
          // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
          $ret += 6;
          break;
        default:
          $ret ++;
      }
    }
    return $ret;
  }

}
