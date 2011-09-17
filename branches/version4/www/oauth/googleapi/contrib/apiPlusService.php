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
   *   $plusService = new apiPlusService(...);
   *   $activities = $plusService->activities;
   *  </code>
   */
  class ActivitiesServiceResource extends apiServiceResource {


    /**
     * List all of the activities in the specified collection for a particular user. (activities.list)
     *
     * @param string $userId The ID of the user to get activities for. The special value "me" can be used to indicate the authenticated user.
     * @param string $collection The collection of activities to list.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string pageToken The continuation token, used to page through large result sets. To get the next page of results, set this parameter to the value of "nextPageToken" from the previous response.
     * @opt_param string maxResults The maximum number of activities to include in the response, used for paging.
     * @return ActivityFeed
     */
    public function listActivities($userId, $collection, $optParams = array()) {
      $params = array('userId' => $userId, 'collection' => $collection);
      $params = array_merge($params, $optParams);
      $data = $this->__call('list', array($params));
      if ($this->useObjects()) {
        return new ActivityFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Get an activity. (activities.get)
     *
     * @param string $activityId The ID of the activity to get.
     * @return Activity
     */
    public function get($activityId) {
      $params = array('activityId' => $activityId);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new Activity($data);
      } else {
        return $data;
      }
    }
  }

  /**
   * The "people" collection of methods.
   * Typical usage is:
   *  <code>
   *   $plusService = new apiPlusService(...);
   *   $people = $plusService->people;
   *  </code>
   */
  class PeopleServiceResource extends apiServiceResource {


    /**
     * Get a person's profile. (people.get)
     *
     * @param string $userId The ID of the person to get the profile for. The special value "me" can be used to indicate the authenticated user.
     * @return Person
     */
    public function get($userId) {
      $params = array('userId' => $userId);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new Person($data);
      } else {
        return $data;
      }
    }
  }



/**
 * Service definition for Plus (v1).
 *
 * <p>
 * Google+ API
 * </p>
 *
 * <p>
 * For more information about this service, see the
 * <a href="" target="_blank">API Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class apiPlusService extends apiService {
  public $activities;
  public $people;
  /**
   * Constructs the internal representation of the Plus service.
   *
   * @param apiClient apiClient
   */
  public function __construct(apiClient $apiClient) {
    $this->rpcPath = '/rpc';
    $this->restBasePath = '/plus/v1/';
    $this->version = 'v1';
    $this->serviceName = 'plus';
    $this->io = $apiClient->getIo();

    $apiClient->addService($this->serviceName, $this->version);
    $this->activities = new ActivitiesServiceResource($this, $this->serviceName, 'activities', json_decode('{"methods": {"list": {"scopes": ["https://www.googleapis.com/auth/plus.me"], "parameters": {"pageToken": {"type": "string", "location": "query"}, "alt": {"default": "json", "enum": ["json"], "location": "query", "type": "string"}, "userId": {"pattern": "me|[0-9]+", "required": true, "type": "string", "location": "path"}, "collection": {"required": true, "enum": ["public"], "location": "path", "type": "string"}, "maxResults": {"format": "uint32", "default": "20", "maximum": "100", "minimum": "1", "location": "query", "type": "integer"}}, "id": "plus.activities.list", "httpMethod": "GET", "path": "people/{userId}/activities/{collection}", "response": {"$ref": "ActivityFeed"}}, "get": {"scopes": ["https://www.googleapis.com/auth/plus.me"], "parameters": {"activityId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "json", "enum": ["json"], "location": "query", "type": "string"}}, "id": "plus.activities.get", "httpMethod": "GET", "path": "activities/{activityId}", "response": {"$ref": "Activity"}}}}', true));
    $this->people = new PeopleServiceResource($this, $this->serviceName, 'people', json_decode('{"methods": {"get": {"scopes": ["https://www.googleapis.com/auth/plus.me"], "parameters": {"userId": {"pattern": "me|[0-9]+", "required": true, "type": "string", "location": "path"}}, "id": "plus.people.get", "httpMethod": "GET", "path": "people/{userId}", "response": {"$ref": "Person"}}}}', true));
  }
}

class ActivityFeed extends apiModel {

  public $nextPageToken;
  public $kind;
  public $title;
  public $items;
  public $updated;
  public $nextLink;
  public $id;
  public $selfLink;

  public function setNextPageToken($nextPageToken) {
    $this->nextPageToken = $nextPageToken;
  }

