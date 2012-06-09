<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('JPATH_PLATFORM') or die();


/**
 * Facebook API User class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @since       12.1
 */
class JFacebookUser extends JFacebookObject
{
	/**
	 * Method to get the specified user's details
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token. For some fields more user or friends permissions are needed.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getUser($user, $access_token=null)
	{
		if ($access_token != null)
		{
			$token = '?access_token=' . $access_token;
		}
		else
		{
			$token = '';
		}

		$path = $user . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the specified user's friends.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getFriends($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/friends' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the user's incoming friend requests.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with read_requests permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getFriendRequests($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/friendrequests' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the user's friend lists.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with read_friendlists permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getFriendLists($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/friendlists' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the user's wall.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with read_stream permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getFeed($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/feed' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to see if a user is a friend of the current user.
	 *
	 * @param   mixed   $current_user  Either an integer containing the user ID or a string containing the username for the current user.
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username for the user.
	 * @param   string  $access_token  The Facebook access token with read_stream permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function hasFriend($current_user, $user, $access_token)
	{
		$token = '?access_token=' . $access_token;
		$friend = '/friends/' . $user;

		// Build the request path.
		$path = $current_user . $friend . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get mutual friends of one user and the current user.
	 *
	 * @param   mixed   $current_user  Either an integer containing the user ID or a string containing the username for the current user.
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username for the user.
	 * @param   string  $access_token  The Facebook access token.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getMutualFriends($current_user, $user, $access_token)
	{
		$token = '?access_token=' . $access_token;
		$friend = '/mutualfriends/' . $user;

		// Build the request path.
		$path = $current_user . $friend . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the user's profile picture.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token.
	 * @param   string  $type          To request a different photo use square | small | normal | large.
	 *
	 * @return  string   The URL to the user's profile picture.
	 *
	 * @since   12.1
	 */
	public function getPicture($user, $access_token=null, $type=null)
	{
		if ($access_token != null)
		{
			$token = '?access_token=' . $access_token;
		}
		else
		{
			$token = '';
		}

		if ($type != null)
		{
			$type = '&type=' . $type;
		}
		else
		{
			$type = '';
		}

		// Build the request path.
		$path = $user . '/picture' . $token . $type;

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		return $response->headers['Location'];
	}

