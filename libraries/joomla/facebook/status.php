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
 * Facebook API Status class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookStatus extends JFacebookObject
{
	/**
	 * Method to get a status message.
	 * 
	 * @param   string  $status        The status message id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getStatus($status, $access_token)
	{
		$token = '?access_token=' . $access_token;

		$path = $status . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to get a status message's comments.
	 * 
	 * @param   string  $status        The status message id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getComments($status, $access_token)
	{
		$token = '?access_token=' . $access_token;

		$path = $status . '/comments' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to post a comment to the status message.
	 * 
	 * @param   string  $status        The status message id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream and user_status or friends_status permission.
	 * @param   string  $message       The comment's text.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createComment($status, $access_token, $message)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $status . '/comments' . $token;

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
	 * Method to get a status message's likes.
	 * 
	 * @param   string  $status        The status message id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getLikes($status, $access_token)
	{
		$token = '?access_token=' . $access_token;

		$path = $status . '/likes' . $token;

		// Send the request.
		return $this->sendRequest($path);
	}

	/**
	 * Method to like status message.
	 * 
	 * @param   string  $status        The status message id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream and user_status or friends_status permission.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createLike($status, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $status . '/likes' . $token;

		// Send the post request.
		return $this->sendRequest($path, 'post');
	}

	/**
	 * Method to unlike a status message.
	 * 
	 * @param   string  $status        The status message id.
	 * @param   string  $access_token  The Facebook access token. 
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function deleteLike($status, $access_token)
	{
		$token = '?access_token=' . $access_token;

		// Build the request path.
		$path = $status . '/likes' . $token;

		// Send the delete request.
		return $this->sendRequest($path, 'delete');
	}

}
