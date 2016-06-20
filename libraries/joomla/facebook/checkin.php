<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('JPATH_PLATFORM') or die();


/**
 * Facebook API Checkin class for the Joomla Platform.
 *
 * @see    http://developers.facebook.com/docs/reference/api/checkin/
 * @since  13.1
 */
class JFacebookCheckin extends JFacebookObject
{
	/**
	 * Method to get a checkin. Requires authentication and user_checkins or friends_checkins permission.
	 *
	 * @param   string  $checkin  The checkin id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getCheckin($checkin)
	{
		return $this->get($checkin);
	}

	/**
	 * Method to get a checkin's comments. Requires authentication and user_checkins or friends_checkins permission.
	 *
	 * @param   string   $checkin  The checkin id.
	 * @param   integer  $limit    The number of objects per page.
	 * @param   integer  $offset   The object's number on the page.
	 * @param   string   $until    A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since    A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getComments($checkin, $limit=0, $offset=0, $until=null, $since=null)
	{
		return $this->getConnection($checkin, 'comments', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to post a comment to the checkin. Requires authentication and publish_stream and user_checkins or friends_checkins permission.
	 *
	 * @param   string  $checkin  The checkin id.
	 * @param   string  $message  The checkin's text.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createComment($checkin, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return $this->createConnection($checkin, 'comments', $data);
	}

	/**
	 * Method to delete a comment. Requires authentication and publish_stream permission.
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
	 * Method to get a checkin's likes. Requires authentication and user_checkins or friends_checkins permission.
	 *
	 * @param   string   $checkin  The checkin id.
	 * @param   integer  $limit    The number of objects per page.
	 * @param   integer  $offset   The object's number on the page.
	 * @param   string   $until    A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since    A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getLikes($checkin, $limit=0, $offset=0, $until=null, $since=null)
	{
		return $this->getConnection($checkin, 'likes', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to like a checkin. Requires authentication and publish_stream and user_checkins or friends_checkins permission.
	 *
	 * @param   string  $checkin  The checkin id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createLike($checkin)
	{
		return $this->createConnection($checkin, 'likes');
	}

	/**
	 * Method to unlike a checkin. Requires authentication and publish_stream permission.
	 *
	 * @param   string  $checkin  The checkin id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function deleteLike($checkin)
	{
		return $this->deleteConnection($checkin, 'likes');
	}
}
