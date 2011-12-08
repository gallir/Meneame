<?php
/*
 * Copyright (c) 2010 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

require_once 'service/apiModel.php';
require_once 'service/apiService.php';
require_once 'service/apiServiceRequest.php';


  /**
   * The "activities" collection of methods.
   * Typical usage is:
   *  <code>
   *   $orkutService = new apiOrkutService(...);
   *   $activities = $orkutService->activities;
   *  </code>
   */
  class ActivitiesServiceResource extends apiServiceResource {


    /**
     * Retrieves a list of activities. (activities.list)
     *
     * @param string $userId The ID of the user whose activities will be listed. Can be me to refer to the viewer (i.e. the authenticated user).
     * @param string $collection The collection of activities to list.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string pageToken A continuation token that allows pagination.
     * @opt_param string maxResults The maximum number of activities to include in the response.
     * @opt_param string hl Specifies the interface language (host language) of your user interface.
     * @return ActivityList
     */
    public function listActivities($userId, $collection, $optParams = array()) {
      $params = array('userId' => $userId, 'collection' => $collection);
      $params = array_merge($params, $optParams);
      $data = $this->__call('list', array($params));
      if ($this->useObjects()) {
        return new ActivityList($data);
      } else {
        return $data;
      }
    }
    /**
     * Deletes an existing activity, if the access controls allow it. (activities.delete)
     *
     * @param string $activityId ID of the activity to remove.
     */
    public function delete($activityId) {
      $params = array('activityId' => $activityId);
      $data = $this->__call('delete', array($params));
      return $data;
    }
  }

  /**
   * The "comments" collection of methods.
   * Typical usage is:
   *  <code>
   *   $orkutService = new apiOrkutService(...);
   *   $comments = $orkutService->comments;
   *  </code>
   */
  class CommentsServiceResource extends apiServiceResource {


    /**
     * Inserts a new comment to an activity. (comments.insert)
     *
     * @param string $activityId The ID of the activity to contain the new comment.
     * @param Comment $postBody
     * @return Comment
     */
    public function insert($activityId, Comment $postBody) {
      $params = array('activityId' => $activityId, 'postBody' => $postBody);
      $data = $this->__call('insert', array($params));
      if ($this->useObjects()) {
        return new Comment($data);
      } else {
        return $data;
      }
    }
    /**
     * Retrieves an existing comment. (comments.get)
     *
     * @param string $commentId ID of the comment to get.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Specifies the interface language (host language) of your user interface.
     * @return Comment
     */
    public function get($commentId, $optParams = array()) {
      $params = array('commentId' => $commentId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new Comment($data);
      } else {
        return $data;
      }
    }
    /**
     * Retrieves a list of comments, possibly filtered. (comments.list)
     *
     * @param string $activityId The ID of the activity containing the comments.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string orderBy Sort search results.
     * @opt_param string pageToken A continuation token that allows pagination.
     * @opt_param string maxResults The maximum number of activities to include in the response.
     * @opt_param string hl Specifies the interface language (host language) of your user interface.
     * @return CommentList
     */
    public function listComments($activityId, $optParams = array()) {
      $params = array('activityId' => $activityId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('list', array($params));
      if ($this->useObjects()) {
        return new CommentList($data);
      } else {
        return $data;
      }
    }
    /**
     * Deletes an existing comment. (comments.delete)
     *
     * @param string $commentId ID of the comment to remove.
     */
    public function delete($commentId) {
      $params = array('commentId' => $commentId);
      $data = $this->__call('delete', array($params));
      return $data;
    }
  }

  /**
   * The "acl" collection of methods.
   * Typical usage is:
   *  <code>
   *   $orkutService = new apiOrkutService(...);
   *   $acl = $orkutService->acl;
   *  </code>
   */
  class AclServiceResource extends apiServiceResource {


    /**
     * Excludes an element from the ACL of the activity. (acl.delete)
     *
     * @param string $activityId ID of the activity.
     * @param string $userId ID of the user to be removed from the activity.
     */
    public function delete($activityId, $userId) {
      $params = array('activityId' => $activityId, 'userId' => $userId);
      $data = $this->__call('delete', array($params));
      return $data;
    }
  }

  /**
   * The "scraps" collection of methods.
   * Typical usage is:
   *  <code>
   *   $orkutService = new apiOrkutService(...);
   *   $scraps = $orkutService->scraps;
   *  </code>
   */
  class ScrapsServiceResource extends apiServiceResource {


    /**
     * Creates a new scrap. (scraps.insert)
     *
     * @param Activity $postBody
     * @return Activity
     */
    public function insert(Activity $postBody) {
      $params = array('postBody' => $postBody);
      $data = $this->__call('insert', array($params));
      if ($this->useObjects()) {
        return new Activity($data);
      } else {
        return $data;
      }
    }
  }

  /**
   * The "activityVisibility" collection of methods.
   * Typical usage is:
   *  <code>
   *   $orkutService = new apiOrkutService(...);
   *   $activityVisibility = $orkutService->activityVisibility;
   *  </code>
   */
  class ActivityVisibilityServiceResource extends apiServiceResource {


    /**
     * Updates the visibility of an existing activity. This method supports patch semantics.
     * (activityVisibility.patch)
     *
     * @param string $activityId ID of the activity.
     * @param Visibility $postBody
     * @return Visibility
     */
    public function patch($activityId, Visibility $postBody) {
      $params = array('activityId' => $activityId, 'postBody' => $postBody);
      $data = $this->__call('patch', array($params));
      if ($this->useObjects()) {
        return new Visibility($data);
      } else {
        return $data;
      }
    }
    /**
     * Updates the visibility of an existing activity. (activityVisibility.update)
     *
     * @param string $activityId ID of the activity.
     * @param Visibility $postBody
     * @return Visibility
     */
    public function update($activityId, Visibility $postBody) {
      $params = array('activityId' => $activityId, 'postBody' => $postBody);
      $data = $this->__call('update', array($params));
      if ($this->useObjects()) {
        return new Visibility($data);
      } else {
        return $data;
      }
    }
    /**
     * Gets the visibility of an existing activity. (activityVisibility.get)
     *
     * @param string $activityId ID of the activity to get the visibility.
     * @return Visibility
     */
    public function get($activityId) {
      $params = array('activityId' => $activityId);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new Visibility($data);
      } else {
        return $data;
      }
    }
  }

  /**
   * The "badges" collection of methods.
   * Typical usage is:
   *  <code>
   *   $orkutService = new apiOrkutService(...);
   *   $badges = $orkutService->badges;
   *  </code>
   */
  class BadgesServiceResource extends apiServiceResource {


    /**
     * Retrieves the list of visible badges of a user. (badges.list)
     *
     * @param string $userId The id of the user whose badges will be listed. Can be me to refer to caller.
     * @return BadgeList
     */
    public function listBadges($userId) {
      $params = array('userId' => $userId);
      $data = $this->__call('list', array($params));
      if ($this->useObjects()) {
        return new BadgeList($data);
      } else {
        return $data;
      }
    }
    /**
     * Retrieves a badge from a user. (badges.get)
     *
     * @param string $userId The ID of the user whose badges will be listed. Can be me to refer to caller.
     * @param string $badgeId The ID of the badge that will be retrieved.
     * @return Badge
     */
    public function get($userId, $badgeId) {
      $params = array('userId' => $userId, 'badgeId' => $badgeId);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new Badge($data);
      } else {
        return $data;
      }
    }
  }

  /**
   * The "counters" collection of methods.
   * Typical usage is:
   *  <code>
   *   $orkutService = new apiOrkutService(...);
   *   $counters = $orkutService->counters;
   *  </code>
   */
  class CountersServiceResource extends apiServiceResource {


    /**
     * Retrieves the counters of an user. (counters.list)
     *
     * @param string $userId The ID of the user whose counters will be listed. Can be me to refer to caller.
     * @return Counters
     */
    public function listCounters($userId) {
      $params = array('userId' => $userId);
      $data = $this->__call('list', array($params));
      if ($this->useObjects()) {
        return new Counters($data);
      } else {
        return $data;
      }
    }
  }