  public function getNextPageToken() {
    return $this->nextPageToken;
  }
  
  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setItems(Activity $items) {
    $this->items = $items;
  }

  public function getItems() {
    return $this->items;
  }
  
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getUpdated() {
    return $this->updated;
  }
  
  public function setNextLink($nextLink) {
    $this->nextLink = $nextLink;
  }

  public function getNextLink() {
    return $this->nextLink;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setSelfLink($selfLink) {
    $this->selfLink = $selfLink;
  }

  public function getSelfLink() {
    return $this->selfLink;
  }
  
}


class PersonUrls extends apiModel {

  public $type;
  public $primary;
  public $value;

  public function setType($type) {
    $this->type = $type;
  }

  public function getType() {
    return $this->type;
  }
  
  public function setPrimary($primary) {
    $this->primary = $primary;
  }

  public function getPrimary() {
    return $this->primary;
  }
  
  public function setValue($value) {
    $this->value = $value;
  }

  public function getValue() {
    return $this->value;
  }
  
}


class ActivityObjectResharers extends apiModel {

  public $totalItems;

  public function setTotalItems($totalItems) {
    $this->totalItems = $totalItems;
  }

  public function getTotalItems() {
    return $this->totalItems;
  }
  
}


class PersonOrganizations extends apiModel {

  public $startDate;
  public $endDate;
  public $description;
  public $title;
  public $primary;
  public $location;
  public $department;
  public $type;
  public $name;

  public function setStartDate($startDate) {
    $this->startDate = $startDate;
  }

  public function getStartDate() {
    return $this->startDate;
  }
  
  public function setEndDate($endDate) {
    $this->endDate = $endDate;
  }

  public function getEndDate() {
    return $this->endDate;
  }
  
  public function setDescription($description) {
    $this->description = $description;
  }

  public function getDescription() {
    return $this->description;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setPrimary($primary) {
    $this->primary = $primary;
  }

  public function getPrimary() {
    return $this->primary;
  }
  
  public function setLocation($location) {
    $this->location = $location;
  }

  public function getLocation() {
    return $this->location;
  }
  
  public function setDepartment($department) {
    $this->department = $department;
  }

  public function getDepartment() {
    return $this->department;
  }
  
  public function setType($type) {
    $this->type = $type;
  }

  public function getType() {
    return $this->type;
  }
  
  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }
  
}


class Activity extends apiModel {

  public $placeName;
  public $kind;
  public $updated;
  public $provider;
  public $title;
  public $url;
  public $object;
  public $placeId;
  public $actor;
  public $id;
  public $access;
  public $verb;
  public $geocode;
  public $radius;
  public $address;
  public $crosspostSource;
  public $placeholder;
  public $annotation;
  public $published;

  public function setPlaceName($placeName) {
    $this->placeName = $placeName;
  }

  public function getPlaceName() {
    return $this->placeName;
  }
  
  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getUpdated() {
    return $this->updated;
  }
  
  public function setProvider(ActivityProvider $provider) {
    $this->provider = $provider;
  }

  public function getProvider() {
    return $this->provider;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }
  
  public function setObject(ActivityObject $object) {
    $this->object = $object;
  }

  public function getObject() {
    return $this->object;
  }
  
  public function setPlaceId($placeId) {
    $this->placeId = $placeId;
  }

  public function getPlaceId() {
    return $this->placeId;
  }
  
  public function setActor(ActivityActor $actor) {
    $this->actor = $actor;
  }

  public function getActor() {
    return $this->actor;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
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
  
  public function setGeocode($geocode) {
    $this->geocode = $geocode;
  }

  public function getGeocode() {
    return $this->geocode;
  }
  
  public function setRadius($radius) {
    $this->radius = $radius;
  }

  public function getRadius() {
    return $this->radius;
  }
  
  public function setAddress($address) {
    $this->address = $address;
  }

  public function getAddress() {
    return $this->address;
  }
  
  public function setCrosspostSource($crosspostSource) {
    $this->crosspostSource = $crosspostSource;
  }

  public function getCrosspostSource() {
    return $this->crosspostSource;
  }
  
  public function setPlaceholder($placeholder) {
    $this->placeholder = $placeholder;
  }

  public function getPlaceholder() {
    return $this->placeholder;
  }
  
  public function setAnnotation($annotation) {
    $this->annotation = $annotation;
  }

  public function getAnnotation() {
    return $this->annotation;
  }
  
  public function setPublished($published) {
    $this->published = $published;
  }

  public function getPublished() {
    return $this->published;
  }
  
}


class PlusAclentryResource extends apiModel {

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


class PersonPlacesLived extends apiModel {

