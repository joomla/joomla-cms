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
class JFacebookEvent extends JFacebookObject
{
	/**
	 * Method to get information about an event visible to the current user.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function getEvent($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		$path = $event . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the event's wall.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getFeed($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/feed' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to post a link on event's feed which the current_user is or maybe attending.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with publish_stream permission.
	 * @param   string  $link          Link URL.
	 * @param   strin   $message       Link message.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createLink($event, $access_token, $link, $message=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/feed' . $token;

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
	 * @return  boolean   Returns true if successful, and false otherwise.
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
	 * Method to post on event's wall. Message or link parameter is required.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * @param   string  $message       Post message.
	 * @param   string  $link          Post URL.
	 * @param   string  $picture       Post thumbnail image (can only be used if link is specified) 
	 * @param   string  $name          Post name (can only be used if link is specified).
	 * @param   string  $caption       Post caption (can only be used if link is specified).
	 * @param   string  $description   Post description (can only be used if link is specified).
	 * @param   array   $actions       Post actions array of objects containing name and link.
	 *
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createPost($event, $access_token, $message=null, $link=null, $picture=null, $name=null, $caption=null,
		$description=null, $actions=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/feed' . $token;

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;
		$data['link'] = $link;
		$data['name'] = $name;
		$data['caption'] = $caption;
		$data['description'] = $description;
		$data['actions'] = $actions;
		$data['picture'] = $picture;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data);
	}

	/**
	 * Method to delete a post. Note: you can only delete the post if it was created by the current user.
	 * 
	 * @param   mixed   $post          The Post ID.
	 * @param   string  $access_token  The Facebook access token. 
	 * 
	 * @return  boolean   Returns true if successful, and false otherwise.
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
	 * Method to post a status message on behalf of the user on the event's wall.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with publish_stream permission.
	 * @param   string  $message       Status message content.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createStatus($event, $access_token, $message)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/feed' . $token;

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
	 * @return  boolean   Returns true if successful, and false otherwise.
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
	 * Method to get the list of invitees for the event.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getInvited($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/invited' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to check if a user is invited to the event.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * 
	 * @return  array   The decoded JSON response or an empty array if the user is not invited.
	 * 
	 * @since   12.1
	 */
	public function isInvited($event, $access_token, $user)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/invited/' . $user . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to invite users to the event.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with create_event permission.
	 * @param   string  $users         Comma separated list of user ids.
	 * 
	 * @return  boolean   Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createInvite($event, $access_token, $users)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/invited' . $token;

		// Set POST request parameters.
		$data = array();
		$data['users'] = $users;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data);
	}

	/**
	 * Method to delete a invitation. Note: you can only delete the invite if the current user is the event admin.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with the rsvp_event permission.
	 * @param   string  $user          The user id.
	 * 
	 * @return  boolean   Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function deleteInvite($event, $access_token, $user)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/invited/' . $user . $token;

		// Send the delete request.
		return $this->sendRequest($path, 'delete');
	}

	/**
	 * Method to get the list of attending users.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getAttending($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/attending' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to check if a user is attending an event.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * 
	 * @return  array   The decoded JSON response or an empty array if the user is not invited.
	 * 
	 * @since   12.1
	 */
	public function isAttending($event, $access_token, $user)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/attending/' . $user . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to set the current user as attending.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with rsvp_event permission.
	 * 
	 * @return  boolean   Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createAttending($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/attending' . $token;

		// Send the post request.
		return $this->sendRequest($path, 'post');
	}

	/**
	 * Method to get the list of maybe attending users.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getMaybe($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/maybe' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to check if a user is maybe attending an event.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * 
	 * @return  array   The decoded JSON response or an empty array if the user is not invited.
	 * 
	 * @since   12.1
	 */
	public function isMaybe($event, $access_token, $user)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/maybe/' . $user . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to set the current user as maybe attending.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with rsvp_event permission.
	 * 
	 * @return  boolean   Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createMaybe($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/maybe' . $token;

		// Send the post request.
		return $this->sendRequest($path, 'post');
	}

	/**
	 * Method to get the list of users which declined the event.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getDeclined($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/declined' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to check if a user responded 'no' to the event.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * 
	 * @return  array   The decoded JSON response or an empty array if the user is not invited.
	 * 
	 * @since   12.1
	 */
	public function isDeclined($event, $access_token, $user)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/declined/' . $user . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to set the current user as declined.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with rsvp_event permission.
	 * 
	 * @return  boolean   Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createDeclined($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/declined' . $token;

		// Send the post request.
		return $this->sendRequest($path, 'post');
	}

	/**
	 * Method to get the list of users which have not replied to the event.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getNoreply($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/noreply' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to check if a user has not replied to the event.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 * @param   mixed   $user          Either an integer containing the user ID or a string containing the username.
	 * 
	 * @return  array   The decoded JSON response or an empty array if the user is not invited.
	 * 
	 * @since   12.1
	 */
	public function isNoreply($event, $access_token, $user)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/noreply/' . $user . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get the event's profile picture.
	 * 
	 * @param   string  $event         The event id.    
	 * @param   string  $access_token  The Facebook access token with user_events or friends_events permission.
	 * @param   string  $type          To request a different photo use square | small | normal | large.
	 * 
	 * @return  string   The URL to the event's profile picture.
	 * 
	 * @since   12.1
	 */
	public function getPicture($event, $access_token=null, $type=null)
	{
		$token = '?access_token=' . $access_token;

		if ($type != null)
		{
			$type = '&type=' . $type;
		}
		else
		{
			$type = '';
		}

		// Build the request path.
		$path = $event . '/picture' . $token . $type;

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		return $response->headers['Location'];
	}

	/**
	 * Method to get photos published on event's wall.
	 * 
	 * @param   string  $event         The event id.    
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getPhotos($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/photos' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to post a photo on event's wall.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with publish_stream  permission.
	 * @param   string  $source        Path to photo.
	 * @param   string  $message       Photo description.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createPhoto($event, $access_token, $source, $message=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/photos' . $token;

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;
		$data[basename($source)] = '@' . realpath($source);

		// Send the post request.
		return $this->sendRequest($path, 'post', $data, array('Content-type' => 'multipart/form-data'));
	}

	/**
	 * Method to get videos published on event's wall.
	 * 
	 * @param   string  $event         The event id.    
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getVideos($event, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/videos' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to post a video on event's wall.
	 * 
	 * @param   string  $event         The event id.
	 * @param   string  $access_token  The Facebook access token with publish_stream  permission.
	 * @param   string  $source        Path to photo.
	 * @param   string  $title         Video title.
	 * @param   string  $description   Video description.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createVideo($event, $access_token, $source, $title=null, $description=null)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $event . '/videos' . $token;

		// Set POST request parameters.
		$data = array();
		$data['title'] = $title;
		$data['description'] = $description;
		$data[basename($source)] = '@' . realpath($source);

		// Send the post request.
		return $this->sendRequest($path, 'post', $data, array('Content-type' => 'multipart/form-data'));
	}
}
