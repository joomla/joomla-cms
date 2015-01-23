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
 * Facebook API Status class for the Joomla Platform.
 *
 * @see    http://developers.facebook.com/docs/reference/api/status/
 * @since  13.1
 */
class JFacebookStatus extends JFacebookObject
{
	/**
	 * Method to get a status message. Requires authentication.
	 *
	 * @param   string  $status  The status message id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getStatus($status)
	{
		return $this->get($status);
	}

	/**
	 * Method to get a status message's comments. Requires authentication.
	 *
	 * @param   string   $status  The status message id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getComments($status, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($status, 'comments', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to post a comment to the status message. Requires authentication and publish_stream and user_status or friends_status permission.
	 *
	 * @param   string  $status   The status message id.
	 * @param   string  $message  The comment's text.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createComment($status, $message)
	{
		// Set POST request parameters.
		$data['message'] = $message;

		return $this->createConnection($status, 'comments', $data);
	}

	/**
	 * Method to delete a comment. Requires authentication and publish_stream and user_status or friends_status permission.
	 *
	 * @param   string  $comment  The comment's id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function deleteComment($comment)
	{
		return $this->deleteConnection($comment);
	}

	/**
	 * Method to get a status message's likes. Requires authentication.
	 *
	 * @param   string   $status  The status message id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getLikes($status, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($status, 'likes', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to like status message. Requires authentication and publish_stream and user_status or friends_status permission.
	 *
	 * @param   string  $status  The status message id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createLike($status)
	{
		return $this->createConnection($status, 'likes');
	}

	/**
	 * Method to unlike a status message. Requires authentication and publish_stream and user_status or friends_status permission.
	 *
	 * @param   string  $status  The status message id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function deleteLike($status)
	{
		return $this->deleteConnection($status, 'likes');
	}
}