  public $primary;
  public $value;

  public function setPrimary($primary) {
    $this->primary = $primary;
  }

  public function getPrimary() {
    return $this->primary;
  }
  
  public function setValue($value) {
    $this->value = $value;
  }

  public function getValue() {
    return $this->value;
  }
  
}


class ActivityObjectAttachmentsImage extends apiModel {

  public $url;
  public $width;
  public $type;
  public $height;

  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }
  
  public function setWidth($width) {
    $this->width = $width;
  }

  public function getWidth() {
    return $this->width;
  }
  
  public function setType($type) {
    $this->type = $type;
  }

  public function getType() {
    return $this->type;
  }
  
  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }
  
}


class ActivityActor extends apiModel {

  public $url;
  public $image;
  public $displayName;
  public $id;

  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }
  
  public function setImage(ActivityActorImage $image) {
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


class ActivityProvider extends apiModel {

  public $title;

  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
}


class ActivityObjectPlusoners extends apiModel {

  public $totalItems;

  public function setTotalItems($totalItems) {
    $this->totalItems = $totalItems;
  }

  public function getTotalItems() {
    return $this->totalItems;
  }
  
}


class PersonImage extends apiModel {

  public $url;

  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }
  
}


class ActivityObjectAttachments extends apiModel {

  public $displayName;
  public $fullImage;
  public $url;
  public $image;
  public $content;
  public $embed;
  public $id;
  public $objectType;

  public function setDisplayName($displayName) {
    $this->displayName = $displayName;
  }

  public function getDisplayName() {
    return $this->displayName;
  }
  
  public function setFullImage(ActivityObjectAttachmentsFullImage $fullImage) {
    $this->fullImage = $fullImage;
  }

  public function getFullImage() {
    return $this->fullImage;
  }
  
  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }
  
  public function setImage(ActivityObjectAttachmentsImage $image) {
    $this->image = $image;
  }

  public function getImage() {
    return $this->image;
  }
  
  public function setContent($content) {
    $this->content = $content;
  }

  public function getContent() {
    return $this->content;
  }
  
  public function setEmbed(ActivityObjectAttachmentsEmbed $embed) {
    $this->embed = $embed;
  }

  public function getEmbed() {
    return $this->embed;
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


class ActivityObjectAttachmentsFullImage extends apiModel {

  public $url;
  public $width;
  public $type;
  public $height;

  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }
  
  public function setWidth($width) {
    $this->width = $width;
  }

  public function getWidth() {
    return $this->width;
  }
  
  public function setType($type) {
    $this->type = $type;
  }

  public function getType() {
    return $this->type;
  }
  
  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }
  
}


class Acl extends apiModel {

  public $items;
  public $kind;
  public $description;

  public function setItems(PlusAclentryResource $items) {
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
  
}


class Person extends apiModel {

  public $relationshipStatus;
  public $organizations;
  public $kind;
  public $displayName;
  public $name;
  public $url;
  public $gender;
  public $aboutMe;
  public $tagline;
  public $urls;
  public $placesLived;
  public $id;
  public $nickname;
  public $birthday;
  public $image;
  public $hasApp;
  public $languagesSpoken;
  public $currentLocation;

  public function setRelationshipStatus($relationshipStatus) {
    $this->relationshipStatus = $relationshipStatus;
  }

  public function getRelationshipStatus() {
    return $this->relationshipStatus;
  }
  
  public function setOrganizations(PersonOrganizations $organizations) {
    $this->organizations = $organizations;
  }

  public function getOrganizations() {
    return $this->organizations;
  }
  
  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setDisplayName($displayName) {
    $this->displayName = $displayName;
  }

  public function getDisplayName() {
    return $this->displayName;
  }
  
