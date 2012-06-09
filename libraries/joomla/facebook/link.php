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
 * Facebook API Link class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookLink extends JFacebookObject
{
	/**
	 * Method to get a link.
	 * 
	 * @param   string  $link          The link id.
	 * @param   string  $access_token  The Facebook access token for public links, read_stream permission for non-public links.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getLink($link, $access_token)
	{
		return $this->get($link, $access_token);
	}

	/**
	 * Method to get a link's comments.
	 * 
	 * @param   string  $link          The link id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getComments($link, $access_token)
	{
		return $this->getConnection($link, $access_token, 'comments');
	}

	/**
	 * Method to comment on a link.
	 * 
	 * @param   string  $link          The link id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * @param   string  $message       The comment's text.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createComment($link, $access_token, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return $this->createConnection($link, $access_token, 'comments', $data);
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
		return $this->deleteConnection($comment, $access_token);
	}

	/**
	 * Method to get link's likes.
	 * 
	 * @param   string  $link          The link id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function getLikes($link, $access_token)
	{
		return $this->getConnection($link, $access_token, 'likes');
	}

	/**
	 * Method to like a link.
	 * 
	 * @param   string  $link          The link id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createLike($link, $access_token)
	{
		return $this->createConnection($link, $access_token, 'likes');
	}

	/**
	 * Method to unlike a link.
	 * 
	 * @param   string  $link          The link id.
	 * @param   string  $access_token  The Facebook access token. 
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function deleteLike($link, $access_token)
	{
		return $this->deleteConnection($link, $access_token, 'likes');
	}
}
