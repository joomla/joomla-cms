<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('JPATH_PLATFORM') or die();


/**
 * Facebook API User class for the Joomla Platform.
 *
 * @see    http://developers.facebook.com/docs/reference/api/user/
 * @since  13.1
 */
class JFacebookUser extends JFacebookObject
{
	/**
	 * Method to get the specified user's details. Authentication is required only for some fields.
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the username.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getUser($user)
	{
		return $this->get($user);
	}

	/**
	 * Method to get the specified user's friends. Requires authentication.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getFriends($user, $limit = 0, $offset = 0)
	{
		return $this->getConnection($user, 'friends', '', $limit, $offset);
	}

	/**
	 * Method to get the user's incoming friend requests. Requires authentication and read_requests permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getFriendRequests($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'friendrequests', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the user's friend lists. Requires authentication and read_friendlists permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getFriendLists($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'friendlists', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the user's wall. Requires authentication and read_stream permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getFeed($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'feed', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the user's news feed. Requires authentication and read_stream permission.
	 *
	 * @param   mixed    $user      Either an integer containing the user ID or a string containing the username.
	 * @param   string   $filter    User's stream filter.
	 * @param   boolean  $location  Retreive only posts with a location attached.
	 * @param   integer  $limit     The number of objects per page.
	 * @param   integer  $offset    The object's number on the page.
	 * @param   string   $until     A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since     A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getHome($user, $filter = null, $location = false, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		$extra_fields = '';

		if ($filter != null)
		{
			$extra_fields = '?filter=' . $filter;
		}

		if ($location == true)
		{
			$extra_fields .= (strpos($extra_fields, '?') === false) ? '?with=location' : '&with=location';
		}

		return $this->getConnection($user, 'home', $extra_fields, $limit, $offset, $until, $since);
	}

	/**
	 * Method to see if a user is a friend of the current user. Requires authentication.
	 *
	 * @param   mixed  $current_user  Either an integer containing the user ID or a string containing the username for the current user.
	 * @param   mixed  $user          Either an integer containing the user ID or a string containing the username for the user.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function hasFriend($current_user, $user)
	{
		return $this->getConnection($current_user, 'friends/' . $user);
	}

	/**
	 * Method to get mutual friends of one user and the current user. Requires authentication.
	 *
	 * @param   mixed    $current_user  Either an integer containing the user ID or a string containing the username for the current user.
	 * @param   mixed    $user          Either an integer containing the user ID or a string containing the username for the user.
	 * @param   integer  $limit         The number of objects per page.
	 * @param   integer  $offset        The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getMutualFriends($current_user, $user, $limit = 0, $offset = 0)
	{
		return $this->getConnection($current_user, 'mutualfriends/' . $user, '', $limit, $offset);
	}

	/**
	 * Method to get the user's profile picture. Requires authentication.
	 *
	 * @param   mixed    $user      Either an integer containing the user ID or a string containing the username.
	 * @param   boolean  $redirect  If false this will return the URL of the profile picture without a 302 redirect.
	 * @param   string   $type      To request a different photo use square | small | normal | large.
	 *
	 * @return  string   The URL to the user's profile picture.
	 *
	 * @since   13.1
	 */
	public function getPicture($user, $redirect = true, $type = null)
	{
		$extra_fields = '';

		if ($redirect == false)
		{
			$extra_fields = '?redirect=false';
		}

		if ($type != null)
		{
			$extra_fields .= (strpos($extra_fields, '?') === false) ? '?type=' . $type : '&type=' . $type;
		}

		return $this->getConnection($user, 'picture', $extra_fields);
	}

