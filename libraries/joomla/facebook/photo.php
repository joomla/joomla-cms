<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('JPATH_PLATFORM') or die();


/**
 * Facebook API Photo class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @see         http://developers.facebook.com/docs/reference/api/photo/
 * @since       13.1
 */
class JFacebookPhoto extends JFacebookObject
{
	/**
	 * Method to get a photo. Requires authentication and user_photos or friends_photos permission for private photos.
	 *
	 * @param   string  $photo  The photo id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getPhoto($photo)
	{
		return $this->get($photo);
	}

	/**
	 * Method to get a photo's comments. Requires authentication and user_photos or friends_photos permission for private photos.
	 *
	 * @param   string   $photo   The photo id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getComments($photo, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($photo, 'comments', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to comment on a photo. Requires authentication and publish_stream permission, user_photos or friends_photos permission for private photos.
	 *
	 * @param   string  $photo    The photo id.
	 * @param   string  $message  The comment's text.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createComment($photo, $message)
	{
		// Set POST request parameters.
		$data['message'] = $message;

		return $this->createConnection($photo, 'comments', $data);
	}

	/**
	 * Method to delete a comment. Requires authentication and publish_stream permission, user_photos or friends_photos permission for private photos.
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
	 * Method to get photo's likes. Requires authentication and user_photos or friends_photos permission for private photos.
	 *
	 * @param   string   $photo   The photo id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getLikes($photo, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($photo, 'likes', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to like a photo. Requires authentication and publish_stream permission, user_photos or friends_photos permission for private photos.
	 *
	 * @param   string  $photo  The photo id.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function createLike($photo)
	{
		return $this->createConnection($photo, 'likes');
	}

	/**
	 * Method to unlike a photo. Requires authentication and publish_stream permission, user_photos or friends_photos permission for private photos.
	 *
	 * @param   string  $photo  The photo id.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deleteLike($photo)
	{
		return $this->deleteConnection($photo, 'likes');
	}

	/**
	 * Method to get the Users tagged in the photo. Requires authentication and user_photos or friends_photos permission for private photos.
	 *
	 * @param   string   $photo   The photo id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getTags($photo, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($photo, 'tags', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to tag one or more Users in a photo. $to or $tag_text required.
	 * Requires authentication and publish_stream permission, user_photos permission for private photos.
	 *
	 * @param   string   $photo     The photo id.
	 * @param   mixed    $to        ID of the User or an array of Users to tag in the photo: [{"id":"1234"}, {"id":"12345"}].
	 * @param   string   $tag_text  A text string to tag.
	 * @param   integer  $x         x coordinate of tag, as a percentage offset from the left edge of the picture.
	 * @param   integer  $y         y coordinate of tag, as a percentage offset from the top edge of the picture.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function createTag($photo, $to = null, $tag_text = null, $x = null, $y = null)
	{
		// Set POST request parameters.
		if (is_array($to))
		{
			$data['tags'] = $to;
		}
		else
		{
			$data['to'] = $to;
		}

		if ($tag_text)
		{
			$data['tag_text'] = $tag_text;
		}

		if ($x)
		{
			$data['x'] = $x;
		}

		if ($y)
		{
			$data['y'] = $y;
		}

		return $this->createConnection($photo, 'tags', $data);
	}

	/**
	 * Method to update the position of the tag for a particular Users in a photo.
	 * Requires authentication and publish_stream permission, user_photos permission for private photos.
	 *
	 * @param   string   $photo  The photo id.
	 * @param   string   $to     ID of the User to update tag in the photo.
	 * @param   integer  $x      x coordinate of tag, as a percentage offset from the left edge of the picture.
	 * @param   integer  $y      y coordinate of tag, as a percentage offset from the top edge of the picture.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function updateTag($photo, $to, $x = null, $y = null)
	{
		// Set POST request parameters.
		$data['to'] = $to;

		if ($x)
		{
			$data['x'] = $x;
		}

		if ($y)
		{
			$data['y'] = $y;
		}

		return $this->createConnection($photo, 'tags', $data);
	}

	/**
	 * Method to get the album-sized view of the photo. Requires authentication and user_photos or friends_photos permission for private photos.
	 *
	 * @param   string   $photo     The photo id.
	 * @param   boolean  $redirect  If false this will return the URL of the picture without a 302 redirect.
	 *
	 * @return  string  URL of the picture.
	 *
	 * @since   13.1
	 */
	public function getPicture($photo, $redirect = true)
	{
		$extra_fields = '';

		if ($redirect == false)
		{
			$extra_fields = '?redirect=false';
		}

		return $this->getConnection($photo, 'picture', $extra_fields);
	}
}
