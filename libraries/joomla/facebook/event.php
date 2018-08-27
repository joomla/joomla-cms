<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Facebook API User class for the Joomla Platform.
 *
 * @link        http://developers.facebook.com/docs/reference/api/event/
 * @since       13.1
 * @deprecated  4.0  Use the `joomla/facebook` package via Composer instead
 */
class JFacebookEvent extends JFacebookObject
{
	/**
	 * Method to get information about an event visible to the current user. Requires authentication.
	 *
	 * @param   string  $event  The event id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getEvent($event)
	{
		return $this->get($event);
	}

	/**
	 * Method to get the event's wall. Requires authentication.
	 *
	 * @param   string   $event   The event id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getFeed($event, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($event, 'feed', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to post a link on event's feed which the current_user is or maybe attending. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $event    The event id.
	 * @param   string  $link     Link URL.
	 * @param   string  $message  Link message.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createLink($event, $link, $message = null)
	{
		// Set POST request parameters.
		$data = array();
		$data['link'] = $link;
		$data['message'] = $message;

		return $this->createConnection($event, 'feed', $data);
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
	 * Method to post on event's wall. Message or link parameter is required. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $event        The event id.
	 * @param   string  $message      Post message.
	 * @param   string  $link         Post URL.
	 * @param   string  $picture      Post thumbnail image (can only be used if link is specified)
	 * @param   string  $name         Post name (can only be used if link is specified).
	 * @param   string  $caption      Post caption (can only be used if link is specified).
	 * @param   string  $description  Post description (can only be used if link is specified).
	 * @param   array   $actions      Post actions array of objects containing name and link.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createPost($event, $message = null, $link = null, $picture = null, $name = null, $caption = null,
		$description = null, $actions = null)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;
		$data['link'] = $link;
		$data['name'] = $name;
		$data['caption'] = $caption;
		$data['description'] = $description;
		$data['actions'] = $actions;
		$data['picture'] = $picture;

		return $this->createConnection($event, 'feed', $data);
	}

	/**
	 * Method to delete a post. Note: you can only delete the post if it was created by the current user.
	 * Requires authentication and publish_stream permission.
	 *
	 * @param   string  $post  The Post ID.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deletePost($post)
	{
		return $this->deleteConnection($post);
	}

	/**
	 * Method to post a status message on behalf of the user on the event's wall. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $event    The event id.
	 * @param   string  $message  Status message content.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createStatus($event, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return $this->createConnection($event, 'feed', $data);
	}

	/**
	 * Method to delete a status. Note: you can only delete the post if it was created by the current user.
	 * Requires authentication and publish_stream permission.
	 *
	 * @param   string  $status  The Status ID.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deleteStatus($status)
	{
		return $this->deleteConnection($status);
	}

	/**
	 * Method to get the list of invitees for the event. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   string   $event   The event id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getInvited($event, $limit = 0, $offset = 0)
	{
		return $this->getConnection($event, 'invited', '', $limit, $offset);
	}

	/**
	 * Method to check if a user is invited to the event. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   string  $event  The event id.
	 * @param   mixed   $user   Either an integer containing the user ID or a string containing the username.
	 *
	 * @return  array   The decoded JSON response or an empty array if the user is not invited.
	 *
	 * @since   13.1
	 */
	public function isInvited($event, $user)
	{
		return $this->getConnection($event, 'invited/' . $user);
	}

	/**
	 * Method to invite users to the event. Requires authentication and create_event permission.
	 *
	 * @param   string  $event  The event id.
	 * @param   string  $users  Comma separated list of user ids.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function createInvite($event, $users)
	{
		// Set POST request parameters.
		$data = array();
		$data['users'] = $users;

		return $this->createConnection($event, 'invited', $data);
	}

	/**
	 * Method to delete an invitation. Note: you can only delete the invite if the current user is the event admin.
	 * Requires authentication and rsvp_event permission.
	 *
	 * @param   string  $event  The event id.
	 * @param   string  $user   The user id.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deleteInvite($event, $user)
	{
		return $this->deleteConnection($event, 'invited/' . $user);
	}

	/**
	 * Method to get the list of attending users. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   string   $event   The event id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getAttending($event, $limit = 0, $offset = 0)
	{
		return $this->getConnection($event, 'attending', '', $limit, $offset);
	}

	/**
	 * Method to check if a user is attending an event. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   string  $event  The event id.
	 * @param   mixed   $user   Either an integer containing the user ID or a string containing the username.
	 *
	 * @return  array   The decoded JSON response or an empty array if the user is not invited.
	 *
	 * @since   13.1
	 */
	public function isAttending($event, $user)
	{
		return $this->getConnection($event, 'attending/' . $user);
	}