	/**
	 * Method to get the user's family relationships. Requires authentication and user_relationships permission..
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getFamily($user, $limit = 0, $offset = 0)
	{
		return $this->getConnection($user, 'family', '', $limit, $offset);
	}

	/**
	 * Method to get the user's notifications. Requires authentication and manage_notifications permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   boolean  $read    Enables you to see notifications that the user has already read.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getNotifications($user, $read = null, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		if ($read == true)
		{
			$read = '?include_read=1';
		}

		// Send the request.
		return $this->getConnection($user, 'notifications', $read, $limit, $offset, $until, $since);
	}

	/**
	 * Method to mark a notification as read. Requires authentication and manage_notifications permission.
	 *
	 * @param   string  $notification  The notification id.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function updateNotification($notification)
	{
		$data['unread'] = 0;

		return $this->createConnection($notification, null, $data);
	}

	/**
	 * Method to get the user's permissions. Requires authentication.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getPermissions($user, $limit = 0, $offset = 0)
	{
		return $this->getConnection($user, 'permissions', '', $limit, $offset);
	}

	/**
	 * Method to revoke a specific permission on behalf of a user. Requires authentication.
	 *
	 * @param   mixed   $user        Either an integer containing the user ID or a string containing the username.
	 * @param   string  $permission  The permission to revoke. If none specified, then this will de-authorize the application completely.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function deletePermission($user, $permission = '')
	{
		return $this->deleteConnection($user, 'permissions', '?permission=' . $permission);
	}

	/**
	 * Method to get the user's albums. Requires authentication and user_photos or friends_photos permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getAlbums($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'albums', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to create an album for a user.  Requires authentication and publish_stream permission.
	 *
	 * @param   mixed   $user         Either an integer containing the user ID or a string containing the username.
	 * @param   string  $name         Album name.
	 * @param   string  $description  Album description.
	 * @param   json    $privacy      A JSON-encoded object that defines the privacy setting for the album.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createAlbum($user, $name, $description = null, $privacy = null)
	{
		// Set POST request parameters.
		$data = array();
		$data['name'] = $name;
		$data['description'] = $description;
		$data['privacy'] = $privacy;

		return $this->createConnection($user, 'albums', $data);
	}

	/**
	 * Method to get the user's checkins. Requires authentication and user_checkins or friends_checkins permission
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getCheckins($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'checkins', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to create a checkin for a user. Requires authentication and publish_checkins permission.
	 *
	 * @param   mixed   $user         Either an integer containing the user ID or a string containing the username.
	 * @param   string  $place        Id of the Place Page.
	 * @param   string  $coordinates  A JSON-encoded string containing latitute and longitude.
	 * @param   string  $tags         Comma separated list of USER_IDs.
	 * @param   string  $message      A message to add to the checkin.
	 * @param   string  $link         A link to add to the checkin.
	 * @param   string  $picture      A picture to add to the checkin.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createCheckin($user, $place, $coordinates, $tags = null, $message = null, $link = null, $picture = null)
	{
		// Set POST request parameters.
		$data = array();
		$data['place'] = $place;
		$data['coordinates'] = $coordinates;
		$data['tags'] = $tags;
		$data['message'] = $message;
		$data['link'] = $link;
		$data['picture'] = $picture;

		return $this->createConnection($user, 'checkins', $data);
	}

	/**
	 * Method to get the user's likes. Requires authentication and user_likes or friends_likes permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getLikes($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'likes', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to see if a user likes a specific Page. Requires authentication.
	 *
	 * @param   mixed   $user  Either an integer containing the user ID or a string containing the username.
	 * @param   string  $page  Facebook ID of the Page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function likesPage($user, $page)
	{
		return $this->getConnection($user, 'likes/' . $page);
	}

	/**
	 * Method to get the current user's events. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getEvents($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'events', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to create an event for a user. Requires authentication create_event permission.
	 *
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * @param   string  $name          Event name.
	 * @param   string  $start_time    Event start time as UNIX timestamp.
	 * @param   string  $end_time      Event end time as UNIX timestamp.
	 * @param   string  $description   Event description.
	 * @param   string  $location      Event location.
	 * @param   string  $location_id   Facebook Place ID of the place the Event is taking place.
	 * @param   string  $privacy_type  Event privacy setting, a string containing 'OPEN' (default), 'CLOSED', or 'SECRET'.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createEvent($user, $name, $start_time, $end_time = null, $description = null,
		$location = null, $location_id = null, $privacy_type = null)
	{
		// Set POST request parameters.
		$data = array();
		$data['start_time'] = $start_time;
		$data['name'] = $name;
		$data['end_time'] = $end_time;
		$data['description'] = $description;
		$data['location'] = $location;
		$data['location_id'] = $location_id;
		$data['privacy_type'] = $privacy_type;

		return $this->createConnection($user, 'events', $data);
	}

	/**
	 * Method to edit an event. Requires authentication create_event permission.
	 *
	 * @param   mixed   $event         Event ID.
	 * @param   string  $name          Event name.
	 * @param   string  $start_time    Event start time as UNIX timestamp.
	 * @param   string  $end_time      Event end time as UNIX timestamp.
	 * @param   string  $description   Event description.
	 * @param   string  $location      Event location.
	 * @param   string  $location_id   Facebook Place ID of the place the Event is taking place.
	 * @param   string  $privacy_type  Event privacy setting, a string containing 'OPEN' (default), 'CLOSED', or 'SECRET'.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function editEvent($event, $name = null, $start_time = null, $end_time = null, $description = null,
		$location = null, $location_id = null, $privacy_type = null)
	{
		// Set POST request parameters.
		$data = array();
		$data['start_time'] = $start_time;
		$data['name'] = $name;
		$data['end_time'] = $end_time;
		$data['description'] = $description;
		$data['location'] = $location;
		$data['location_id'] = $location_id;
		$data['privacy_type'] = $privacy_type;

		return $this->createConnection($event, null, $data);
	}

	/**
	 * Method to delete an event. Note: you can only delete the event if it was created by the same app. Requires authentication create_event permission.
	 *
	 * @param   string  $event  Event ID.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deleteEvent($event)
	{
		return $this->deleteConnection($event);
	}

	/**
	 * Method to get the groups that the user belongs to. Requires authentication and user_groups or friends_groups permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getGroups($user, $limit = 0, $offset = 0)
	{
		return $this->getConnection($user, 'groups', '', $limit, $offset);
	}

	/**
	 * Method to get the user's posted links. Requires authentication and user_groups or friends_groups permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getLinks($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'links', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to post a link on user's feed. Requires authentication and publish_stream permission.
	 *
	 * @param   mixed   $user     Either an integer containing the user ID or a string containing the username.
	 * @param   string  $link     Link URL.
	 * @param   strin   $message  Link message.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createLink($user, $link, $message = null)
	{
		// Set POST request parameters.
		$data = array();
		$data['link'] = $link;
		$data['message'] = $message;

		return $this->createConnection($user, 'feed', $data);
	}

	/**
	 * Method to delete a link. Requires authentication and publish_stream permission.
	 *
	 * @param   mixed  $link  The Link ID.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deleteLink($link)
	{
		return $this->deleteConnection($link);
	}

	/**
	 * Method to get the user's notes. Requires authentication and user_groups or friends_groups permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getNotes($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'notes', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to create a note on the behalf of the user.
	 * Requires authentication and publish_stream permission, user_groups or friends_groups permission.
	 *
	 * @param   mixed   $user     Either an integer containing the user ID or a string containing the username.
	 * @param   string  $subject  The subject of the note.
	 * @param   string  $message  Note content.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createNote($user, $subject, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['subject'] = $subject;
		$data['message'] = $message;

		return $this->createConnection($user, 'notes', $data);
	}

	/**
	 * Method to get the user's photos. Requires authentication and user_groups or friends_groups permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getPhotos($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'photos', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to post a photo on user's wall. Requires authentication and publish_stream permission, user_groups or friends_groups permission.
	 *
	 * @param   mixed    $user      Either an integer containing the user ID or a string containing the username.
	 * @param   string   $source    Path to photo.
	 * @param   string   $message   Photo description.
	 * @param   string   $place     Facebook ID of the place associated with the photo.
	 * @param   boolean  $no_story  If set to 1, optionally suppresses the feed story that is automatically
	 * 								generated on a user’s profile when they upload a photo using your application.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createPhoto($user, $source, $message = null, $place = null, $no_story = null)
	{
		// Set POST request parameters.
		$data = array();
		$data[basename($source)] = '@' . realpath($source);
		$data['message'] = $message;
		$data['place'] = $place;
		$data['no_story'] = $no_story;

		return $this->createConnection($user, 'photos', $data, array('Content-Type' => 'multipart/form-data'));
	}

	/**
	 * Method to get the user's posts. Requires authentication and read_stream permission for non-public posts.
	 *
	 * @param   mixed    $user      Either an integer containing the user ID or a string containing the username.
	 * @param   boolean  $location  Retreive only posts with a location attached.
	 * @param   integer  $limit     The number of objects per page.
	 * @param   integer  $offset    The object's number on the page.
	 * @param   string   $until     A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since     A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getPosts($user, $location = false, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		if ($location == true)
		{
			$location = '?with=location';
		}

		// Send the request.
		return $this->getConnection($user, 'posts', $location, $limit, $offset, $until, $since);
	}

	/**
	 * Method to post on a user's wall. Message or link parameter is required. Requires authentication and publish_stream permission.
	 *
	 * @param   mixed   $user               Either an integer containing the user ID or a string containing the username.
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
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createPost($user, $message = null, $link = null, $picture = null, $name = null, $caption = null,
		$description = null, $actions = null, $place = null, $tags = null, $privacy = null, $object_attachment = null)
	{
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

		return $this->createConnection($user, 'feed', $data);
	}

	/**
	 * Method to delete a post. Note: you can only delete the post if it was created by the current user. Requires authentication
	 *
	 * @param   string  $post  The Post ID.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function deletePost($post)
	{
		return $this->deleteConnection($post);
	}

	/**
	 * Method to get the user's statuses. Requires authentication read_stream permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getStatuses($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'statuses', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to post a status message on behalf of the user. Requires authentication publish_stream permission.
	 *
	 * @param   mixed   $user     Either an integer containing the user ID or a string containing the username.
	 * @param   string  $message  Status message content.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createStatus($user, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return $this->createConnection($user, 'feed', $data);
	}

	/**
	 * Method to delete a status. Note: you can only delete the post if it was created by the current user.
	 * Requires authentication publish_stream permission.
	 *
	 * @param   string  $status  The Status ID.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function deleteStatus($status)
	{
		return $this->deleteConnection($status);
	}

	/**
	 * Method to get the videos the user has been tagged in. Requires authentication and user_videos or friends_videos permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getVideos($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'videos', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to post a video on behalf of the user. Requires authentication and publish_stream permission.
	 *
	 * @param   mixed   $user         Either an integer containing the user ID or a string containing the username.
	 * @param   string  $source       Path to video.
	 * @param   string  $title        Video title.
	 * @param   string  $description  Video description.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createVideo($user, $source, $title = null, $description = null)
	{
		// Set POST request parameters.
		$data = array();
		$data[basename($source)] = '@' . realpath($source);
		$data['title'] = $title;
		$data['description'] = $description;

		return $this->createConnection($user, 'videos', $data, array('Content-Type' => 'multipart/form-data'));
	}

	/**
	 * Method to get the posts the user has been tagged in. Requires authentication and user_videos or friends_videos permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getTagged($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'tagged', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the activities listed on the user's profile. Requires authentication and user_activities or friends_activities permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getActivities($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'activities', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the books listed on the user's profile. Requires authentication and user_likes or friends_likes permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getBooks($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'books', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the interests listed on the user's profile. Requires authentication.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getInterests($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'interests', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the movies listed on the user's profile. Requires authentication and user_likes or friends_likes permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getMovies($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'movies', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the television listed on the user's profile. Requires authentication and user_likes or friends_likes permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getTelevision($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'television', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the music listed on the user's profile. Requires authentication user_likes or friends_likes permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getMusic($user, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($user, 'music', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to get the user's subscribers. Requires authentication and user_subscriptions or friends_subscriptions permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getSubscribers($user, $limit = 0, $offset = 0)
	{
		return $this->getConnection($user, 'subscribers', '', $limit, $offset);
	}

	/**
	 * Method to get the people the user is subscribed to. Requires authentication and user_subscriptions or friends_subscriptions permission.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the username.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getSubscribedTo($user, $limit = 0, $offset = 0)
	{
		return $this->getConnection($user, 'subscribedto', '', $limit, $offset);
	}
}
