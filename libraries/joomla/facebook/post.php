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
class JFacebookPost extends JFacebookObject
{
/**
	 * Method to get a post.
	 * 
	 * @param   string  $post          The post id.
	 * @param   string  $access_token  The Facebook access token for public data and read_stream permission for all data.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getPost($post, $access_token)
	{
		return $this->get($post, $access_token);
	}

	/**
	 * Method to delete a post if it was created by this application.
	 * 
	 * @param   string  $post          The post id.
	 * @param   string  $access_token  The Facebook access token with publish_stream permission. 
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function deletePost($post, $access_token)
	{
		return $this->deleteConnection($post, $access_token);
	}

	/**
	 * Method to get a post's comments.
	 * 
	 * @param   string  $post          The post id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getComments($post, $access_token)
	{
		return $this->getConnection($post, $access_token, 'comments');
	}

	/**
	 * Method to comment on a post.
	 * 
	 * @param   string  $post          The post id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * @param   string  $message       The comment's text.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createComment($post, $access_token, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return $this->createConnection($post, $access_token, 'comments', $data);
	}

	/**
	 * Method to delete a comment.
	 * 
	 * @param   string  $comment       The comment's id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission. 
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function deleteComment($comment, $access_token)
	{
		return $this->deleteConnection($comment, $access_token);
	}

	/**
	 * Method to get post's likes.
	 * 
	 * @param   string  $post          The post id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function getLikes($post, $access_token)
	{
		return $this->getConnection($post, $access_token, 'likes');
	}

	/**
	 * Method to like a post.
	 * 
	 * @param   string  $post          The post id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createLike($post, $access_token)
	{
		return $this->createConnection($post, $access_token, 'likes');
	}

	/**
	 * Method to unlike a post.
	 * 
	 * @param   string  $post          The post id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission. 
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function deleteLike($post, $access_token)
	{
		return $this->deleteConnection($post, $access_token, 'likes');
	}
}