	/**
	 * Method to set the current user as attending. Requires authentication and rsvp_event permission.
	 *
	 * @param   string  $event  The event id.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function createAttending($event)
	{
		return $this->createConnection($event, 'attending');
	}

	/**
	 * Method to get the list of maybe attending users. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   string   $event   The event id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getMaybe($event, $limit = 0, $offset = 0)
	{
		return $this->getConnection($event, 'maybe', '', $limit, $offset);
	}

	/**
	 * Method to check if a user is maybe attending an event. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   string  $event  The event id.
	 * @param   mixed   $user   Either an integer containing the user ID or a string containing the username.
	 *
	 * @return  array   The decoded JSON response or an empty array if the user is not invited.
	 *
	 * @since   13.1
	 */
	public function isMaybe($event, $user)
	{
		return $this->getConnection($event, 'maybe/' . $user);
	}

	/**
	 * Method to set the current user as maybe attending. Requires authentication and rscp_event permission.
	 *
	 * @param   string  $event  The event id.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function createMaybe($event)
	{
		return $this->createConnection($event, 'maybe');
	}

	/**
	 * Method to get the list of users which declined the event. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   string   $event   The event id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getDeclined($event, $limit = 0, $offset = 0)
	{
		return $this->getConnection($event, 'declined', '', $limit, $offset);
	}

	/**
	 * Method to check if a user responded 'no' to the event. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   string  $event  The event id.
	 * @param   mixed   $user   Either an integer containing the user ID or a string containing the username.
	 *
	 * @return  array   The decoded JSON response or an empty array if the user is not invited.
	 *
	 * @since   13.1
	 */
	public function isDeclined($event, $user)
	{
		return $this->getConnection($event, 'declined/' . $user);
	}

	/**
	 * Method to set the current user as declined. Requires authentication and rscp_event permission.
	 *
	 * @param   string  $event  The event id.
	 *
	 * @return  boolean   Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function createDeclined($event)
	{
		return $this->createConnection($event, 'declined');
	}

	/**
	 * Method to get the list of users which have not replied to the event. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   string   $event   The event id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getNoreply($event, $limit = 0, $offset = 0)
	{
		return $this->getConnection($event, 'noreply', '', $limit, $offset);
	}

	/**
	 * Method to check if a user has not replied to the event. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   string  $event  The event id.
	 * @param   mixed   $user   Either an integer containing the user ID or a string containing the username.
	 *
	 * @return  array   The decoded JSON response or an empty array if the user is not invited.
	 *
	 * @since   13.1
	 */
	public function isNoreply($event, $user)
	{
		return $this->getConnection($event, 'noreply/' . $user);
	}

	/**
	 * Method to get the event's profile picture. Requires authentication and user_events or friends_events permission.
	 *
	 * @param   string   $event     The event id.
	 * @param   boolean  $redirect  If false this will return the URL of the picture without a 302 redirect.
	 * @param   string   $type      To request a different photo use square | small | normal | large.
	 *
	 * @return  string   The URL to the event's profile picture.
	 *
	 * @since   13.1
	 */
	public function getPicture($event, $redirect = true, $type = null)
	{
		$extra_fields = '';

		if ($redirect == false)
		{
			$extra_fields = '?redirect=false';
		}

		if ($type)
		{
			$extra_fields .= (strpos($extra_fields, '?') === false) ? '?type=' . $type : '&type=' . $type;
		}

		return $this->getConnection($event, 'picture', $extra_fields);
	}

	/**
	 * Method to get photos published on event's wall. Requires authentication.
	 *
	 * @param   string   $event   The event id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getPhotos($event, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($event, 'photos', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to post a photo on event's wall. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $event    The event id.
	 * @param   string  $source   Path to photo.
	 * @param   string  $message  Photo description.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createPhoto($event, $source, $message = null)
	{
		// Set POST request parameters.
		$data = array();
		$data[basename($source)] = '@' . realpath($source);

		if ($message)
		{
			$data['message'] = $message;
		}

		return $this->createConnection($event, 'photos', $data, array('Content-Type' => 'multipart/form-data'));
	}

	/**
	 * Method to get videos published on event's wall. Requires authentication.
	 *
	 * @param   string   $event   The event id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getVideos($event, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($event, 'videos', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to post a video on event's wall. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $event        The event id.
	 * @param   string  $source       Path to photo.
	 * @param   string  $title        Video title.
	 * @param   string  $description  Video description.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createVideo($event, $source, $title = null, $description = null)
	{
		// Set POST request parameters.
		$data = array();
		$data[basename($source)] = '@' . realpath($source);

		if ($title)
		{
			$data['title'] = $title;
		}

		if ($description)
		{
			$data['description'] = $description;
		}

		return $this->createConnection($event, 'videos', $data, array('Content-Type' => 'multipart/form-data'));
	}
}
