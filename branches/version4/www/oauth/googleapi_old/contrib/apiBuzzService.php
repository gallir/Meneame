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
   *   $buzzService = new apiBuzzService(...);
   *   $activities = $buzzService->activities;
   *  </code>
   */
  class ActivitiesServiceResource extends apiServiceResource {


    /**
     * Get a count of link shares (activities.count)
     *
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string url URLs for which to get share counts.
     * @opt_param string hl Language code to limit language results.
     * @return CountFeed
     */
    public function count($optParams = array()) {
      $params = array();
      $params = array_merge($params, $optParams);
      $data = $this->__call('count', array($params));
      if ($this->useObjects()) {
        return new CountFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Create a new activity (activities.insert)
     *
     * @param string $userId ID of the user being referenced.
     * @param $postBody the {@link Activity}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param bool preview If true, only preview the action.
     * @opt_param string hl Language code to limit language results.
     * @return Activity
     */
    public function insert($userId, Activity $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('insert', array($params));
      if ($this->useObjects()) {
        return new Activity($data);
      } else {
        return $data;
      }
    }
    /**
     * Search for activities (activities.search)
     *
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string pid ID of a place to use in a geographic location query.
     * @opt_param string lon Longitude to use in a geographic location query.
     * @opt_param string q Full-text search query string.
     * @opt_param bool truncateAtom Truncate the value of the atom:content element.
     * @opt_param string radius Radius to use in a geographic location query.
     * @opt_param string bbox Bounding box to use in a geographic location query.
     * @opt_param string hl Language code to limit language results.
     * @opt_param string lat Latitude to use in a geographic location query.
     * @return ActivityFeed
     */
    public function search($optParams = array()) {
      $params = array();
      $params = array_merge($params, $optParams);
      $data = $this->__call('search', array($params));
      if ($this->useObjects()) {
        return new ActivityFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Get an activity (activities.get)
     *
     * @param string $userId ID of the user whose post to get.
     * @param string $postId ID of the post to get.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param bool truncateAtom Truncate the value of the atom:content element.
     * @opt_param string max-comments Maximum number of comments to include.
     * @opt_param string hl Language code to limit language results.
     * @opt_param string max-liked Maximum number of likes to include.
     * @return Activity
     */
    public function get($userId, $postId, $optParams = array()) {
      $params = array('userId' => $userId, 'postId' => $postId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new Activity($data);
      } else {
        return $data;
      }
    }
    /**
     * Get real-time activity tracking information (activities.track)
     *
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string pid ID of a place to use in a geographic location query.
     * @opt_param string lon Longitude to use in a geographic location query.
     * @opt_param string q Full-text search query string.
     * @opt_param string radius Radius to use in a geographic location query.
     * @opt_param string bbox Bounding box to use in a geographic location query.
     * @opt_param string hl Language code to limit language results.
     * @opt_param string lat Latitude to use in a geographic location query.
     * @return ActivityFeed
     */
    public function track($optParams = array()) {
      $params = array();
      $params = array_merge($params, $optParams);
      $data = $this->__call('track', array($params));
      if ($this->useObjects()) {
        return new ActivityFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * List activities (activities.list)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $scope The collection of activities to list.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param bool truncateAtom Truncate the value of the atom:content element.
     * @opt_param string max-comments Maximum number of comments to include.
     * @opt_param string hl Language code to limit language results.
     * @opt_param string max-liked Maximum number of likes to include.
     * @return ActivityFeed
     */
    public function listActivities($userId, $scope, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope);
      $params = array_merge($params, $optParams);
      $data = $this->__call('list', array($params));
      if ($this->useObjects()) {
        return new ActivityFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Update an activity (activities.update)
     *
     * @param string $userId ID of the user whose post to update.
     * @param string $scope The collection to which the activity belongs.
     * @param string $postId ID of the activity to update.
     * @param $postBody the {@link Activity}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string abuseType
     * @opt_param string hl Language code to limit language results.
     * @return Activity
     */
    public function update($userId, $scope, $postId, Activity $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope, 'postId' => $postId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('update', array($params));
      if ($this->useObjects()) {
        return new Activity($data);
      } else {
        return $data;
      }
    }
    /**
     * Update an activity. This method supports patch semantics. (activities.patch)
     *
     * @param string $userId ID of the user whose post to update.
     * @param string $scope The collection to which the activity belongs.
     * @param string $postId ID of the activity to update.
     * @param $postBody the {@link Activity}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string abuseType
     * @opt_param string hl Language code to limit language results.
     * @return Activity
     */
    public function patch($userId, $scope, $postId, Activity $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope, 'postId' => $postId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('patch', array($params));
      if ($this->useObjects()) {
        return new Activity($data);
      } else {
        return $data;
      }
    }
    /**
     * Search for people by topic (activities.extractPeopleFromSearch)
     *
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string pid ID of a place to use in a geographic location query.
     * @opt_param string lon Longitude to use in a geographic location query.
     * @opt_param string q Full-text search query string.
     * @opt_param string radius Radius to use in a geographic location query.
     * @opt_param string bbox Bounding box to use in a geographic location query.
     * @opt_param string hl Language code to limit language results.
     * @opt_param string lat Latitude to use in a geographic location query.
     * @return PeopleFeed
     */
    public function extractPeopleFromSearch($optParams = array()) {
      $params = array();
      $params = array_merge($params, $optParams);
      $data = $this->__call('extractPeopleFromSearch', array($params));
      if ($this->useObjects()) {
        return new PeopleFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Delete an activity (activities.delete)
     *
     * @param string $userId ID of the user whose post to delete.
     * @param string $scope The collection to which the activity belongs.
     * @param string $postId ID of the activity to delete.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     */
    public function delete($userId, $scope, $postId, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope, 'postId' => $postId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('delete', array($params));
      return $data;
    }
  }

  /**
   * The "people" collection of methods.
   * Typical usage is:
   *  <code>
   *   $buzzService = new apiBuzzService(...);
   *   $people = $buzzService->people;
   *  </code>
   */
  class PeopleServiceResource extends apiServiceResource {


    /**
     * Get people who liked an activity (people.liked)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $scope
     * @param string $postId ID of the activity that was liked.
     * @param string $groupId
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string hl Language code to limit language results.
     * @return PeopleFeed
     */
    public function liked($userId, $scope, $postId, $groupId, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope, 'postId' => $postId, 'groupId' => $groupId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('liked', array($params));
      if ($this->useObjects()) {
        return new PeopleFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Get a user profile (people.get)
     *
     * @param string $userId ID of the user being referenced.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return Person
     */
    public function get($userId, $optParams = array()) {
      $params = array('userId' => $userId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new Person($data);
      } else {
        return $data;
      }
    }
    /**
     * Add a person to a group (people.update)
     *
     * @param string $userId ID of the owner of the group.
     * @param string $groupId ID of the group to which to add the person.
     * @param string $personId ID of the person to add to the group.
     * @param $postBody the {@link Person}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return Person
     */
    public function update($userId, $groupId, $personId, Person $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'groupId' => $groupId, 'personId' => $personId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('update', array($params));
      if ($this->useObjects()) {
        return new Person($data);
      } else {
        return $data;
      }
    }
    /**
     * Get people in a group (people.list)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $groupId ID of the group for which to list users.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string hl Language code to limit language results.
     * @return PeopleFeed
     */
    public function listPeople($userId, $groupId, $optParams = array()) {
      $params = array('userId' => $userId, 'groupId' => $groupId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('list', array($params));
      if ($this->useObjects()) {
        return new PeopleFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Search for people (people.search)
     *
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string q Full-text search query string.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string hl Language code to limit language results.
     * @return PeopleFeed
     */
    public function search($optParams = array()) {
      $params = array();
      $params = array_merge($params, $optParams);
      $data = $this->__call('search', array($params));
      if ($this->useObjects()) {
        return new PeopleFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Add a person to a group. This method supports patch semantics. (people.patch)
     *
     * @param string $userId ID of the owner of the group.
     * @param string $groupId ID of the group to which to add the person.
     * @param string $personId ID of the person to add to the group.
     * @param $postBody the {@link Person}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return Person
     */
    public function patch($userId, $groupId, $personId, Person $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'groupId' => $groupId, 'personId' => $personId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('patch', array($params));
      if ($this->useObjects()) {
        return new Person($data);
      } else {
        return $data;
      }
    }
    /**
     * Get people who reshared an activity (people.reshared)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $scope
     * @param string $postId ID of the activity that was reshared.
     * @param string $groupId
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string hl Language code to limit language results.
     * @return PeopleFeed
     */
    public function reshared($userId, $scope, $postId, $groupId, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope, 'postId' => $postId, 'groupId' => $groupId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('reshared', array($params));
      if ($this->useObjects()) {
        return new PeopleFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Remove a person from a group (people.delete)
     *
     * @param string $userId ID of the owner of the group.
     * @param string $groupId ID of the group from which to remove the person.
     * @param string $personId ID of the person to remove from the group.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     */
    public function delete($userId, $groupId, $personId, $optParams = array()) {
      $params = array('userId' => $userId, 'groupId' => $groupId, 'personId' => $personId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('delete', array($params));
      return $data;
    }
  }

  /**
   * The "photoAlbums" collection of methods.
   * Typical usage is:
   *  <code>
   *   $buzzService = new apiBuzzService(...);
   *   $photoAlbums = $buzzService->photoAlbums;
   *  </code>
   */
  class PhotoAlbumsServiceResource extends apiServiceResource {


    /**
     * Create a photo album (photoAlbums.insert)
     *
     * @param string $userId ID of the user being referenced.
     * @param $postBody the {@link Album}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return Album
     */
    public function insert($userId, Album $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('insert', array($params));
      if ($this->useObjects()) {
        return new Album($data);
      } else {
        return $data;
      }
    }
    /**
     * Get a photo album (photoAlbums.get)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $albumId ID of the album to get.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return Album
     */
    public function get($userId, $albumId, $optParams = array()) {
      $params = array('userId' => $userId, 'albumId' => $albumId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new Album($data);
      } else {
        return $data;
      }
    }
    /**
     * List a user's photo albums (photoAlbums.list)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $scope The collection of albums to list.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string hl Language code to limit language results.
     * @return AlbumsFeed
     */
    public function listPhotoAlbums($userId, $scope, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope);
      $params = array_merge($params, $optParams);
      $data = $this->__call('list', array($params));
      if ($this->useObjects()) {
        return new AlbumsFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Delete a photo album (photoAlbums.delete)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $albumId ID of the album to delete.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     */
    public function delete($userId, $albumId, $optParams = array()) {
      $params = array('userId' => $userId, 'albumId' => $albumId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('delete', array($params));
      return $data;
    }
  }

  /**
   * The "comments" collection of methods.
   * Typical usage is:
   *  <code>
   *   $buzzService = new apiBuzzService(...);
   *   $comments = $buzzService->comments;
   *  </code>
   */
  class CommentsServiceResource extends apiServiceResource {


    /**
     * Create a comment (comments.insert)
     *
     * @param string $userId ID of the user on whose behalf to comment.
     * @param string $postId ID of the activity on which to comment.
     * @param $postBody the {@link Comment}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return Comment
     */
    public function insert($userId, $postId, Comment $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'postId' => $postId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('insert', array($params));
      if ($this->useObjects()) {
        return new Comment($data);
      } else {
        return $data;
      }
    }
    /**
     * Get a comment (comments.get)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $postId ID of the activity for which to get comments.
     * @param string $commentId ID of the comment being referenced.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return Comment
     */
    public function get($userId, $postId, $commentId, $optParams = array()) {
      $params = array('userId' => $userId, 'postId' => $postId, 'commentId' => $commentId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new Comment($data);
      } else {
        return $data;
      }
    }
    /**
     * List comments (comments.list)
     *
     * @param string $userId ID of the user for whose post to get comments.
     * @param string $scope The collection to which the activity belongs.
     * @param string $postId ID of the activity for which to get comments.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string hl Language code to limit language results.
     * @return CommentFeed
     */
    public function listComments($userId, $scope, $postId, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope, 'postId' => $postId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('list', array($params));
      if ($this->useObjects()) {
        return new CommentFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Update a comment (comments.update)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $scope The collection to which the activity belongs.
     * @param string $postId ID of the activity for which to update the comment.
     * @param string $commentId ID of the comment being referenced.
     * @param $postBody the {@link Comment}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string abuseType
     * @opt_param string hl Language code to limit language results.
     * @return Comment
     */
    public function update($userId, $scope, $postId, $commentId, Comment $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope, 'postId' => $postId, 'commentId' => $commentId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('update', array($params));
      if ($this->useObjects()) {
        return new Comment($data);
      } else {
        return $data;
      }
    }
    /**
     * Update a comment. This method supports patch semantics. (comments.patch)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $scope The collection to which the activity belongs.
     * @param string $postId ID of the activity for which to update the comment.
     * @param string $commentId ID of the comment being referenced.
     * @param $postBody the {@link Comment}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string abuseType
     * @opt_param string hl Language code to limit language results.
     * @return Comment
     */
    public function patch($userId, $scope, $postId, $commentId, Comment $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope, 'postId' => $postId, 'commentId' => $commentId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('patch', array($params));
      if ($this->useObjects()) {
        return new Comment($data);
      } else {
        return $data;
      }
    }
    /**
     * Delete a comment (comments.delete)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $postId ID of the activity for which to delete the comment.
     * @param string $commentId ID of the comment being referenced.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     */
    public function delete($userId, $postId, $commentId, $optParams = array()) {
      $params = array('userId' => $userId, 'postId' => $postId, 'commentId' => $commentId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('delete', array($params));
      return $data;
    }
  }

  /**
   * The "photos" collection of methods.
   * Typical usage is:
   *  <code>
   *   $buzzService = new apiBuzzService(...);
   *   $photos = $buzzService->photos;
   *  </code>
   */
  class PhotosServiceResource extends apiServiceResource {


    /**
     * Upload a photo to an album (photos.insert2)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $albumId ID of the album to which to upload.
     * @param $postBody the {@link ChiliPhotosResourceJson}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return ChiliPhotosResourceJson
     */
    public function insert2($userId, $albumId, ChiliPhotosResourceJson $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'albumId' => $albumId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('insert2', array($params));
      if ($this->useObjects()) {
        return new ChiliPhotosResourceJson($data);
      } else {
        return $data;
      }
    }
    /**
     * Upload a photo to an album (photos.insert)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $albumId ID of the album to which to upload.
     * @param $postBody the {@link AlbumLite}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return AlbumLite
     */
    public function insert($userId, $albumId, AlbumLite $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'albumId' => $albumId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('insert', array($params));
      if ($this->useObjects()) {
        return new AlbumLite($data);
      } else {
        return $data;
      }
    }
    /**
     * Get photo metadata (photos.get)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $albumId ID of the album containing the photo.
     * @param string $photoId ID of the photo for which to get metadata.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return ChiliPhotosResourceJson
     */
    public function get($userId, $albumId, $photoId, $optParams = array()) {
      $params = array('userId' => $userId, 'albumId' => $albumId, 'photoId' => $photoId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new ChiliPhotosResourceJson($data);
      } else {
        return $data;
      }
    }
    /**
     * Get a user's photos (photos.listByScope)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $scope The collection of photos to list.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string hl Language code to limit language results.
     * @return PhotosFeed
     */
    public function listByScope($userId, $scope, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope);
      $params = array_merge($params, $optParams);
      $data = $this->__call('listByScope', array($params));
      if ($this->useObjects()) {
        return new PhotosFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Delete a photo (photos.delete)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $albumId ID of the album to which to photo belongs.
     * @param string $photoId ID of the photo to delete.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     */
    public function delete($userId, $albumId, $photoId, $optParams = array()) {
      $params = array('userId' => $userId, 'albumId' => $albumId, 'photoId' => $photoId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('delete', array($params));
      return $data;
    }
    /**
     * List photos in an album (photos.listByAlbum)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $albumId ID of the album for which to list photos.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string hl Language code to limit language results.
     * @return PhotosFeed
     */
    public function listByAlbum($userId, $albumId, $optParams = array()) {
      $params = array('userId' => $userId, 'albumId' => $albumId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('listByAlbum', array($params));
      if ($this->useObjects()) {
        return new PhotosFeed($data);
      } else {
        return $data;
      }
    }
  }

  /**
   * The "related" collection of methods.
   * Typical usage is:
   *  <code>
   *   $buzzService = new apiBuzzService(...);
   *   $related = $buzzService->related;
   *  </code>
   */
  class RelatedServiceResource extends apiServiceResource {


    /**
     * Get related links for an activity (related.list)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $scope The collection to which the activity belongs.
     * @param string $postId ID of the activity to which to get related links.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return RelatedFeed
     */
    public function listRelated($userId, $scope, $postId, $optParams = array()) {
      $params = array('userId' => $userId, 'scope' => $scope, 'postId' => $postId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('list', array($params));
      if ($this->useObjects()) {
        return new RelatedFeed($data);
      } else {
        return $data;
      }
    }
  }

  /**
   * The "groups" collection of methods.
   * Typical usage is:
   *  <code>
   *   $buzzService = new apiBuzzService(...);
   *   $groups = $buzzService->groups;
   *  </code>
   */
  class GroupsServiceResource extends apiServiceResource {


    /**
     * Create a group (groups.insert)
     *
     * @param string $userId ID of the user being referenced.
     * @param $postBody the {@link Group}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return Group
     */
    public function insert($userId, Group $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('insert', array($params));
      if ($this->useObjects()) {
        return new Group($data);
      } else {
        return $data;
      }
    }
    /**
     * Get a group (groups.get)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $groupId ID of the group to get.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return Group
     */
    public function get($userId, $groupId, $optParams = array()) {
      $params = array('userId' => $userId, 'groupId' => $groupId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('get', array($params));
      if ($this->useObjects()) {
        return new Group($data);
      } else {
        return $data;
      }
    }
    /**
     * Get a user's groups (groups.list)
     *
     * @param string $userId ID of the user being referenced.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string max-results Maximum number of results to include.
     * @opt_param string c A continuation token that allows pagination.
     * @opt_param string hl Language code to limit language results.
     * @return GroupFeed
     */
    public function listGroups($userId, $optParams = array()) {
      $params = array('userId' => $userId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('list', array($params));
      if ($this->useObjects()) {
        return new GroupFeed($data);
      } else {
        return $data;
      }
    }
    /**
     * Update a group (groups.update)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $groupId ID of the group to update.
     * @param $postBody the {@link Group}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return Group
     */
    public function update($userId, $groupId, Group $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'groupId' => $groupId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('update', array($params));
      if ($this->useObjects()) {
        return new Group($data);
      } else {
        return $data;
      }
    }
    /**
     * Update a group. This method supports patch semantics. (groups.patch)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $groupId ID of the group to update.
     * @param $postBody the {@link Group}
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     * @return Group
     */
    public function patch($userId, $groupId, Group $postBody, $optParams = array()) {
      $params = array('userId' => $userId, 'groupId' => $groupId, 'postBody' => $postBody);
      $params = array_merge($params, $optParams);
      $data = $this->__call('patch', array($params));
      if ($this->useObjects()) {
        return new Group($data);
      } else {
        return $data;
      }
    }
    /**
     * Delete a group (groups.delete)
     *
     * @param string $userId ID of the user being referenced.
     * @param string $groupId ID of the group to delete.
     * @param array $optParams Optional parameters. Valid optional parameters are listed below.
     *
     * @opt_param string hl Language code to limit language results.
     */
    public function delete($userId, $groupId, $optParams = array()) {
      $params = array('userId' => $userId, 'groupId' => $groupId);
      $params = array_merge($params, $optParams);
      $data = $this->__call('delete', array($params));
      return $data;
    }
  }



/**
 * Service definition for Buzz (v1).
 *
 * <p>
 * Lets you share updates, photos, videos, and more with your friends around the world
 * </p>
 *
 * <p>
 * For more information about this service, see the
 * <a href="http://code.google.com/apis/buzz/v1/using_rest.html" target="_blank">API Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class apiBuzzService extends apiService {
  public $activities;
  public $people;
  public $photoAlbums;
  public $comments;
  public $photos;
  public $related;
  public $groups;
  /**
   * Constructs the internal representation of the Buzz service.
   *
   * @param apiClient apiClient
   */
  public function __construct(apiClient $apiClient) {
    $this->rpcPath = '/rpc';
    $this->restBasePath = '/buzz/v1/';
    $this->version = 'v1';
    $this->serviceName = 'buzz';
    $this->io = $apiClient->getIo();

    $apiClient->addService($this->serviceName, $this->version);
    $this->activities = new ActivitiesServiceResource($this, $this->serviceName, 'activities', json_decode('{"methods": {"count": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"url": {"repeated": true, "type": "string", "location": "query"}, "hl": {"type": "string", "location": "query"}}, "response": {"$ref": "CountFeed"}, "httpMethod": "GET", "path": "activities/count", "id": "chili.activities.count"}, "insert": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "preview": {"default": "false", "type": "boolean", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "mediaUpload": {"maxSize": "10MB", "accept": ["image/*"], "protocols": {"simple": {"path": "upload/activities/{userId}/@self", "multipart": true}, "resumable": {"path": "resumable/upload/activities/{userId}/@self", "multipart": true}}}, "request": {"$ref": "Activity"}, "id": "chili.activities.insert", "httpMethod": "POST", "path": "activities/{userId}/@self", "response": {"$ref": "Activity"}}, "search": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "c": {"type": "string", "location": "query"}, "pid": {"type": "string", "location": "query"}, "lon": {"type": "string", "location": "query"}, "q": {"type": "string", "location": "query"}, "truncateAtom": {"type": "boolean", "location": "query"}, "radius": {"type": "string", "location": "query"}, "bbox": {"type": "string", "location": "query"}, "hl": {"type": "string", "location": "query"}, "lat": {"type": "string", "location": "query"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}}, "response": {"$ref": "ActivityFeed"}, "httpMethod": "GET", "path": "activities/search", "id": "chili.activities.search"}, "get": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"userId": {"required": true, "type": "string", "location": "path"}, "truncateAtom": {"type": "boolean", "location": "query"}, "max-comments": {"default": "0", "format": "uint32", "type": "integer", "location": "query"}, "hl": {"type": "string", "location": "query"}, "max-liked": {"default": "0", "format": "uint32", "type": "integer", "location": "query"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}}, "id": "chili.activities.get", "httpMethod": "GET", "path": "activities/{userId}/@self/{postId}", "response": {"$ref": "Activity"}}, "track": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "c": {"type": "string", "location": "query"}, "pid": {"type": "string", "location": "query"}, "lon": {"type": "string", "location": "query"}, "q": {"type": "string", "location": "query"}, "radius": {"type": "string", "location": "query"}, "bbox": {"type": "string", "location": "query"}, "hl": {"type": "string", "location": "query"}, "lat": {"type": "string", "location": "query"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}}, "response": {"$ref": "ActivityFeed"}, "httpMethod": "GET", "path": "activities/track", "id": "chili.activities.track"}, "list": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "c": {"type": "string", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}, "truncateAtom": {"type": "boolean", "location": "query"}, "max-comments": {"default": "0", "format": "uint32", "type": "integer", "location": "query"}, "hl": {"type": "string", "location": "query"}, "max-liked": {"default": "0", "format": "uint32", "type": "integer", "location": "query"}, "scope": {"required": true, "enum": ["@comments", "@consumption", "@liked", "@public", "@self"], "location": "path", "type": "string"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}}, "id": "chili.activities.list", "httpMethod": "GET", "path": "activities/{userId}/{scope}", "response": {"$ref": "ActivityFeed"}}, "update": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"userId": {"required": true, "type": "string", "location": "path"}, "abuseType": {"type": "string", "location": "query"}, "hl": {"type": "string", "location": "query"}, "scope": {"required": true, "enum": ["@abuse", "@liked", "@muted", "@self"], "location": "path", "type": "string"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}}, "request": {"$ref": "Activity"}, "id": "chili.activities.update", "httpMethod": "PUT", "path": "activities/{userId}/{scope}/{postId}", "response": {"$ref": "Activity"}}, "patch": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"userId": {"required": true, "type": "string", "location": "path"}, "abuseType": {"type": "string", "location": "query"}, "hl": {"type": "string", "location": "query"}, "scope": {"required": true, "enum": ["@abuse", "@liked", "@muted", "@self"], "location": "path", "type": "string"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}}, "request": {"$ref": "Activity"}, "id": "chili.activities.patch", "httpMethod": "PATCH", "path": "activities/{userId}/{scope}/{postId}", "response": {"$ref": "Activity"}}, "extractPeopleFromSearch": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "c": {"type": "string", "location": "query"}, "pid": {"type": "string", "location": "query"}, "lon": {"type": "string", "location": "query"}, "q": {"type": "string", "location": "query"}, "radius": {"type": "string", "location": "query"}, "bbox": {"type": "string", "location": "query"}, "hl": {"type": "string", "location": "query"}, "lat": {"type": "string", "location": "query"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}}, "response": {"$ref": "PeopleFeed"}, "httpMethod": "GET", "path": "activities/search/@people", "id": "chili.activities.extractPeopleFromSearch"}, "delete": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"scope": {"required": true, "enum": ["@liked", "@muted", "@self"], "location": "path", "type": "string"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "httpMethod": "DELETE", "path": "activities/{userId}/{scope}/{postId}", "id": "chili.activities.delete"}}}', true));
    $this->people = new PeopleServiceResource($this, $this->serviceName, 'people', json_decode('{"methods": {"search": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "q": {"type": "string", "location": "query"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "c": {"type": "string", "location": "query"}, "hl": {"type": "string", "location": "query"}}, "response": {"$ref": "PeopleFeed"}, "httpMethod": "GET", "path": "people/search", "id": "chili.people.search"}, "get": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "id": "chili.people.get", "httpMethod": "GET", "path": "people/{userId}/@self", "response": {"$ref": "Person"}}, "update": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"personId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "groupId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "request": {"$ref": "Person"}, "id": "chili.people.update", "httpMethod": "PUT", "path": "people/{userId}/@groups/{groupId}/{personId}", "response": {"$ref": "Person"}}, "list": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "c": {"type": "string", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "groupId": {"required": true, "type": "string", "location": "path"}}, "id": "chili.people.list", "httpMethod": "GET", "path": "people/{userId}/@groups/{groupId}", "response": {"$ref": "PeopleFeed"}}, "liked": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "c": {"type": "string", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}, "scope": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}, "groupId": {"required": true, "type": "string", "location": "path", "enum": ["@liked"]}}, "id": "chili.people.liked", "httpMethod": "GET", "path": "activities/{userId}/{scope}/{postId}/{groupId}", "response": {"$ref": "PeopleFeed"}}, "patch": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"personId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "groupId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "request": {"$ref": "Person"}, "id": "chili.people.patch", "httpMethod": "PATCH", "path": "people/{userId}/@groups/{groupId}/{personId}", "response": {"$ref": "Person"}}, "reshared": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "c": {"type": "string", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}, "scope": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}, "groupId": {"required": true, "type": "string", "location": "path", "enum": ["@reshared"]}}, "id": "chili.people.reshared", "httpMethod": "GET", "path": "activities/{userId}/{scope}/{postId}/{groupId}", "response": {"$ref": "PeopleFeed"}}, "delete": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"personId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "groupId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "httpMethod": "DELETE", "path": "people/{userId}/@groups/{groupId}/{personId}", "id": "chili.people.delete"}}}', true));
    $this->photoAlbums = new PhotoAlbumsServiceResource($this, $this->serviceName, 'photoAlbums', json_decode('{"methods": {"insert": {"scopes": ["https://www.googleapis.com/auth/picasa"], "parameters": {"alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "request": {"$ref": "Album"}, "id": "chili.photoAlbums.insert", "httpMethod": "POST", "path": "photos/{userId}/@self", "response": {"$ref": "Album"}}, "delete": {"scopes": ["https://www.googleapis.com/auth/picasa"], "parameters": {"albumId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "httpMethod": "DELETE", "path": "photos/{userId}/@self/{albumId}", "id": "chili.photoAlbums.delete"}, "list": {"scopes": ["https://www.googleapis.com/auth/picasa"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "c": {"type": "string", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}, "scope": {"required": true, "enum": ["@self"], "location": "path", "type": "string"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}}, "id": "chili.photoAlbums.list", "httpMethod": "GET", "path": "photos/{userId}/{scope}", "response": {"$ref": "AlbumsFeed"}}, "get": {"scopes": ["https://www.googleapis.com/auth/picasa"], "parameters": {"albumId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "id": "chili.photoAlbums.get", "httpMethod": "GET", "path": "photos/{userId}/@self/{albumId}", "response": {"$ref": "Album"}}}}', true));
    $this->comments = new CommentsServiceResource($this, $this->serviceName, 'comments', json_decode('{"methods": {"insert": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "request": {"$ref": "Comment"}, "id": "chili.comments.insert", "httpMethod": "POST", "path": "activities/{userId}/@self/{postId}/@comments", "response": {"$ref": "Comment"}}, "get": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"commentId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "id": "chili.comments.get", "httpMethod": "GET", "path": "activities/{userId}/@self/{postId}/@comments/{commentId}", "response": {"$ref": "Comment"}}, "list": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "c": {"type": "string", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}, "scope": {"required": true, "enum": ["@self"], "location": "path", "type": "string"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}}, "id": "chili.comments.list", "httpMethod": "GET", "path": "activities/{userId}/{scope}/{postId}/@comments", "response": {"$ref": "CommentFeed"}}, "update": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"userId": {"required": true, "type": "string", "location": "path"}, "abuseType": {"type": "string", "location": "query"}, "hl": {"type": "string", "location": "query"}, "commentId": {"required": true, "type": "string", "location": "path"}, "scope": {"required": true, "enum": ["@abuse", "@self"], "location": "path", "type": "string"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}}, "request": {"$ref": "Comment"}, "id": "chili.comments.update", "httpMethod": "PUT", "path": "activities/{userId}/{scope}/{postId}/@comments/{commentId}", "response": {"$ref": "Comment"}}, "patch": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"userId": {"required": true, "type": "string", "location": "path"}, "abuseType": {"type": "string", "location": "query"}, "hl": {"type": "string", "location": "query"}, "commentId": {"required": true, "type": "string", "location": "path"}, "scope": {"required": true, "enum": ["@abuse", "@self"], "location": "path", "type": "string"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}}, "request": {"$ref": "Comment"}, "id": "chili.comments.patch", "httpMethod": "PATCH", "path": "activities/{userId}/{scope}/{postId}/@comments/{commentId}", "response": {"$ref": "Comment"}}, "delete": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"commentId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "httpMethod": "DELETE", "path": "activities/{userId}/@self/{postId}/@comments/{commentId}", "id": "chili.comments.delete"}}}', true));
    $this->photos = new PhotosServiceResource($this, $this->serviceName, 'photos', json_decode('{"methods": {"insert2": {"scopes": ["https://www.googleapis.com/auth/picasa"], "parameters": {"albumId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "mediaUpload": {"maxSize": "30MB", "accept": ["image/*"], "protocols": {"simple": {"path": "upload/photos/{userId}/@self/{albumId}/@photos", "multipart": true}, "resumable": {"path": "resumable/upload/photos/{userId}/@self/{albumId}/@photos", "multipart": true}}}, "request": {"$ref": "ChiliPhotosResourceJson"}, "id": "chili.photos.insert2", "httpMethod": "POST", "path": "photos/{userId}/@self/{albumId}/@photos", "response": {"$ref": "ChiliPhotosResourceJson"}}, "insert": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"albumId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "mediaUpload": {"maxSize": "30MB", "accept": ["image/*"], "protocols": {"simple": {"path": "upload/photos/{userId}/{albumId}", "multipart": true}, "resumable": {"path": "resumable/upload/photos/{userId}/{albumId}", "multipart": true}}}, "request": {"$ref": "AlbumLite"}, "id": "chili.photos.insert", "httpMethod": "POST", "path": "photos/{userId}/{albumId}", "response": {"$ref": "AlbumLite"}}, "get": {"scopes": ["https://www.googleapis.com/auth/picasa"], "parameters": {"albumId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "photoId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}}, "id": "chili.photos.get", "httpMethod": "GET", "path": "photos/{userId}/@self/{albumId}/@photos/{photoId}", "response": {"$ref": "ChiliPhotosResourceJson"}}, "listByScope": {"scopes": ["https://www.googleapis.com/auth/picasa"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "c": {"type": "string", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}, "scope": {"required": true, "enum": ["@recent"], "location": "path", "type": "string"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}}, "id": "chili.photos.listByScope", "httpMethod": "GET", "path": "photos/{userId}/@self/{scope}/@photos", "response": {"$ref": "PhotosFeed"}}, "listByAlbum": {"scopes": ["https://www.googleapis.com/auth/picasa"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "c": {"type": "string", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}, "albumId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}}, "id": "chili.photos.listByAlbum", "httpMethod": "GET", "path": "photos/{userId}/@self/{albumId}/@photos", "response": {"$ref": "PhotosFeed"}}, "delete": {"scopes": ["https://www.googleapis.com/auth/picasa"], "parameters": {"albumId": {"required": true, "type": "string", "location": "path"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "photoId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}, "userId": {"required": true, "type": "string", "location": "path"}}, "httpMethod": "DELETE", "path": "photos/{userId}/@self/{albumId}/@photos/{photoId}", "id": "chili.photos.delete"}}}', true));
    $this->related = new RelatedServiceResource($this, $this->serviceName, 'related', json_decode('{"methods": {"list": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"scope": {"required": true, "enum": ["@self"], "location": "path", "type": "string"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "postId": {"required": true, "type": "string", "location": "path"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "id": "chili.related.list", "httpMethod": "GET", "path": "activities/{userId}/{scope}/{postId}/@related", "response": {"$ref": "RelatedFeed"}}}}', true));
    $this->groups = new GroupsServiceResource($this, $this->serviceName, 'groups', json_decode('{"methods": {"insert": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "request": {"$ref": "Group"}, "id": "chili.groups.insert", "httpMethod": "POST", "path": "people/{userId}/@groups", "response": {"$ref": "Group"}}, "get": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "groupId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "id": "chili.groups.get", "httpMethod": "GET", "path": "people/{userId}/@groups/{groupId}/@self", "response": {"$ref": "Group"}}, "list": {"scopes": ["https://www.googleapis.com/auth/buzz", "https://www.googleapis.com/auth/buzz.readonly"], "parameters": {"max-results": {"default": "20", "format": "uint32", "type": "integer", "location": "query"}, "alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "c": {"type": "string", "location": "query"}, "hl": {"type": "string", "location": "query"}}, "id": "chili.groups.list", "httpMethod": "GET", "path": "people/{userId}/@groups", "response": {"$ref": "GroupFeed"}}, "update": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "groupId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "request": {"$ref": "Group"}, "id": "chili.groups.update", "httpMethod": "PUT", "path": "people/{userId}/@groups/{groupId}/@self", "response": {"$ref": "Group"}}, "patch": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "groupId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "request": {"$ref": "Group"}, "id": "chili.groups.patch", "httpMethod": "PATCH", "path": "people/{userId}/@groups/{groupId}/@self", "response": {"$ref": "Group"}}, "delete": {"scopes": ["https://www.googleapis.com/auth/buzz"], "parameters": {"alt": {"default": "atom", "enum": ["atom", "json"], "location": "query", "type": "string"}, "userId": {"required": true, "type": "string", "location": "path"}, "groupId": {"required": true, "type": "string", "location": "path"}, "hl": {"type": "string", "location": "query"}}, "httpMethod": "DELETE", "path": "people/{userId}/@groups/{groupId}", "id": "chili.groups.delete"}}}', true));
  }
}

class Album extends apiModel {

  public $kind;
  public $description;
  public $links;
  public $created;
  public $lastModified;
  public $tags;
  public $version;
  public $firstPhotoId;
  public $owner;
  public $title;
  public $id;

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
  
  public function setLinks(AlbumLinks $links) {
    $this->links = $links;
  }

  public function getLinks() {
    return $this->links;
  }
  
  public function setCreated($created) {
    $this->created = $created;
  }

  public function getCreated() {
    return $this->created;
  }
  
  public function setLastModified($lastModified) {
    $this->lastModified = $lastModified;
  }

  public function getLastModified() {
    return $this->lastModified;
  }
  
  public function setTags($tags) {
    $this->tags = $tags;
  }

  public function getTags() {
    return $this->tags;
  }
  
  public function setVersion($version) {
    $this->version = $version;
  }

  public function getVersion() {
    return $this->version;
  }
  
  public function setFirstPhotoId($firstPhotoId) {
    $this->firstPhotoId = $firstPhotoId;
  }

  public function getFirstPhotoId() {
    return $this->firstPhotoId;
  }
  
  public function setOwner(AlbumOwner $owner) {
    $this->owner = $owner;
  }

  public function getOwner() {
    return $this->owner;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
}


class PersonAccounts extends apiModel {

  public $username;
  public $domain;
  public $userid;

  public function setUsername($username) {
    $this->username = $username;
  }

  public function getUsername() {
    return $this->username;
  }
  
  public function setDomain($domain) {
    $this->domain = $domain;
  }

  public function getDomain() {
    return $this->domain;
  }
  
  public function setUserid($userid) {
    $this->userid = $userid;
  }

  public function getUserid() {
    return $this->userid;
  }
  
}


class ActivityFeed extends apiModel {

  public $kind;
  public $links;
  public $title;
  public $items;
  public $updated;
  public $id;

  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setLinks(ActivityFeedLinksItems $links) {
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
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
}


class Group extends apiModel {

  public $memberCount;
  public $kind;
  public $id;
  public $links;
  public $title;

  public function setMemberCount($memberCount) {
    $this->memberCount = $memberCount;
  }

  public function getMemberCount() {
    return $this->memberCount;
  }
  
  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setLinks(GroupLinks $links) {
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
  
}


class RelatedFeed extends apiModel {

  public $kind;
  public $links;
  public $title;
  public $items;
  public $updated;
  public $id;

  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setLinks(RelatedFeedLinksItems $links) {
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
  
  public function setItems(Related $items) {
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
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
}


class AlbumsFeed extends apiModel {

  public $items;
  public $kind;

  public function setItems(Album $items) {
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


class CommentLinksInReplyTo extends apiModel {

  public $source;
  public $href;
  public $ref;

  public function setSource($source) {
    $this->source = $source;
  }

  public function getSource() {
    return $this->source;
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
  
}


class PersonEmails extends apiModel {

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


class ActivitySource extends apiModel {

  public $title;

  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
}


class PhotosFeed extends apiModel {

  public $items;
  public $kind;

  public function setItems(ChiliPhotosResourceJson $items) {
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


class ActivityObjectActor extends apiModel {

  public $profileUrl;
  public $thumbnailUrl;
  public $id;
  public $name;

  public function setProfileUrl($profileUrl) {
    $this->profileUrl = $profileUrl;
  }

  public function getProfileUrl() {
    return $this->profileUrl;
  }
  
  public function setThumbnailUrl($thumbnailUrl) {
    $this->thumbnailUrl = $thumbnailUrl;
  }

  public function getThumbnailUrl() {
    return $this->thumbnailUrl;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }
  
}


class ActivityCategories extends apiModel {

  public $term;
  public $schema;
  public $label;

  public function setTerm($term) {
    $this->term = $term;
  }

  public function getTerm() {
    return $this->term;
  }
  
  public function setSchema($schema) {
    $this->schema = $schema;
  }

  public function getSchema() {
    return $this->schema;
  }
  
  public function setLabel($label) {
    $this->label = $label;
  }

  public function getLabel() {
    return $this->label;
  }
  
}


class ChiliPhotosResourceJsonOwner extends apiModel {

  public $profileUrl;
  public $thumbnailUrl;
  public $id;
  public $name;

  public function setProfileUrl($profileUrl) {
    $this->profileUrl = $profileUrl;
  }

  public function getProfileUrl() {
    return $this->profileUrl;
  }
  
  public function setThumbnailUrl($thumbnailUrl) {
    $this->thumbnailUrl = $thumbnailUrl;
  }

  public function getThumbnailUrl() {
    return $this->thumbnailUrl;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }
  
}


class ActivityVisibility extends apiModel {

  public $entries;

  public function setEntries(ActivityVisibilityEntries $entries) {
    $this->entries = $entries;
  }

  public function getEntries() {
    return $this->entries;
  }
  
}


class ActivityLinks extends apiModel {

  public $liked;

  public function setLiked(ActivityLinksLiked $liked) {
    $this->liked = $liked;
  }

  public function getLiked() {
    return $this->liked;
  }
  
}


class ActivityObjectAttachmentsLinksItems extends apiModel {

  public $count;
  public $updated;
  public $title;
  public $height;
  public $width;
  public $href;
  public $type;

  public function setCount($count) {
    $this->count = $count;
  }

  public function getCount() {
    return $this->count;
  }
  
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getUpdated() {
    return $this->updated;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }
  
  public function setWidth($width) {
    $this->width = $width;
  }

  public function getWidth() {
    return $this->width;
  }
  
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
  
}


class RelatedFeedLinksItems extends apiModel {

  public $count;
  public $updated;
  public $title;
  public $height;
  public $width;
  public $href;
  public $type;

  public function setCount($count) {
    $this->count = $count;
  }

  public function getCount() {
    return $this->count;
  }
  
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getUpdated() {
    return $this->updated;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }
  
  public function setWidth($width) {
    $this->width = $width;
  }

  public function getWidth() {
    return $this->width;
  }
  
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
  
}


class CommentActor extends apiModel {

  public $profileUrl;
  public $thumbnailUrl;
  public $id;
  public $name;

  public function setProfileUrl($profileUrl) {
    $this->profileUrl = $profileUrl;
  }

  public function getProfileUrl() {
    return $this->profileUrl;
  }
  
  public function setThumbnailUrl($thumbnailUrl) {
    $this->thumbnailUrl = $thumbnailUrl;
  }

  public function getThumbnailUrl() {
    return $this->thumbnailUrl;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }
  
}


class AlbumLiteCollectionPhoto extends apiModel {

  public $photoUrl;

  public function setPhotoUrl($photoUrl) {
    $this->photoUrl = $photoUrl;
  }

  public function getPhotoUrl() {
    return $this->photoUrl;
  }
  
}


class CommentFeedLinks extends apiModel {


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


class PersonPhotos extends apiModel {

  public $width;
  public $type;
  public $primary;
  public $value;
  public $height;

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
  
  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }
  
}


class ChiliPhotosResourceJson extends apiModel {

  public $album;
  public $kind;
  public $description;
  public $links;
  public $created;
  public $lastModified;
  public $title;
  public $version;
  public $video;
  public $fileSize;
  public $timestamp;
  public $owner;
  public $id;

  public function setAlbum(ChiliPhotosResourceJsonAlbum $album) {
    $this->album = $album;
  }

  public function getAlbum() {
    return $this->album;
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
  
  public function setLinks(ChiliPhotosResourceJsonLinks $links) {
    $this->links = $links;
  }

  public function getLinks() {
    return $this->links;
  }
  
  public function setCreated($created) {
    $this->created = $created;
  }

  public function getCreated() {
    return $this->created;
  }
  
  public function setLastModified($lastModified) {
    $this->lastModified = $lastModified;
  }

  public function getLastModified() {
    return $this->lastModified;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setVersion($version) {
    $this->version = $version;
  }

  public function getVersion() {
    return $this->version;
  }
  
  public function setVideo(Video $video) {
    $this->video = $video;
  }

  public function getVideo() {
    return $this->video;
  }
  
  public function setFileSize($fileSize) {
    $this->fileSize = $fileSize;
  }

  public function getFileSize() {
    return $this->fileSize;
  }
  
  public function setTimestamp($timestamp) {
    $this->timestamp = $timestamp;
  }

  public function getTimestamp() {
    return $this->timestamp;
  }
  
  public function setOwner(ChiliPhotosResourceJsonOwner $owner) {
    $this->owner = $owner;
  }

  public function getOwner() {
    return $this->owner;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
}


class GroupFeedLinks extends apiModel {


}


class AlbumOwner extends apiModel {

  public $profileUrl;
  public $thumbnailUrl;
  public $id;
  public $name;

  public function setProfileUrl($profileUrl) {
    $this->profileUrl = $profileUrl;
  }

  public function getProfileUrl() {
    return $this->profileUrl;
  }
  
  public function setThumbnailUrl($thumbnailUrl) {
    $this->thumbnailUrl = $thumbnailUrl;
  }

  public function getThumbnailUrl() {
    return $this->thumbnailUrl;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }
  
}


class ActivityObjectAttachmentsLinks extends apiModel {


}


class Link extends apiModel {

  public $count;
  public $updated;
  public $title;
  public $height;
  public $width;
  public $href;
  public $type;

  public function setCount($count) {
    $this->count = $count;
  }

  public function getCount() {
    return $this->count;
  }
  
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getUpdated() {
    return $this->updated;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }
  
  public function setWidth($width) {
    $this->width = $width;
  }

  public function getWidth() {
    return $this->width;
  }
  
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
  
}


class PeopleFeed extends apiModel {

  public $totalResults;
  public $entry;
  public $kind;
  public $itemsPerPage;
  public $startIndex;

  public function setTotalResults($totalResults) {
    $this->totalResults = $totalResults;
  }

  public function getTotalResults() {
    return $this->totalResults;
  }
  
  public function setEntry(Person $entry) {
    $this->entry = $entry;
  }

  public function getEntry() {
    return $this->entry;
  }
  
  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setItemsPerPage($itemsPerPage) {
    $this->itemsPerPage = $itemsPerPage;
  }

  public function getItemsPerPage() {
    return $this->itemsPerPage;
  }
  
  public function setStartIndex($startIndex) {
    $this->startIndex = $startIndex;
  }

  public function getStartIndex() {
    return $this->startIndex;
  }
  
}


class GroupFeedLinksItems extends apiModel {

  public $count;
  public $updated;
  public $title;
  public $height;
  public $width;
  public $href;
  public $type;

  public function setCount($count) {
    $this->count = $count;
  }

  public function getCount() {
    return $this->count;
  }
  
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getUpdated() {
    return $this->updated;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }
  
  public function setWidth($width) {
    $this->width = $width;
  }

  public function getWidth() {
    return $this->width;
  }
  
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
  
}


class ActivityVisibilityEntries extends apiModel {

  public $id;
  public $title;

  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
}


class AlbumLiteCollection extends apiModel {

  public $album;
  public $albumId;
  public $photo;

  public function setAlbum($album) {
    $this->album = $album;
  }

  public function getAlbum() {
    return $this->album;
  }
  
  public function setAlbumId($albumId) {
    $this->albumId = $albumId;
  }

  public function getAlbumId() {
    return $this->albumId;
  }
  
  public function setPhoto(AlbumLiteCollectionPhoto $photo) {
    $this->photo = $photo;
  }

  public function getPhoto() {
    return $this->photo;
  }
  
}


class CommentLinks extends apiModel {

  public $inReplyTo;

  public function setInReplyTo(CommentLinksInReplyTo $inReplyTo) {
    $this->inReplyTo = $inReplyTo;
  }

  public function getInReplyTo() {
    return $this->inReplyTo;
  }
  
}


class PersonIms extends apiModel {

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


class RelatedFeedLinks extends apiModel {


}


class ChiliPhotosResourceJsonLinks extends apiModel {

  public $alternate;

  public function setAlternate(Link $alternate) {
    $this->alternate = $alternate;
  }

  public function getAlternate() {
    return $this->alternate;
  }
  
}


class ActivityFeedLinksItems extends apiModel {

  public $count;
  public $updated;
  public $title;
  public $height;
  public $width;
  public $href;
  public $type;

  public function setCount($count) {
    $this->count = $count;
  }

  public function getCount() {
    return $this->count;
  }
  
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getUpdated() {
    return $this->updated;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }
  
  public function setWidth($width) {
    $this->width = $width;
  }

  public function getWidth() {
    return $this->width;
  }
  
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
  
}


class Person extends apiModel {

  public $status;
  public $phoneNumbers;
  public $fashion;
  public $addresses;
  public $romance;
  public $turnOffs;
  public $preferredUsername;
  public $quotes;
  public $books;
  public $accounts;
  public $turnOns;
  public $hasApp;
  public $drinker;
  public $sports;
  public $languagesSpoken;
  public $children;
  public $ethnicity;
  public $relationships;
  public $note;
  public $thumbnailUrl;
  public $humor;
  public $id;
  public $tags;
  public $anniversary;
  public $happiestWhen;
  public $currentLocation;
  public $languages;
  public $religion;
  public $ims;
  public $music;
  public $pets;
  public $interests;
  public $activities;
  public $updated;
  public $bodyType;
  public $relationshipStatus;
  public $sexualOrientation;
  public $food;
  public $cars;
  public $aboutMe;
  public $kind;
  public $photos;
  public $birthday;
  public $connected;
  public $profileVideo;
  public $heroes;
  public $nickname;
  public $emails;
  public $organizations;
  public $jobInterests;
  public $displayName;
  public $name;
  public $politicalViews;
  public $tvShows;
  public $gender;
  public $profileSong;
  public $smoker;
  public $scaredOf;
  public $profileUrl;
  public $movies;
  public $utcOffset;
  public $urls;
  public $published;
  public $livingArrangement;
  public $lookingFor;

  public function setStatus($status) {
    $this->status = $status;
  }

  public function getStatus() {
    return $this->status;
  }
  
  public function setPhoneNumbers(PersonPhoneNumbers $phoneNumbers) {
    $this->phoneNumbers = $phoneNumbers;
  }

  public function getPhoneNumbers() {
    return $this->phoneNumbers;
  }
  
  public function setFashion($fashion) {
    $this->fashion = $fashion;
  }

  public function getFashion() {
    return $this->fashion;
  }
  
  public function setAddresses(PersonAddresses $addresses) {
    $this->addresses = $addresses;
  }

  public function getAddresses() {
    return $this->addresses;
  }
  
  public function setRomance($romance) {
    $this->romance = $romance;
  }

  public function getRomance() {
    return $this->romance;
  }
  
  public function setTurnOffs($turnOffs) {
    $this->turnOffs = $turnOffs;
  }

  public function getTurnOffs() {
    return $this->turnOffs;
  }
  
  public function setPreferredUsername($preferredUsername) {
    $this->preferredUsername = $preferredUsername;
  }

  public function getPreferredUsername() {
    return $this->preferredUsername;
  }
  
  public function setQuotes($quotes) {
    $this->quotes = $quotes;
  }

  public function getQuotes() {
    return $this->quotes;
  }
  
  public function setBooks($books) {
    $this->books = $books;
  }

  public function getBooks() {
    return $this->books;
  }
  
  public function setAccounts(PersonAccounts $accounts) {
    $this->accounts = $accounts;
  }

  public function getAccounts() {
    return $this->accounts;
  }
  
  public function setTurnOns($turnOns) {
    $this->turnOns = $turnOns;
  }

  public function getTurnOns() {
    return $this->turnOns;
  }
  
  public function setHasApp($hasApp) {
    $this->hasApp = $hasApp;
  }

  public function getHasApp() {
    return $this->hasApp;
  }
  
  public function setDrinker($drinker) {
    $this->drinker = $drinker;
  }

  public function getDrinker() {
    return $this->drinker;
  }
  
  public function setSports($sports) {
    $this->sports = $sports;
  }

  public function getSports() {
    return $this->sports;
  }
  
  public function setLanguagesSpoken($languagesSpoken) {
    $this->languagesSpoken = $languagesSpoken;
  }

  public function getLanguagesSpoken() {
    return $this->languagesSpoken;
  }
  
  public function setChildren($children) {
    $this->children = $children;
  }

  public function getChildren() {
    return $this->children;
  }
  
  public function setEthnicity($ethnicity) {
    $this->ethnicity = $ethnicity;
  }

  public function getEthnicity() {
    return $this->ethnicity;
  }
  
  public function setRelationships($relationships) {
    $this->relationships = $relationships;
  }

  public function getRelationships() {
    return $this->relationships;
  }
  
  public function setNote($note) {
    $this->note = $note;
  }

  public function getNote() {
    return $this->note;
  }
  
  public function setThumbnailUrl($thumbnailUrl) {
    $this->thumbnailUrl = $thumbnailUrl;
  }

  public function getThumbnailUrl() {
    return $this->thumbnailUrl;
  }
  
  public function setHumor($humor) {
    $this->humor = $humor;
  }

  public function getHumor() {
    return $this->humor;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setTags($tags) {
    $this->tags = $tags;
  }

  public function getTags() {
    return $this->tags;
  }
  
  public function setAnniversary($anniversary) {
    $this->anniversary = $anniversary;
  }

  public function getAnniversary() {
    return $this->anniversary;
  }
  
  public function setHappiestWhen($happiestWhen) {
    $this->happiestWhen = $happiestWhen;
  }

  public function getHappiestWhen() {
    return $this->happiestWhen;
  }
  
  public function setCurrentLocation($currentLocation) {
    $this->currentLocation = $currentLocation;
  }

  public function getCurrentLocation() {
    return $this->currentLocation;
  }
  
  public function setLanguages($languages) {
    $this->languages = $languages;
  }

  public function getLanguages() {
    return $this->languages;
  }
  
  public function setReligion($religion) {
    $this->religion = $religion;
  }

  public function getReligion() {
    return $this->religion;
  }
  
  public function setIms(PersonIms $ims) {
    $this->ims = $ims;
  }

  public function getIms() {
    return $this->ims;
  }
  
  public function setMusic($music) {
    $this->music = $music;
  }

  public function getMusic() {
    return $this->music;
  }
  
  public function setPets($pets) {
    $this->pets = $pets;
  }

  public function getPets() {
    return $this->pets;
  }
  
  public function setInterests($interests) {
    $this->interests = $interests;
  }

  public function getInterests() {
    return $this->interests;
  }
  
  public function setActivities($activities) {
    $this->activities = $activities;
  }

  public function getActivities() {
    return $this->activities;
  }
  
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getUpdated() {
    return $this->updated;
  }
  
  public function setBodyType($bodyType) {
    $this->bodyType = $bodyType;
  }

  public function getBodyType() {
    return $this->bodyType;
  }
  
  public function setRelationshipStatus($relationshipStatus) {
    $this->relationshipStatus = $relationshipStatus;
  }

  public function getRelationshipStatus() {
    return $this->relationshipStatus;
  }
  
  public function setSexualOrientation($sexualOrientation) {
    $this->sexualOrientation = $sexualOrientation;
  }

  public function getSexualOrientation() {
    return $this->sexualOrientation;
  }
  
  public function setFood($food) {
    $this->food = $food;
  }

  public function getFood() {
    return $this->food;
  }
  
  public function setCars($cars) {
    $this->cars = $cars;
  }

  public function getCars() {
    return $this->cars;
  }
  
  public function setAboutMe($aboutMe) {
    $this->aboutMe = $aboutMe;
  }

  public function getAboutMe() {
    return $this->aboutMe;
  }
  
  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setPhotos(PersonPhotos $photos) {
    $this->photos = $photos;
  }

  public function getPhotos() {
    return $this->photos;
  }
  
  public function setBirthday($birthday) {
    $this->birthday = $birthday;
  }

  public function getBirthday() {
    return $this->birthday;
  }
  
  public function setConnected($connected) {
    $this->connected = $connected;
  }

  public function getConnected() {
    return $this->connected;
  }
  
  public function setProfileVideo($profileVideo) {
    $this->profileVideo = $profileVideo;
  }

  public function getProfileVideo() {
    return $this->profileVideo;
  }
  
  public function setHeroes($heroes) {
    $this->heroes = $heroes;
  }

  public function getHeroes() {
    return $this->heroes;
  }
  
  public function setNickname($nickname) {
    $this->nickname = $nickname;
  }

  public function getNickname() {
    return $this->nickname;
  }
  
  public function setEmails(PersonEmails $emails) {
    $this->emails = $emails;
  }

  public function getEmails() {
    return $this->emails;
  }
  
  public function setOrganizations(PersonOrganizations $organizations) {
    $this->organizations = $organizations;
  }

  public function getOrganizations() {
    return $this->organizations;
  }
  
  public function setJobInterests($jobInterests) {
    $this->jobInterests = $jobInterests;
  }

  public function getJobInterests() {
    return $this->jobInterests;
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
  
  public function setPoliticalViews($politicalViews) {
    $this->politicalViews = $politicalViews;
  }

  public function getPoliticalViews() {
    return $this->politicalViews;
  }
  
  public function setTvShows($tvShows) {
    $this->tvShows = $tvShows;
  }

  public function getTvShows() {
    return $this->tvShows;
  }
  
  public function setGender($gender) {
    $this->gender = $gender;
  }

  public function getGender() {
    return $this->gender;
  }
  
  public function setProfileSong($profileSong) {
    $this->profileSong = $profileSong;
  }

  public function getProfileSong() {
    return $this->profileSong;
  }
  
  public function setSmoker($smoker) {
    $this->smoker = $smoker;
  }

  public function getSmoker() {
    return $this->smoker;
  }
  
  public function setScaredOf($scaredOf) {
    $this->scaredOf = $scaredOf;
  }

  public function getScaredOf() {
    return $this->scaredOf;
  }
  
  public function setProfileUrl($profileUrl) {
    $this->profileUrl = $profileUrl;
  }

  public function getProfileUrl() {
    return $this->profileUrl;
  }
  
  public function setMovies($movies) {
    $this->movies = $movies;
  }

  public function getMovies() {
    return $this->movies;
  }
  
  public function setUtcOffset($utcOffset) {
    $this->utcOffset = $utcOffset;
  }

  public function getUtcOffset() {
    return $this->utcOffset;
  }
  
  public function setUrls(PersonUrls $urls) {
    $this->urls = $urls;
  }

  public function getUrls() {
    return $this->urls;
  }
  
  public function setPublished($published) {
    $this->published = $published;
  }

  public function getPublished() {
    return $this->published;
  }
  
  public function setLivingArrangement($livingArrangement) {
    $this->livingArrangement = $livingArrangement;
  }

  public function getLivingArrangement() {
    return $this->livingArrangement;
  }
  
  public function setLookingFor($lookingFor) {
    $this->lookingFor = $lookingFor;
  }

  public function getLookingFor() {
    return $this->lookingFor;
  }
  
}


class ActivityFeedLinks extends apiModel {


}


class GroupLinksSelf extends apiModel {

  public $href;
  public $type;

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
  
}


class CountFeedCounts extends apiModel {


}


class Activity extends apiModel {

  public $untranslatedTitle;
  public $links;
  public $radius;
  public $id;
  public $title;
  public $geocode;
  public $actor;
  public $source;
  public $verbs;
  public $crosspostSource;
  public $placeName;
  public $updated;
  public $object;
  public $visibility;
  public $detectedlLang;
  public $address;
  public $placeholder;
  public $annotation;
  public $categories;
  public $targetLang;
  public $kind;
  public $placeId;
  public $published;

  public function setUntranslatedTitle($untranslatedTitle) {
    $this->untranslatedTitle = $untranslatedTitle;
  }

  public function getUntranslatedTitle() {
    return $this->untranslatedTitle;
  }
  
  public function setLinks(ActivityLinks $links) {
    $this->links = $links;
  }

  public function getLinks() {
    return $this->links;
  }
  
  public function setRadius($radius) {
    $this->radius = $radius;
  }

  public function getRadius() {
    return $this->radius;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setGeocode($geocode) {
    $this->geocode = $geocode;
  }

  public function getGeocode() {
    return $this->geocode;
  }
  
  public function setActor(ActivityActor $actor) {
    $this->actor = $actor;
  }

  public function getActor() {
    return $this->actor;
  }
  
  public function setSource(ActivitySource $source) {
    $this->source = $source;
  }

  public function getSource() {
    return $this->source;
  }
  
  public function setVerbs($verbs) {
    $this->verbs = $verbs;
  }

  public function getVerbs() {
    return $this->verbs;
  }
  
  public function setCrosspostSource($crosspostSource) {
    $this->crosspostSource = $crosspostSource;
  }

  public function getCrosspostSource() {
    return $this->crosspostSource;
  }
  
  public function setPlaceName($placeName) {
    $this->placeName = $placeName;
  }

  public function getPlaceName() {
    return $this->placeName;
  }
  
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getUpdated() {
    return $this->updated;
  }
  
  public function setObject(ActivityObject $object) {
    $this->object = $object;
  }

  public function getObject() {
    return $this->object;
  }
  
  public function setVisibility(ActivityVisibility $visibility) {
    $this->visibility = $visibility;
  }

  public function getVisibility() {
    return $this->visibility;
  }
  
  public function setDetectedlLang($detectedlLang) {
    $this->detectedlLang = $detectedlLang;
  }

  public function getDetectedlLang() {
    return $this->detectedlLang;
  }
  
  public function setAddress($address) {
    $this->address = $address;
  }

  public function getAddress() {
    return $this->address;
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
  
  public function setCategories(ActivityCategories $categories) {
    $this->categories = $categories;
  }

  public function getCategories() {
    return $this->categories;
  }
  
  public function setTargetLang($targetLang) {
    $this->targetLang = $targetLang;
  }

  public function getTargetLang() {
    return $this->targetLang;
  }
  
  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setPlaceId($placeId) {
    $this->placeId = $placeId;
  }

  public function getPlaceId() {
    return $this->placeId;
  }
  
  public function setPublished($published) {
    $this->published = $published;
  }

  public function getPublished() {
    return $this->published;
  }
  
}


class ActivityActor extends apiModel {

  public $profileUrl;
  public $thumbnailUrl;
  public $id;
  public $name;

  public function setProfileUrl($profileUrl) {
    $this->profileUrl = $profileUrl;
  }

  public function getProfileUrl() {
    return $this->profileUrl;
  }
  
  public function setThumbnailUrl($thumbnailUrl) {
    $this->thumbnailUrl = $thumbnailUrl;
  }

  public function getThumbnailUrl() {
    return $this->thumbnailUrl;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setName($name) {
    $this->name = $name;
  }

  public function getName() {
    return $this->name;
  }
  
}


class ActivityObjectLinks extends apiModel {


}


class PersonPhoneNumbers extends apiModel {

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


class CommentFeed extends apiModel {

  public $kind;
  public $links;
  public $title;
  public $items;
  public $updated;
  public $id;

  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setLinks(CommentFeedLinksItems $links) {
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
  
  public function setItems(Comment $items) {
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
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
}


class GroupLinks extends apiModel {

  public $self;

  public function setSelf(GroupLinksSelf $self) {
    $this->self = $self;
  }

  public function getSelf() {
    return $this->self;
  }
  
}


class Comment extends apiModel {

  public $targetLang;
  public $kind;
  public $untranslatedContent;
  public $links;
  public $originalContent;
  public $updated;
  public $actor;
  public $content;
  public $published;
  public $detectedLang;
  public $placeholder;
  public $id;

  public function setTargetLang($targetLang) {
    $this->targetLang = $targetLang;
  }

  public function getTargetLang() {
    return $this->targetLang;
  }
  
  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setUntranslatedContent($untranslatedContent) {
    $this->untranslatedContent = $untranslatedContent;
  }

  public function getUntranslatedContent() {
    return $this->untranslatedContent;
  }
  
  public function setLinks(CommentLinks $links) {
    $this->links = $links;
  }

  public function getLinks() {
    return $this->links;
  }
  
  public function setOriginalContent($originalContent) {
    $this->originalContent = $originalContent;
  }

  public function getOriginalContent() {
    return $this->originalContent;
  }
  
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getUpdated() {
    return $this->updated;
  }
  
  public function setActor(CommentActor $actor) {
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
  
  public function setDetectedLang($detectedLang) {
    $this->detectedLang = $detectedLang;
  }

  public function getDetectedLang() {
    return $this->detectedLang;
  }
  
  public function setPlaceholder($placeholder) {
    $this->placeholder = $placeholder;
  }

  public function getPlaceholder() {
    return $this->placeholder;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
}


class AlbumLite extends apiModel {

  public $kind;
  public $collection;

  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setCollection(AlbumLiteCollection $collection) {
    $this->collection = $collection;
  }

  public function getCollection() {
    return $this->collection;
  }
  
}


class CommentFeedLinksItems extends apiModel {

  public $count;
  public $updated;
  public $title;
  public $height;
  public $width;
  public $href;
  public $type;

  public function setCount($count) {
    $this->count = $count;
  }

  public function getCount() {
    return $this->count;
  }
  
  public function setUpdated($updated) {
    $this->updated = $updated;
  }

  public function getUpdated() {
    return $this->updated;
  }
  
  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setHeight($height) {
    $this->height = $height;
  }

  public function getHeight() {
    return $this->height;
  }
  
  public function setWidth($width) {
    $this->width = $width;
  }

  public function getWidth() {
    return $this->width;
  }
  
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
  
}


class ActivityObjectLinksItems extends apiModel {

  public $href;
  public $type;

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


class Related extends apiModel {

  public $title;
  public $kind;
  public $href;
  public $id;
  public $summary;

  public function setTitle($title) {
    $this->title = $title;
  }

  public function getTitle() {
    return $this->title;
  }
  
  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
  public function setHref($href) {
    $this->href = $href;
  }

  public function getHref() {
    return $this->href;
  }
  
  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setSummary($summary) {
    $this->summary = $summary;
  }

  public function getSummary() {
    return $this->summary;
  }
  
}


class CountFeed extends apiModel {

  public $counts;
  public $kind;

  public function setCounts(CountFeedCountsItems $counts) {
    $this->counts = $counts;
  }

  public function getCounts() {
    return $this->counts;
  }
  
  public function setKind($kind) {
    $this->kind = $kind;
  }

  public function getKind() {
    return $this->kind;
  }
  
}


class ChiliPhotosResourceJsonAlbum extends apiModel {

  public $id;
  public $page_link;

  public function setId($id) {
    $this->id = $id;
  }

  public function getId() {
    return $this->id;
  }
  
  public function setPage_link(Link $page_link) {
    $this->page_link = $page_link;
  }

  public function getPage_link() {
    return $this->page_link;
  }
  
}


class Video extends apiModel {

  public $duration;
  public $status;
  public $streams;
  public $size;

  public function setDuration($duration) {
    $this->duration = $duration;
  }

  public function getDuration() {
    return $this->duration;
  }
  
  public function setStatus($status) {
    $this->status = $status;
  }

  public function getStatus() {
    return $this->status;
  }
  
  public function setStreams(Link $streams) {
    $this->streams = $streams;
  }

  public function getStreams() {
    return $this->streams;
  }
  
  public function setSize($size) {
    $this->size = $size;
  }

  public function getSize() {
    return $this->size;
  }
  
}


class ActivityObjectAttachments extends apiModel {

  public $content;
  public $type;
  public $id;
  public $links;
  public $title;

  public function setContent($content) {
    $this->content = $content;
  }

  public function getContent() {
    return $this->content;
  }
  
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
  
  public function setLinks(ActivityObjectAttachmentsLinksItems $links) {
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
  
}


class ActivityObject extends apiModel {

  public $targetLang;
  public $liked;
  public $attachments;
  public $links;
  public $originalContent;
  public $actor;
  public $shareOriginal;
  public $content;
  public $detectedlLang;
  public $comments;
  public $type;
  public $id;
  public $untranslatedContent;

  public function setTargetLang($targetLang) {
    $this->targetLang = $targetLang;
  }

  public function getTargetLang() {
    return $this->targetLang;
  }
  
  public function setLiked(Person $liked) {
    $this->liked = $liked;
  }

  public function getLiked() {
    return $this->liked;
  }
  
  public function setAttachments(ActivityObjectAttachments $attachments) {
    $this->attachments = $attachments;
  }

  public function getAttachments() {
    return $this->attachments;
  }
  
  public function setLinks(ActivityObjectLinksItems $links) {
    $this->links = $links;
  }

  public function getLinks() {
    return $this->links;
  }
  
  public function setOriginalContent($originalContent) {
    $this->originalContent = $originalContent;
  }

  public function getOriginalContent() {
    return $this->originalContent;
  }
  
  public function setActor(ActivityObjectActor $actor) {
    $this->actor = $actor;
  }

  public function getActor() {
    return $this->actor;
  }
  
  public function setShareOriginal(Activity $shareOriginal) {
    $this->shareOriginal = $shareOriginal;
  }

  public function getShareOriginal() {
    return $this->shareOriginal;
  }
  
  public function setContent($content) {
    $this->content = $content;
  }

  public function getContent() {
    return $this->content;
  }
  
  public function setDetectedlLang($detectedlLang) {
    $this->detectedlLang = $detectedlLang;
  }

  public function getDetectedlLang() {
    return $this->detectedlLang;
  }
  
  public function setComments(Comment $comments) {
    $this->comments = $comments;
  }

  public function getComments() {
    return $this->comments;
  }
  
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
  
  public function setUntranslatedContent($untranslatedContent) {
    $this->untranslatedContent = $untranslatedContent;
  }

  public function getUntranslatedContent() {
    return $this->untranslatedContent;
  }
  
}


class ActivityLinksLiked extends apiModel {

  public $count;
  public $href;
  public $type;

  public function setCount($count) {
    $this->count = $count;
  }

  public function getCount() {
    return $this->count;
  }
  
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
  
}


class AlbumLinks extends apiModel {

  public $alternate;
  public $enclosure;

  public function setAlternate(Link $alternate) {
    $this->alternate = $alternate;
  }

  public function getAlternate() {
    return $this->alternate;
  }
  
  public function setEnclosure(Link $enclosure) {
    $this->enclosure = $enclosure;
  }

  public function getEnclosure() {
    return $this->enclosure;
  }
  
}


class CountFeedCountsItems extends apiModel {

  public $count;
  public $timestamp;

  public function setCount($count) {
    $this->count = $count;
  }

  public function getCount() {
    return $this->count;
  }
  
  public function setTimestamp($timestamp) {
    $this->timestamp = $timestamp;
  }

  public function getTimestamp() {
    return $this->timestamp;
  }
  
}


class GroupFeed extends apiModel {

  public $items;
  public $kind;
  public $links;

  public function setItems(Group $items) {
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
  
  public function setLinks(GroupFeedLinksItems $links) {
    $this->links = $links;
  }

  public function getLinks() {
    return $this->links;
  }
  
}


class PersonAddresses extends apiModel {

  public $locality;
  public $country;
  public $region;
  public $primary;
  public $formatted;
  public $streetAddress;
  public $postalCode;
  public $type;

  public function setLocality($locality) {
    $this->locality = $locality;
  }

  public function getLocality() {
    return $this->locality;
  }
  
  public function setCountry($country) {
    $this->country = $country;
  }

  public function getCountry() {
    return $this->country;
  }
  
  public function setRegion($region) {
    $this->region = $region;
  }

  public function getRegion() {
    return $this->region;
  }
  
  public function setPrimary($primary) {
    $this->primary = $primary;
  }

  public function getPrimary() {
    return $this->primary;
  }
  
  public function setFormatted($formatted) {
    $this->formatted = $formatted;
  }

  public function getFormatted() {
    return $this->formatted;
  }
  
  public function setStreetAddress($streetAddress) {
    $this->streetAddress = $streetAddress;
  }

  public function getStreetAddress() {
    return $this->streetAddress;
  }
  
  public function setPostalCode($postalCode) {
    $this->postalCode = $postalCode;
  }

  public function getPostalCode() {
    return $this->postalCode;
  }
  
  public function setType($type) {
    $this->type = $type;
  }

  public function getType() {
    return $this->type;
  }
  
}

