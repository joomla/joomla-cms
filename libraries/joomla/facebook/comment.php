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
 * Facebook API Comment class for the Joomla Platform.
 *
 * @link        http://developers.facebook.com/docs/reference/api/Comment/
 * @since       13.1
 * @deprecated  4.0  Use the `joomla/facebook` package via Composer instead
 */
class JFacebookComment extends JFacebookObject
{
	/**
	 * Method to get a comment. Requires authentication.
	 *
	 * @param   string  $comment  The comment id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getComment($comment)
	{
		return $this->get($comment);
	}

	/**
	 * Method to delete a comment. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $comment  The comment id.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deleteComment($comment)
	{
		return $this->deleteConnection($comment);
	}

	/**
	 * Method to get a comment's comments. Requires authentication.
	 *
	 * @param   string   $comment  The comment id.
	 * @param   integer  $limit    The number of objects per page.
	 * @param   integer  $offset   The object's number on the page.
	 * @param   string   $until    A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since    A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getComments($comment, $limit=0, $offset=0, $until=null, $since=null)
	{
		return $this->getConnection($comment, 'comments', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to comment on a comment. Requires authentication with publish_stream permission.
	 *
	 * @param   string  $comment  The comment id.
	 * @param   string  $message  The comment's text.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createComment($comment, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return $this->createConnection($comment, 'comments', $data);
	}

	/**
	 * Method to get comment's likes. Requires authentication.
	 *
	 * @param   string   $comment  The comment id.
	 * @param   integer  $limit    The number of objects per page.
	 * @param   integer  $offset   The object's number on the page.
	 * @param   string   $until    A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since    A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getLikes($comment, $limit=0, $offset=0, $until=null, $since=null)
	{
		return $this->getConnection($comment, 'likes', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to like a comment. Requires authentication and publish_stram permission.
	 *
	 * @param   string  $comment  The comment id.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function createLike($comment)
	{
		return $this->createConnection($comment, 'likes');
	}

	/**
	 * Method to unlike a comment. Requires authentication and publish_stram permission.
	 *
	 * @param   string  $comment  The comment id.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deleteLike($comment)
	{
		return $this->deleteConnection($comment, 'likes');
	}
}