	/**
	 * Method to get the user's family relationships.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_relationships permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getFamily($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/family' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the user's notifications.
	 *
	 * @param   mixed    $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string   $access_token  The Facebook access token with manage_notifications permission.
	 * @param   boolean  $read          Enables you to see notifications that the user has already read.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getNotifications($user, $access_token, $read=null)
	{
		if ($read == true)
		{
			$token = '&access_token=' . $access_token;

			// Build the request path.
			$path = $user . '/notifications?include_read=1' . $token;
		}
		else
		{
			$token = '?access_token=' . $access_token;

			// Build the request path.
			$path = $user . '/notifications' . $token;
		}

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to mark a notification as read.
	 *
	 * @param   string  $notification  The notification id.
	 * @param   string  $access_token  The Facebook access token with manage_notifications permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function updateNotification($notification, $access_token)
	{
		$token = '&access_token=' . $access_token;

		// Build the request path.
		$path = $notification . '?unread=0' . $token;

		// Send the post request.
		return $this->sendRequest($path, 'post');
	}

	/**
	 * Method to get the user's permissions.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getPermissions($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/permissions' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to revoke a specific permission on behalf of a user.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token.
	 * @param   string  $permission    The permission to revoke. If none specified, then this will de-authorize the application completely.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function deletePermission($user, $access_token, $permission='')
	{
		$permissions = '/permissions?permission=' . $permission;
		$token = '&access_token=' . $access_token;

		// Build the request path.
		$path = $user . $permissions . $token;

		// Send the delete request.
		return $this->sendRequest($path, 'delete');
	}

	/**
	 * Method to get the user's albums.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_photos or friends_photos permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getAlbums($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/albums' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to create an album for a user.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with publish_stream  permission.
	 * @param   string  $name          Album name.
	 * @param   string  $description   Album description.
	 * @param   json    $privacy       A JSON-encoded object that defines the privacy setting for the album.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function createAlbum($user, $access_token, $name, $description=null, $privacy=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/albums' . $token;

		// Set POST request parameters.
		$data = array();
		$data['name'] = $name;
		$data['description'] = $description;
		$data['privacy'] = $privacy;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data);
	}

	/**
	 * Method to get the user's checkins.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_checkins or friends_checkins permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getCheckins($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/checkins' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to create a checkin for a user.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with publish_checkins permission.
	 * @param   string  $place         Id of the Place Page.
	 * @param   json    $coordinates   A JSON-encoded object containing latitute and longitude.
	 * @param   string  $tags          Comma separated list of USER_IDs.
	 * @param   string  $message       A message to add to the checkin.
	 * @param   string  $link          A link to add to the checkin.
	 * @param   string  $picture       A picture to add to the checkin.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function createCheckin($user, $access_token, $place, $coordinates, $tags=null, $message=null, $link=null, $picture=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/checkins' . $token;

		// Set POST request parameters.
		$data = array();
		$data['place'] = $place;
		$data['coordinates'] = $coordinates;
		$data['tags'] = $tags;
		$data['message'] = $message;
		$data['link'] = $link;
		$data['picture'] = $picture;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data);
	}

	/**
	 * Method to get the user's likes.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_likes or friends_likes permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getLikes($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/likes' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to see if a user likes a specific Page.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token.
	 * @param   string  $page          Facebook ID of the Page.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function likesPage($user, $access_token, $page)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/likes/' . $page . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the current user's events.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getEvents($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/events' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to create an event for a user.
	 *
	 * @param   mixed      $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string     $access_token  The Facebook access token with the create_event permission.
	 * @param   string     $name          Event name.
	 * @param   timestamp  $start_time    Event start time as UNIX timestamp.
	 * @param   timestamp  $end_time      Event end time as UNIX timestamp.
	 * @param   string     $description   Event description.
	 * @param   string     $location      Event location.
	 * @param   string     $location_id   Facebook Place ID of the place the Event is taking place.
	 * @param   string     $privacy_type  Event privacy setting, a string containing 'OPEN' (default), 'CLOSED', or 'SECRET'.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function createEvent($user, $access_token, $name, $start_time, $end_time=null, $description=null,
		$location=null, $location_id=null, $privacy_type=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/events' . $token;

		// Set POST request parameters.
		$data = array();
		$data['start_time'] = $start_time;
		$data['name'] = $name;
		$data['end_time'] = $end_time;
		$data['description'] = $description;
		$data['location'] = $location;
		$data['location_id'] = $location_id;
		$data['privacy_type'] = $privacy_type;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data);
	}

	/**
	 * Method to edit an event.
	 *
	 * @param   mixed      $event         Event ID.
	 * @param   string     $access_token  The Facebook access token with the create_event permission.
	 * @param   string     $name          Event name.
	 * @param   timestamp  $start_time    Event start time as UNIX timestamp.
	 * @param   timestamp  $end_time      Event end time as UNIX timestamp.
	 * @param   string     $description   Event description.
	 * @param   string     $location      Event location.
	 * @param   string     $location_id   Facebook Place ID of the place the Event is taking place.
	 * @param   string     $privacy_type  Event privacy setting, a string containing 'OPEN' (default), 'CLOSED', or 'SECRET'.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function editEvent($event, $access_token, $name=null, $start_time=null, $end_time=null, $description=null,
		$location=null, $location_id=null, $privacy_type=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . $token;

		// Set POST request parameters.
		$data = array();
		$data['start_time'] = $start_time;
		$data['name'] = $name;
		$data['end_time'] = $end_time;
		$data['description'] = $description;
		$data['location'] = $location;
		$data['location_id'] = $location_id;
		$data['privacy_type'] = $privacy_type;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data);
	}

	/**
	 * Method to delete an event. Note: you can only delete the event if it was created by the same app.
	 *
	 * @param   string  $event         Event ID.
	 * @param   string  $access_token  The Facebook access token with the create_event permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function deleteEvent($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . $token;

		// Send the delete request.
		return $this->sendRequest($path, 'delete');
	}

	/**
	 * Method to get the groups that the user belongs to.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_groups or friends_groups permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getGroups($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/groups' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the user's posted links.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_groups or friends_groups permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getLinks($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/links' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to post a link on user's feed.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with publish_stream  permission.
	 * @param   string  $link          Link URL.
	 * @param   strin   $message       Link message.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function createLink($user, $access_token, $link, $message=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/feed' . $token;

		// Set POST request parameters.
		$data = array();
		$data['link'] = $link;
		$data['message'] = $message;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data);
	}

	/**
	 * Method to delete a link.
	 *
	 * @param   mixed   $link          The Link ID.
	 * @param   string  $access_token  The Facebook access token.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function deleteLink($link, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $link . $token;

		// Send the delete request.
		return $this->sendRequest($path, 'delete');
	}

	/**
	 * Method to get the user's notes.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_groups or friends_groups permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getNotes($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/notes' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to create a note on the behalf of the user.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with publish_stream  permission.
	 * @param   string  $subject       The subject of the note.
	 * @param   strin   $message       Note content.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function createNote($user, $access_token, $subject, $message)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/notes' . $token;

		// Set POST request parameters.
		$data = array();
		$data['subject'] = $subject;
		$data['message'] = $message;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data);
	}

	/**
	 * Method to get the user's photos.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_groups or friends_groups permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getPhotos($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/photos' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to post a photo on user's wall.
	 *
	 * @param   mixed    $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string   $access_token  The Facebook access token with publish_stream  permission.
	 * @param   string   $source        Path to photo.
	 * @param   string   $message       Photo description.
	 * @param   string   $place         Facebook ID of the place associated with the photo.
	 * @param   boolean  $no_story      If set to 1, optionally suppresses the feed story that is automatically
	 * 									generated on a user’s profile when they upload a photo using your application.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function createPhoto($user, $access_token, $source, $message=null, $place=null, $no_story=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/photos' . $token;

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;
		$data['place'] = $place;
		$data['no_story'] = $no_story;
		$data[basename($source)] = '@' . realpath($source);

		// Send the post request.
		return $this->sendRequest($path, 'post', $data, array('Content-type' => 'multipart/form-data'));
	}

	/**
	 * Method to get the user's posts.
	 *
	 * @param   mixed    $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string   $access_token  The Facebook access token with read_stream permission for non-public posts.
	 * @param   boolean  $location      Retreive only posts with a location attached.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getPosts($user, $access_token, $location=false)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/posts' . $token;

		if ($location == true)
		{
			$path .= '&with=location';
		}

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to post on a user's wall. Message or link parameter is required.
	 *
	 * @param   mixed   $user               Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token       The Facebook access token with the publish_stream permission.
	 * @param   string  $message            Post message.
	 * @param   string  $link               Post URL.
	 * @param   string  $picture            Post thumbnail image (can only be used if link is specified)
	 * @param   string  $name               Post name (can only be used if link is specified).
	 * @param   string  $caption            Post caption (can only be used if link is specified).
	 * @param   string  $description        Post description (can only be used if link is specified).
	 * @param   array   $actions            Post actions array of objects containing name and link.
	 * @param   string  $place              Facebook Page ID of the location associated with this Post.
	 * @param   string  $tags               Comma-separated list of Facebook IDs of people tagged in this Post.
	 * 										For example: 1207059,701732. You cannot specify this field without also specifying a place.
	 * @param   string  $privacy            Post privacy settings (can only be specified if the Timeline being posted
	 * 										on belongs to the User creating the Post).
	 * @param   string  $object_attachment  Facebook ID for an existing picture in the User's photo albums to use as the thumbnail image.
	 *                                      The User must be the owner of the photo, and the photo cannot be part of a message attachment.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function createPost($user, $access_token, $message=null, $link=null, $picture=null, $name=null, $caption=null,
		$description=null, $actions=null, $place=null, $tags=null, $privacy=null, $object_attachment=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/feed' . $token;

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;
		$data['link'] = $link;
		$data['name'] = $name;
		$data['caption'] = $caption;
		$data['description'] = $description;
		$data['actions'] = $actions;
		$data['place'] = $place;
		$data['tags'] = $tags;
		$data['privacy'] = $privacy;
		$data['object_attachment'] = $object_attachment;
		$data['picture'] = $picture;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data);
	}

	/**
	 * Method to delete a post. Note: you can only delete the post if it was created by the current user.
	 *
	 * @param   string  $post          The Post ID.
	 * @param   string  $access_token  The Facebook access token.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function deletePost($post, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $post . $token;

		// Send the delete request.
		return $this->sendRequest($path, 'delete');
	}

	/**
	 * Method to get the user's statuses.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with read_stream permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getStatuses($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/statuses' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to post a status message on behalf of the user.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with publish_stream permission.
	 * @param   strin   $message       Status message content.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function createStatus($user, $access_token, $message)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/feed' . $token;

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data);
	}

	/**
	 * Method to delete a status. Note: you can only delete the post if it was created by the current user.
	 *
	 * @param   string  $status        The Status ID.
	 * @param   string  $access_token  The Facebook access token.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function deleteStatus($status, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $status . $token;

		// Send the delete request.
		return $this->sendRequest($path, 'delete');
	}

	/**
	 * Method to get the videos the user has been tagged in.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_videos or friends_videos permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getVideos($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/videos' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to post a video on behalf of the user.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with publish_stream permission.
	 * @param   string  $source        Path to video.
	 * @param   string  $title         Video title.
	 * @param   string  $description   Video description.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function createVideo($user, $access_token, $source, $title=null, $description=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/videos' . $token;

		// Set POST request parameters.
		$data = array();
		$data[basename($source)] = '@' . realpath($source);
		$data['title'] = $title;
		$data['description'] = $description;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data, array('Content-type' => 'multipart/form-data'));
	}

	/**
	 * Method to get the posts the user has been tagged in.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_videos or friends_videos permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getTagged($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/tagged' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the activities listed on the user's profile.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_activities or friends_activities permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getActivities($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/activities' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the books listed on the user's profile.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_likes or friends_likes permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getBooks($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/books' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the interests listed on the user's profile.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_interests or friends_interests permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getInterests($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/interests' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the movies listed on the user's profile.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_likes or friends_likes permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getMovies($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/movies' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the television listed on the user's profile.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_likes or friends_likes permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getTelevision($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/television' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the music listed on the user's profile.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_likes or friends_likes permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getMusic($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/music' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the user's subscribers.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_subscriptions or friends_subscriptions permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getSubscribers($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/subscribers' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the people the user is subscribed to.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $access_token  The Facebook access token with user_subscriptions or friends_subscriptions permission.
	 *
	 * @return  array   The decoded JSON response.
	 *
	 * @since   12.1
	 */
	public function getSubscribedTo($user, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $user . '/subscribedto' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}
}