/**
 * Service definition for Orkut (v2).
 *
 * <p>
 * Lets you manage activities, comments and badges in Orkut. More stuff coming in time.
 * </p>
 *
 * <p>
 * For more information about this service, see the
 * <a href="http://code.google.com/apis/orkut/v2/reference.html" target="_blank">API Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class apiOrkutService extends apiService {
  public $activities;
  public $comments;
  public $acl;
  public $scraps;
  public $activityVisibility;
  public $badges;
  public $counters;
  /**
   * Constructs the internal representation of the Orkut service.
   *
   * @param apiClient apiClient
   */
  public function __construct(apiClient $apiClient) {
    $this->rpcPath = '/rpc';
    $this->restBasePath = '/orkut/v2/';
    $this->version = 'v2';
    $this->serviceName = 'orkut';
    $this->io = $apiClient->getIo();

    $apiClient->addService($this->serviceName, $this->version);
    $this->activities = new ActivitiesServiceResource($this, $this->serviceName, 'activities', json_decode('{"methods": {"list": {"scopes": ["https://www.googleapis.com/auth/orkut", "https://www.googleapis.com/auth/orkut.readonly"], "parameters": {"collection": {"required": true, "enum": ["all", "scraps", "stream"], "location": "path", "type": "string"}, "pageToken": {"type": "string", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}, "maxResults": {"format": "uint32", "maximum": "100", "minimum": "1", "location": "query", "type": "integer"}}, "id": "orkut.activities.list", "httpMethod": "GET", "path": "people/{userId}/activities/{collection}", "response": {"$ref": "ActivityList"}}, "delete": {"scopes": ["https://www.googleapis.com/auth/orkut"], "parameters": {"activityId": {"required": true, "type": "string", "location": "path"}}, "httpMethod": "DELETE", "path": "activities/{activityId}", "id": "orkut.activities.delete"}}}', true));
    $this->comments = new CommentsServiceResource($this, $this->serviceName, 'comments', json_decode('{"methods": {"insert": {"scopes": ["https://www.googleapis.com/auth/orkut"], "parameters": {"activityId": {"required": true, "type": "string", "location": "path"}}, "request": {"$ref": "Comment"}, "id": "orkut.comments.insert", "httpMethod": "POST", "path": "activities/{activityId}/comments", "response": {"$ref": "Comment"}}, "delete": {"scopes": ["https://www.googleapis.com/auth/orkut"], "parameters": {"commentId": {"required": true, "type": "string", "location": "path"}}, "httpMethod": "DELETE", "path": "comments/{commentId}", "id": "orkut.comments.delete"}, "list": {"scopes": ["https://www.googleapis.com/auth/orkut", "https://www.googleapis.com/auth/orkut.readonly"], "parameters": {"orderBy": {"default": "DESCENDING_SORT", "enum": ["ascending", "descending"], "location": "query", "type": "string"}, "pageToken": {"type": "string", "location": "query"}, "activityId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}, "maxResults": {"format": "uint32", "minimum": "1", "type": "integer", "location": "query"}}, "id": "orkut.comments.list", "httpMethod": "GET", "path": "activities/{activityId}/comments", "response": {"$ref": "CommentList"}}, "get": {"scopes": ["https://www.googleapis.com/auth/orkut", "https://www.googleapis.com/auth/orkut.readonly"], "parameters": {"commentId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "id": "orkut.comments.get", "httpMethod": "GET", "path": "comments/{commentId}", "response": {"$ref": "Comment"}}}}', true));
    $this->acl = new AclServiceResource($this, $this->serviceName, 'acl', json_decode('{"methods": {"delete": {"scopes": ["https://www.googleapis.com/auth/orkut"], "parameters": {"activityId": {"required": true, "type": "string", "location": "path"}, "userId": {"required": true, "type": "string", "location": "path"}}, "httpMethod": "DELETE", "path": "activities/{activityId}/acl/{userId}", "id": "orkut.acl.delete"}}}', true));
    $this->scraps = new ScrapsServiceResource($this, $this->serviceName, 'scraps', json_decode('{"methods": {"insert": {"scopes": ["https://www.googleapis.com/auth/orkut"], "request": {"$ref": "Activity"}, "response": {"$ref": "Activity"}, "httpMethod": "POST", "path": "activities/scraps", "id": "orkut.scraps.insert"}}}', true));
    $this->activityVisibility = new ActivityVisibilityServiceResource($this, $this->serviceName, 'activityVisibility', json_decode('{"methods": {"get": {"scopes": ["https://www.googleapis.com/auth/orkut", "https://www.googleapis.com/auth/orkut.readonly"], "parameters": {"activityId": {"required": true, "type": "string", "location": "path"}}, "id": "orkut.activityVisibility.get", "httpMethod": "GET", "path": "activities/{activityId}/visibility", "response": {"$ref": "Visibility"}}, "update": {"scopes": ["https://www.googleapis.com/auth/orkut"], "parameters": {"activityId": {"required": true, "type": "string", "location": "path"}}, "request": {"$ref": "Visibility"}, "id": "orkut.activityVisibility.update", "httpMethod": "PUT", "path": "activities/{activityId}/visibility", "response": {"$ref": "Visibility"}}, "patch": {"scopes": ["https://www.googleapis.com/auth/orkut"], "parameters": {"activityId": {"required": true, "type": "string", "location": "path"}}, "request": {"$ref": "Visibility"}, "id": "orkut.activityVisibility.patch", "httpMethod": "PATCH", "path": "activities/{activityId}/visibility", "response": {"$ref": "Visibility"}}}}', true));
    $this->badges = new BadgesServiceResource($this, $this->serviceName, 'badges', json_decode('{"methods": {"list": {"scopes": ["https://www.googleapis.com/auth/orkut", "https://www.googleapis.com/auth/orkut.readonly"], "parameters": {"userId": {"required": true, "type": "string", "location": "path"}}, "id": "orkut.badges.list", "httpMethod": "GET", "path": "people/{userId}/badges", "response": {"$ref": "BadgeList"}}, "get": {"scopes": ["https://www.googleapis.com/auth/orkut", "https://www.googleapis.com/auth/orkut.readonly"], "parameters": {"userId": {"required": true, "type": "string", "location": "path"}, "badgeId": {"format": "int64", "required": true, "type": "string", "location": "path"}}, "id": "orkut.badges.get", "httpMethod": "GET", "path": "people/{userId}/badges/{badgeId}", "response": {"$ref": "Badge"}}}}', true));
    $this->counters = new CountersServiceResource($this, $this->serviceName, 'counters', json_decode('{"methods": {"list": {"scopes": ["https://www.googleapis.com/auth/orkut", "https://www.googleapis.com/auth/orkut.readonly"], "parameters": {"userId": {"required": true, "type": "string", "location": "path"}}, "id": "orkut.counters.list", "httpMethod": "GET", "path": "people/{userId}/counters", "response": {"$ref": "Counters"}}}}', true));
  }
}

