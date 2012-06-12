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
 * Facebook API Post class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookComment extends JFacebookObject
{
/**
	 * Method to get a comment.
	 * 
	 * @param   string  $comment       The comment id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getComment($comment, $access_token)
	{
		return parent::get($comment, $access_token);
	}

	/**
	 * Method to delete a comment.
	 * 
	 * @param   string  $comment       The comment id.
	 * @param   string  $access_token  The Facebook access token with publish_stream permission. 
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function deleteComment($comment, $access_token)
	{
		return parent::deleteConnection($comment, $access_token);
	}

	/**
	 * Method to get a comment's comments.
	 * 
	 * @param   string  $comment       The comment id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getComments($comment, $access_token)
	{
		return parent::getConnection($comment, $access_token, 'comments');
	}

	/**
	 * Method to comment on a comment.
	 * 
	 * @param   string  $comment       The comment id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * @param   string  $message       The comment's text.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createComment($comment, $access_token, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return parent::createConnection($comment, $access_token, 'comments', $data);
	}

	/**
	 * Method to get comment's likes.
	 * 
	 * @param   string  $comment       The comment id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getLikes($comment, $access_token)
	{
		return parent::getConnection($comment, $access_token, 'likes');
	}

	/**
	 * Method to like a comment.
	 * 
	 * @param   string  $comment       The comment id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createLike($comment, $access_token)
	{
		return parent::createConnection($comment, $access_token, 'likes');
	}

	/**
	 * Method to unlike a comment.
	 * 
	 * @param   string  $comment       The comment id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission. 
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function deleteLike($comment, $access_token)
	{
		return parent::deleteConnection($comment, $access_token, 'likes');
	}
}
