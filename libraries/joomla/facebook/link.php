<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Facebook API Link class for the Joomla Platform.
 *
 * @link        http://developers.facebook.com/docs/reference/api/link/
 * @since       3.2.0
 * @deprecated  4.0  Use the `joomla/facebook` package via Composer instead
 */
class JFacebookLink extends JFacebookObject
{
	/**
	 * Method to get a link. Requires authentication and read_stream permission for non-public links.
	 *
	 * @param   string  $link  The link id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   3.2.0
	 */
	public function getLink($link)
	{
		return $this->get($link);
	}

	/**
	 * Method to get a link's comments. Requires authentication and read_stream permission for non-public links.
	 *
	 * @param   string   $link    The link id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   3.2.0
	 */
	public function getComments($link, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($link, 'comments', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to comment on a link. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $link     The link id.
	 * @param   string  $message  The comment's text.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   3.2.0
	 */
	public function createComment($link, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return $this->createConnection($link, 'comments', $data);
	}

	/**
	 * Method to delete a comment. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $comment  The comment's id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   3.2.0
	 */
	public function deleteComment($comment)
	{
		return $this->deleteConnection($comment);
	}

	/**
	 * Method to get link's likes. Requires authentication and read_stream permission for non-public links.
	 *
	 * @param   string   $link    The link id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   3.2.0
	 */
	public function getLikes($link, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($link, 'likes', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to like a link. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $link  The link id.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   3.2.0
	 */
	public function createLike($link)
	{
		return $this->createConnection($link, 'likes');
	}

	/**
	 * Method to unlike a link. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $link  The link id.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   3.2.0
	 */
	public function deleteLike($link)
	{
		return $this->deleteConnection($link, 'likes');
	}
}
