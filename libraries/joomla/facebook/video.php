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
 * Facebook API Video class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookVideo extends JFacebookObject
{
	/**
	 * Method to get a video.
	 * 
	 * @param   string  $video         The video id.
	 * @param   string  $access_token  The Facebook access token for public videos and user_videos or friends_videos permission for private videos.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getVideo($video, $access_token)
	{
		return parent::get($video, $access_token);
	}

	/**
	 * Method to get a video's comments.
	 * 
	 * @param   string  $video         The video id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getComments($video, $access_token)
	{
		return parent::getConnection($video, $access_token, 'comments');
	}

	/**
	 * Method to comment on a video.
	 * 
	 * @param   string  $video         The video id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * @param   string  $message       The comment's text.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createComment($video, $access_token, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return parent::createConnection($video, $access_token, 'comments', $data);
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
		return parent::deleteConnection($comment, $access_token);
	}

	/**
	 * Method to get video's likes.
	 * 
	 * @param   string  $video         The video id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getLikes($video, $access_token)
	{
		return parent::getConnection($video, $access_token, 'likes');
	}

	/**
	 * Method to like a video.
	 * 
	 * @param   string  $video         The video id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createLike($video, $access_token)
	{
		return parent::createConnection($video, $access_token, 'likes');
	}

	/**
	 * Method to unlike a video.
	 * 
	 * @param   string  $video         The video id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission. 
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function deleteLike($video, $access_token)
	{
		return parent::deleteConnection($video, $access_token, 'likes');
	}

	/**
	 * Method to get the album-sized view of the video.
	 * 
	 * @param   string  $video         The video id.
	 * @param   string  $access_token  The Facebook access token for public photod and user_videos or friends_videos permission for private photos.
	 * 
	 * @return  string  URL of the picture.
	 * 
	 * @since   12.1
	 */
	public function getPicture($video, $access_token)
	{
		return parent::getConnection($video, $access_token, 'picture');
	}
}
