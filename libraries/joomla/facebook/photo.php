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
 * Facebook API Photo class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookPhoto extends JFacebookObject
{
	/**
	 * Method to get a photo.
	 * 
	 * @param   string  $photo         The photo id.
	 * @param   string  $access_token  The Facebook access token for public photod and user_photos or friends_photos permission for private photos.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getPhoto($photo, $access_token)
	{
		return parent::get($photo, $access_token);
	}

	/**
	 * Method to get a photo's comments.
	 * 
	 * @param   string  $photo         The photo id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getComments($photo, $access_token)
	{
		return parent::getConnection($photo, $access_token, 'comments');
	}

	/**
	 * Method to comment on a photo.
	 * 
	 * @param   string  $photo         The photo id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * @param   string  $message       The comment's text.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createComment($photo, $access_token, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return parent::createConnection($photo, $access_token, 'comments', $data);
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
	 * Method to get photo's likes.
	 * 
	 * @param   string  $photo         The photo id.
	 * @param   string  $access_token  The Facebook access token.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getLikes($photo, $access_token)
	{
		return parent::getConnection($photo, $access_token, 'likes');
	}

	/**
	 * Method to like a photo.
	 * 
	 * @param   string  $photo         The photo id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createLike($photo, $access_token)
	{
		return parent::createConnection($photo, $access_token, 'likes');
	}

	/**
	 * Method to unlike a photo.
	 * 
	 * @param   string  $photo         The photo id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission. 
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function deleteLike($photo, $access_token)
	{
		return parent::deleteConnection($photo, $access_token, 'likes');
	}

	/**
	 * Method to get the Users tagged in the photo.
	 * 
	 * @param   string  $photo         The photo id.
	 * @param   string  $access_token  The Facebook access token for public photod and user_photos or friends_photos permission for private photos.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getTags($photo, $access_token)
	{
		return parent::getConnection($photo, $access_token, 'tags');
	}

	/**
	 * Method to tag one or more Users in a photo. $to or $tag_text required
	 * 
	 * @param   string  $photo         The photo id.
	 * @param   string  $access_token  The Facebook access token with the user_photos and publish_stream permissions.
	 * @param   mixed   $to            ID of the User or an array of Users to tag in the photo: [{"id":"1234"}, {"id":"12345"}].
	 * @param   string  $tag_text      A text string to tag.
	 * @param   number  $x             x coordinate of tag, as a percentage offset from the left edge of the picture.
	 * @param   number  $y             y coordinate of tag, as a percentage offset from the top edge of the picture.
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createTag($photo, $access_token, $to=null, $tag_text=null, $x=null, $y=null)
	{
		// Set POST request parameters.
		$data = array();
		if (is_array($to))
		{
			$data['tags'] = $to;
		}
		else
		{
			$data['to'] = $to;
		}
		$data['tag_text'] = $tag_text;
		$data['x'] = $x;
		$data['y'] = $y;

		return parent::createConnection($photo, $access_token, 'tags', $data);
	}

	/**
	 * Method to update the position of the tag for a particular Users in a photo.
	 * 
	 * @param   string  $photo         The photo id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * @param   string  $to            ID of the User to update tag in the photo.
	 * @param   number  $x             x coordinate of tag, as a percentage offset from the left edge of the picture.
	 * @param   number  $y             y coordinate of tag, as a percentage offset from the top edge of the picture.
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function updateTag($photo, $access_token, $to, $x=null, $y=null)
	{
		// Set POST request parameters.
		$data = array();
		$data['to'] = $to;
		$data['x'] = $x;
		$data['y'] = $y;

		return parent::createConnection($photo, $access_token, 'tags', $data);
	}

	/**
	 * Method to get the album-sized view of the photo.
	 * 
	 * @param   string  $photo         The photo id.
	 * @param   string  $access_token  The Facebook access token for public photod and user_photos or friends_photos permission for private photos.
	 * 
	 * @return  string  URL of the picture.
	 * 
	 * @since   12.1
	 */
	public function getPicture($photo, $access_token)
	{
		return parent::getConnection($photo, $access_token, 'picture');
	}
}