class Acl extends apiModel {
  protected $__itemsType = 'AclItems';
  protected $__itemsDataType = 'array';
  public $items;
  public $kind;
  public $description;
  public $totalParticipants;
  public function setItems(/* array(AclItems) */ $items) {
    $this->assertIsArray($items, 'AclItems', __METHOD__);
    $this->items = $items;
  }
  public function getItems() {
    return $this->items;
  }
  public function setKind($kind) {
    $this->kind = $kind;
  }
  public function getKind() {
    return $this->kind;
  }
  public function setDescription($description) {
    $this->description = $description;
  }
  public function getDescription() {
    return $this->description;
  }
  public function setTotalParticipants($totalParticipants) {
    $this->totalParticipants = $totalParticipants;
  }
  public function getTotalParticipants() {
    return $this->totalParticipants;
  }
}

class AclItems extends apiModel {
  public $type;
  public $id;
  public function setType($type) {
    $this->type = $type;
  }
  public function getType() {
    return $this->type;
  }
  public function setId($id) {
    $this->id = $id;
  }
  public function getId() {
    return $this->id;
  }
}

class Activity extends apiModel {
  public $kind;
  protected $__linksType = 'OrkutLinkResource';
  protected $__linksDataType = 'array';
  public $links;
  public $title;
  protected $__objectType = 'ActivityObject';
  protected $__objectDataType = '';
  public $object;
  public $updated;
  protected $__actorType = 'OrkutAuthorResource';
  protected $__actorDataType = '';
  public $actor;
  protected $__accessType = 'Acl';
  protected $__accessDataType = '';
  public $access;
  public $verb;
  public $published;
  public $id;
  public function setKind($kind) {
    $this->kind = $kind;
  }
  public function getKind() {
    return $this->kind;
  }
  public function setLinks(/* array(OrkutLinkResource) */ $links) {
    $this->assertIsArray($links, 'OrkutLinkResource', __METHOD__);
    $this->links = $links;
  }
  public function getLinks() {
    return $this->links;
  }
  public function setTitle($title) {
    $this->title = $title;
  }
  public function getTitle() {
    return $this->title;
  }
  public function setObject(ActivityObject $object) {
    $this->object = $object;
  }
  public function getObject() {
    return $this->object;
  }
  public function setUpdated($updated) {
    $this->updated = $updated;
  }
  public function getUpdated() {
    return $this->updated;
  }
  public function setActor(OrkutAuthorResource $actor) {
    $this->actor = $actor;
  }
  public function getActor() {
    return $this->actor;
  }
  public function setAccess(Acl $access) {
    $this->access = $access;
  }
  public function getAccess() {
    return $this->access;
  }
  public function setVerb($verb) {
    $this->verb = $verb;
  }
  public function getVerb() {
    return $this->verb;
  }
  public function setPublished($published) {
    $this->published = $published;
  }
  public function getPublished() {
    return $this->published;
  }
  public function setId($id) {
    $this->id = $id;
  }
  public function getId() {
    return $this->id;
  }
}