  public function setName(PersonName $name) {
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
  
  public function setAboutMe($aboutMe) {
    $this->aboutMe = $aboutMe;
  }

  public function getAboutMe() {
    return $this->aboutMe;
  }
  
  public function setTagline($tagline) {
    $this->tagline = $tagline;
  }

  public function getTagline() {
    return $this->tagline;
  }
  
  public function setUrls(PersonUrls $urls) {
    $this->urls = $urls;
  }

  public function getUrls() {
    return $this->urls;
  }
  
  public function setPlacesLived(PersonPlacesLived $placesLived) {
    $this->placesLived = $placesLived;
  }

  public function getPlacesLived() {
    return $this->placesLived;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setNickname($nickname) {
    $this->nickname = $nickname;
  }

  public function getNickname() {
    return $this->nickname;
  }
  
  public function setBirthday($birthday) {
    $this->birthday = $birthday;
  }

  public function getBirthday() {
    return $this->birthday;
  }
  
  public function setImage(PersonImage $image) {
    $this->image = $image;
  }

  public function getImage() {
    return $this->image;
  }
  
  public function setHasApp($hasApp) {
    $this->hasApp = $hasApp;
  }

  public function getHasApp() {
    return $this->hasApp;
  }
  
  public function setLanguagesSpoken($languagesSpoken) {
    $this->languagesSpoken = $languagesSpoken;
  }

  public function getLanguagesSpoken() {
    return $this->languagesSpoken;
  }
  
  public function setCurrentLocation($currentLocation) {
    $this->currentLocation = $currentLocation;
  }

  public function getCurrentLocation() {
    return $this->currentLocation;
  }
  
}


class ActivityObjectActor extends apiModel {

  public $url;
  public $image;
  public $displayName;
  public $id;

  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }
  
  public function setImage(ActivityObjectActorImage $image) {
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


class ActivityObjectReplies extends apiModel {

  public $totalItems;

  public function setTotalItems($totalItems) {
    $this->totalItems = $totalItems;
  }

  public function getTotalItems() {
    return $this->totalItems;
  }
  
}


class ActivityActorImage extends apiModel {

  public $url;

  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }
  
}


class ActivityObjectActorImage extends apiModel {

  public $url;

  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }
  
}


class PersonName extends apiModel {

  public $honorificPrefix;
  public $middleName;
  public $familyName;
  public $formatted;
  public $givenName;
  public $honorificSuffix;

  public function setHonorificPrefix($honorificPrefix) {
    $this->honorificPrefix = $honorificPrefix;
  }

  public function getHonorificPrefix() {
    return $this->honorificPrefix;
  }
  
  public function setMiddleName($middleName) {
    $this->middleName = $middleName;
  }

  public function getMiddleName() {
    return $this->middleName;
  }
  
  public function setFamilyName($familyName) {
    $this->familyName = $familyName;
  }

  public function getFamilyName() {
    return $this->familyName;
  }
  
  public function setFormatted($formatted) {
    $this->formatted = $formatted;
  }

  public function getFormatted() {
    return $this->formatted;
  }
  
  public function setGivenName($givenName) {
    $this->givenName = $givenName;
  }

  public function getGivenName() {
    return $this->givenName;
  }
  
  public function setHonorificSuffix($honorificSuffix) {
    $this->honorificSuffix = $honorificSuffix;
  }

  public function getHonorificSuffix() {
    return $this->honorificSuffix;
  }
  
}


class ActivityObjectAttachmentsEmbed extends apiModel {

  public $url;
  public $type;

  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }
  
  public function setType($type) {
    $this->type = $type;
  }

  public function getType() {
    return $this->type;
  }
  
}


class ActivityObject extends apiModel {

  public $resharers;
  public $attachments;
  public $originalContent;
  public $plusoners;
  public $actor;
  public $content;
  public $url;
  public $replies;
  public $id;
  public $objectType;

  public function setResharers(ActivityObjectResharers $resharers) {
    $this->resharers = $resharers;
  }

  public function getResharers() {
    return $this->resharers;
  }
  
  public function setAttachments(ActivityObjectAttachments $attachments) {
    $this->attachments = $attachments;
  }

  public function getAttachments() {
    return $this->attachments;
  }
  
  public function setOriginalContent($originalContent) {
    $this->originalContent = $originalContent;
  }

  public function getOriginalContent() {
    return $this->originalContent;
  }
  
  public function setPlusoners(ActivityObjectPlusoners $plusoners) {
    $this->plusoners = $plusoners;
  }

  public function getPlusoners() {
    return $this->plusoners;
  }
  
  public function setActor(ActivityObjectActor $actor) {
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
  
  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }
  
  public function setReplies(ActivityObjectReplies $replies) {
    $this->replies = $replies;
  }

  public function getReplies() {
    return $this->replies;
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

