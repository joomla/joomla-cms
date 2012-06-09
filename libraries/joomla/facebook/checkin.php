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
 * Facebook API Checkin class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookCheckin extends JFacebookObject
{
	/**
	 * Method to get a checkin.
	 * 
	 * @param   string  $checkin       The checkin id.
	 * @param   string  $access_token  The Facebook access token with user_checkins or friends_checkins permission.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getCheckin($checkin, $access_token)
	{
		$token = '?access_token=' . $access_token;

		$path = $checkin . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get a checkin's comments.
	 * 
	 * @param   string  $checkin       The checkin id.
	 * @param   string  $access_token  The Facebook access token with user_checkins or friends_checkins permission.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getComments($checkin, $access_token)
	{
		$token = '?access_token=' . $access_token;

		$path = $checkin . '/comments' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to post a comment to the checkin.
	 * 
	 * @param   string  $checkin       The checkin id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream and user_checkins or friends_checkins permission.
	 * @param   string  $message       The checkin's text.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createComment($checkin, $access_token, $message)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $checkin . '/comments' . $token;

		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		// Send the post request.
		return $this->sendRequest($path, 'post', $data);
	}

	/**
	 * Method to delete a comment.
	 * 
	 * @param   string  $comment       The comment's id.
	 * @param   string  $access_token  The Facebook access token. 
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function deleteComment($comment, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $comment . $token;

		// Send the delete request.
		return $this->sendRequest($path, 'delete');
	}

	/**
	 * Method to get a checkin's likes.
	 * 
	 * @param   string  $checkin       The checkin id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getLikes($checkin, $access_token)
	{
		$token = '?access_token=' . $access_token;

		$path = $checkin . '/likes' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to like a checkin.
	 * 
	 * @param   string  $checkin       The checkin id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream and user_checkin or friends_checkin permission.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createLike($checkin, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $checkin . '/likes' . $token;

		// Send the post request.
		return $this->sendRequest($path, 'post');
	}

	/**
	 * Method to unlike a checkin.
	 * 
	 * @param   string  $checkin       The checkin id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission. 
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function deleteLike($checkin, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $checkin . '/likes' . $token;

		// Send the delete request.
		return $this->sendRequest($path, 'delete');
	}
}