class ActivityList extends apiModel {
  public $nextPageToken;
  protected $__itemsType = 'Activity';
  protected $__itemsDataType = 'array';
  public $items;
  public $kind;
  public function setNextPageToken($nextPageToken) {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken() {
    return $this->nextPageToken;
  }
  public function setItems(/* array(Activity) */ $items) {
    $this->assertIsArray($items, 'Activity', __METHOD__);
    $this->items = $items;
  }
  public function getItems() {
    return $this->items;
  }
  public function setKind($kind) {
    $this->kind = $kind;
  }
  public function getKind() {
    return $this->kind;
  }
}

class ActivityObject extends apiModel {
  public $content;
  protected $__itemsType = 'OrkutActivityobjectsResource';
  protected $__itemsDataType = 'array';
  public $items;
  protected $__repliesType = 'ActivityObjectReplies';
  protected $__repliesDataType = '';
  public $replies;
  public $objectType;
  public function setContent($content) {
    $this->content = $content;
  }
  public function getContent() {
    return $this->content;
  }
  public function setItems(/* array(OrkutActivityobjectsResource) */ $items) {
    $this->assertIsArray($items, 'OrkutActivityobjectsResource', __METHOD__);
    $this->items = $items;
  }
  public function getItems() {
    return $this->items;
  }
  public function setReplies(ActivityObjectReplies $replies) {
    $this->replies = $replies;
  }
  public function getReplies() {
    return $this->replies;
  }
  public function setObjectType($objectType) {
    $this->objectType = $objectType;
  }
  public function getObjectType() {
    return $this->objectType;
  }
}

class ActivityObjectReplies extends apiModel {
  public $totalItems;
  protected $__itemsType = 'Comment';
  protected $__itemsDataType = 'array';
  public $items;
  public $url;
  public function setTotalItems($totalItems) {
    $this->totalItems = $totalItems;
  }
  public function getTotalItems() {
    return $this->totalItems;
  }
  public function setItems(/* array(Comment) */ $items) {
    $this->assertIsArray($items, 'Comment', __METHOD__);
    $this->items = $items;
  }
  public function getItems() {
    return $this->items;
  }
  public function setUrl($url) {
    $this->url = $url;
  }
  public function getUrl() {
    return $this->url;
  }
}

class Badge extends apiModel {
  public $badgeSmallLogo;
  public $kind;
  public $description;
  public $sponsorLogo;
  public $sponsorName;
  public $badgeLargeLogo;
  public $caption;
  public $sponsorUrl;
  public $id;
  public function setBadgeSmallLogo($badgeSmallLogo) {
    $this->badgeSmallLogo = $badgeSmallLogo;
  }
  public function getBadgeSmallLogo() {
    return $this->badgeSmallLogo;
  }
  public function setKind($kind) {
    $this->kind = $kind;
  }
  public function getKind() {
    return $this->kind;
  }
  public function setDescription($description) {
    $this->description = $description;
  }
  public function getDescription() {
    return $this->description;
  }
  public function setSponsorLogo($sponsorLogo) {
    $this->sponsorLogo = $sponsorLogo;
  }
  public function getSponsorLogo() {
    return $this->sponsorLogo;
  }
  public function setSponsorName($sponsorName) {
    $this->sponsorName = $sponsorName;
  }
  public function getSponsorName() {
    return $this->sponsorName;
  }
  public function setBadgeLargeLogo($badgeLargeLogo) {
    $this->badgeLargeLogo = $badgeLargeLogo;
  }
  public function getBadgeLargeLogo() {
    return $this->badgeLargeLogo;
  }
  public function setCaption($caption) {
    $this->caption = $caption;
  }
  public function getCaption() {
    return $this->caption;
  }
  public function setSponsorUrl($sponsorUrl) {
    $this->sponsorUrl = $sponsorUrl;
  }
  public function getSponsorUrl() {
    return $this->sponsorUrl;
  }
  public function setId($id) {
    $this->id = $id;
  }
  public function getId() {
    return $this->id;
  }
}

class BadgeList extends apiModel {
  protected $__itemsType = 'Badge';
  protected $__itemsDataType = 'array';
  public $items;
  public $kind;
  public function setItems(/* array(Badge) */ $items) {
    $this->assertIsArray($items, 'Badge', __METHOD__);
    $this->items = $items;
  }
  public function getItems() {
    return $this->items;
  }
  public function setKind($kind) {
    $this->kind = $kind;
  }
  public function getKind() {
    return $this->kind;
  }
}

class Comment extends apiModel {
  protected $__inReplyToType = 'CommentInReplyTo';
  protected $__inReplyToDataType = '';
  public $inReplyTo;
  public $kind;
  protected $__linksType = 'OrkutLinkResource';
  protected $__linksDataType = 'array';
  public $links;
  protected $__actorType = 'OrkutAuthorResource';
  protected $__actorDataType = '';
  public $actor;
  public $content;
  public $published;
  public $id;
  public function setInReplyTo(CommentInReplyTo $inReplyTo) {
    $this->inReplyTo = $inReplyTo;
  }
  public function getInReplyTo() {
    return $this->inReplyTo;
  }
  public function setKind($kind) {
    $this->kind = $kind;
  }
  public function getKind() {
    return $this->kind;
  }
  public function setLinks(/* array(OrkutLinkResource) */ $links) {
    $this->assertIsArray($links, 'OrkutLinkResource', __METHOD__);
    $this->links = $links;
  }
  public function getLinks() {
    return $this->links;
  }
  public function setActor(OrkutAuthorResource $actor) {
    $this->actor = $actor;
  }
  public function getActor() {
    return $this->actor;
  }
  public function setContent($content) {
    $this->content = $content;
  }
  public function getContent() {
    return $this->content;
  }
  public function setPublished($published) {
    $this->published = $published;
  }
  public function getPublished() {
    return $this->published;
  }
  public function setId($id) {
    $this->id = $id;
  }
  public function getId() {
    return $this->id;
  }
}

class CommentInReplyTo extends apiModel {
  public $type;
  public $href;
  public $ref;
  public $rel;
  public function setType($type) {
    $this->type = $type;
  }
  public function getType() {
    return $this->type;
  }
  public function setHref($href) {
    $this->href = $href;
  }
  public function getHref() {
    return $this->href;
  }
  public function setRef($ref) {
    $this->ref = $ref;
  }
  public function getRef() {
    return $this->ref;
  }
  public function setRel($rel) {
    $this->rel = $rel;
  }
  public function getRel() {
    return $this->rel;
  }
}

class CommentList extends apiModel {
  public $nextPageToken;
  protected $__itemsType = 'Comment';
  protected $__itemsDataType = 'array';
  public $items;
  public $kind;
  public $previousPageToken;
  public function setNextPageToken($nextPageToken) {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken() {
    return $this->nextPageToken;
  }
  public function setItems(/* array(Comment) */ $items) {
    $this->assertIsArray($items, 'Comment', __METHOD__);
    $this->items = $items;
  }
  public function getItems() {
    return $this->items;
  }
  public function setKind($kind) {
    $this->kind = $kind;
  }
  public function getKind() {
    return $this->kind;
  }
  public function setPreviousPageToken($previousPageToken) {
    $this->previousPageToken = $previousPageToken;
  }
  public function getPreviousPageToken() {
    return $this->previousPageToken;
  }
}

class Counters extends apiModel {
  protected $__itemsType = 'OrkutCounterResource';
  protected $__itemsDataType = 'array';
  public $items;
  public $kind;
  public function setItems(/* array(OrkutCounterResource) */ $items) {
    $this->assertIsArray($items, 'OrkutCounterResource', __METHOD__);
    $this->items = $items;
  }
  public function getItems() {
    return $this->items;
  }
  public function setKind($kind) {
    $this->kind = $kind;
  }
  public function getKind() {
    return $this->kind;
  }
}

class OrkutActivityobjectsResource extends apiModel {
  public $displayName;
  protected $__linksType = 'OrkutLinkResource';
  protected $__linksDataType = 'array';
  public $links;
  public $content;
  protected $__personType = 'OrkutActivitypersonResource';
  protected $__personDataType = '';
  public $person;
  public $id;
  public $objectType;
  public function setDisplayName($displayName) {
    $this->displayName = $displayName;
  }
  public function getDisplayName() {
    return $this->displayName;
  }
  public function setLinks(/* array(OrkutLinkResource) */ $links) {
    $this->assertIsArray($links, 'OrkutLinkResource', __METHOD__);
    $this->links = $links;
  }
  public function getLinks() {
    return $this->links;
  }
  public function setContent($content) {
    $this->content = $content;
  }
  public function getContent() {
    return $this->content;
  }
  public function setPerson(OrkutActivitypersonResource $person) {
    $this->person = $person;
  }
  public function getPerson() {
    return $this->person;
  }
  public function setId($id) {
    $this->id = $id;
  }
  public function getId() {
    return $this->id;
  }
  public function setObjectType($objectType) {
    $this->objectType = $objectType;
  }
  public function getObjectType() {
    return $this->objectType;
  }
}

class OrkutActivitypersonResource extends apiModel {
  protected $__nameType = 'OrkutActivitypersonResourceName';
  protected $__nameDataType = '';
  public $name;
  public $url;
  public $gender;
  protected $__imageType = 'OrkutActivitypersonResourceImage';
  protected $__imageDataType = '';
  public $image;
  public $birthday;
  public $id;
  public function setName(OrkutActivitypersonResourceName $name) {
    $this->name = $name;
  }
  public function getName() {
    return $this->name;
  }
  public function setUrl($url) {
    $this->url = $url;
  }
  public function getUrl() {
    return $this->url;
  }
  public function setGender($gender) {
    $this->gender = $gender;
  }
  public function getGender() {
    return $this->gender;
  }
  public function setImage(OrkutActivitypersonResourceImage $image) {
    $this->image = $image;
  }
  public function getImage() {
    return $this->image;
  }
  public function setBirthday($birthday) {
    $this->birthday = $birthday;
  }
  public function getBirthday() {
    return $this->birthday;
  }
  public function setId($id) {
    $this->id = $id;
  }
  public function getId() {
    return $this->id;
  }
}

class OrkutActivitypersonResourceImage extends apiModel {
  public $url;
  public function setUrl($url) {
    $this->url = $url;
  }
  public function getUrl() {
    return $this->url;
  }
}

class OrkutActivitypersonResourceName extends apiModel {
  public $givenName;
  public $familyName;
  public function setGivenName($givenName) {
    $this->givenName = $givenName;
  }
  public function getGivenName() {
    return $this->givenName;
  }
  public function setFamilyName($familyName) {
    $this->familyName = $familyName;
  }
  public function getFamilyName() {
    return $this->familyName;
  }
}

class OrkutAuthorResource extends apiModel {
  public $url;
  protected $__imageType = 'OrkutAuthorResourceImage';
  protected $__imageDataType = '';
  public $image;
  public $displayName;
  public $id;
  public function setUrl($url) {
    $this->url = $url;
  }
  public function getUrl() {
    return $this->url;
  }
  public function setImage(OrkutAuthorResourceImage $image) {
    $this->image = $image;
  }
  public function getImage() {
    return $this->image;
  }
  public function setDisplayName($displayName) {
    $this->displayName = $displayName;
  }
  public function getDisplayName() {
    return $this->displayName;
  }
  public function setId($id) {
    $this->id = $id;
  }
  public function getId() {
    return $this->id;
  }
}

class OrkutAuthorResourceImage extends apiModel {
  public $url;
  public function setUrl($url) {
    $this->url = $url;
  }
  public function getUrl() {
    return $this->url;
  }
}

class OrkutCounterResource extends apiModel {
  public $total;
  protected $__linkType = 'OrkutLinkResource';
  protected $__linkDataType = '';
  public $link;
  public $name;
  public function setTotal($total) {
    $this->total = $total;
  }
  public function getTotal() {
    return $this->total;
  }
  public function setLink(OrkutLinkResource $link) {
    $this->link = $link;
  }
  public function getLink() {
    return $this->link;
  }
  public function setName($name) {
    $this->name = $name;
  }
  public function getName() {
    return $this->name;
  }
}

class OrkutLinkResource extends apiModel {
  public $href;
  public $type;
  public $rel;
  public $title;
  public function setHref($href) {
    $this->href = $href;
  }
  public function getHref() {
    return $this->href;
  }
  public function setType($type) {
    $this->type = $type;
  }
  public function getType() {
    return $this->type;
  }
  public function setRel($rel) {
    $this->rel = $rel;
  }
  public function getRel() {
    return $this->rel;
  }
  public function setTitle($title) {
    $this->title = $title;
  }
  public function getTitle() {
    return $this->title;
  }
}

class Visibility extends apiModel {
  public $kind;
  public $visibility;
  protected $__linksType = 'OrkutLinkResource';
  protected $__linksDataType = 'array';
  public $links;
  public function setKind($kind) {
    $this->kind = $kind;
  }
  public function getKind() {
    return $this->kind;
  }
  public function setVisibility($visibility) {
    $this->visibility = $visibility;
  }
  public function getVisibility() {
    return $this->visibility;
  }
  public function setLinks(/* array(OrkutLinkResource) */ $links) {
    $this->assertIsArray($links, 'OrkutLinkResource', __METHOD__);
    $this->links = $links;
  }
  public function getLinks() {
    return $this->links;
  }
}
