<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('JPATH_PLATFORM') or die();


/**
 * Facebook API Video class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @see         http://developers.facebook.com/docs/reference/api/video/
 * @since       13.1
 */
class JFacebookVideo extends JFacebookObject
{
	/**
	 * Method to get a video. Requires authentication and user_videos or friends_videos permission for private videos.
	 *
	 * @param   string  $video  The video id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getVideo($video)
	{
		return $this->get($video);
	}

	/**
	 * Method to get a video's comments. Requires authentication and user_videos or friends_videos permission for private videos.
	 *
	 * @param   string   $video   The video id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getComments($video, $limit=0, $offset=0, $until=null, $since=null)
	{
		return $this->getConnection($video, 'comments', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to comment on a video. Requires authentication and publish_stream permission, user_videos or friends_videos permission for private videos.
	 *
	 * @param   string  $video    The video id.
	 * @param   string  $message  The comment's text.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createComment($video, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return $this->createConnection($video, 'comments', $data);
	}

	/**
	 * Method to delete a comment. Requires authentication and publish_stream permission, user_videos or friends_videos permission for private videos.
	 *
	 * @param   string  $comment  The comment's id.
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
	 * Method to get video's likes. Requires authentication and user_videos or friends_videos permission for private videos.
	 *
	 * @param   string   $video   The video id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getLikes($video, $limit=0, $offset=0, $until=null, $since=null)
	{
		return $this->getConnection($video, 'likes', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to like a video. Requires authentication and publish_stream permission, user_videos or friends_videos permission for private videos.
	 *
	 * @param   string  $video  The video id.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function createLike($video)
	{
		return $this->createConnection($video, 'likes');
	}

	/**
	 * Method to unlike a video. Requires authentication and publish_stream permission, user_videos or friends_videos permission for private videos.
	 *
	 * @param   string  $video  The video id.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deleteLike($video)
	{
		return $this->deleteConnection($video, 'likes');
	}

	/**
	 * Method to get the album-sized view of the video. Requires authentication and user_videos or friends_videos permission for private photos.
	 *
	 * @param   string  $video  The video id.
	 *
	 * @return  string  URL of the picture.
	 *
	 * @since   13.1
	 */
	public function getPicture($video)
	{
		return $this->getConnection($video, 'picture');
	}
}
