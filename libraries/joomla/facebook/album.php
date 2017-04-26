<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Facebook API Album class for the Joomla Platform.
 *
 * @see         http://developers.facebook.com/docs/reference/api/album/
 * @since       13.1
 * @deprecated  4.0  Use the `joomla/facebook` package via Composer instead
 */
class JFacebookAlbum extends JFacebookObject
{
	/**
	 * Method to get an album. Requires authentication and user_photos or friends_photos permission for private photos.
	 *
	 * @param   string  $album  The album id.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getAlbum($album)
	{
		return $this->get($album);
	}

	/**
	 * Method to get the photos contained in this album. Requires authentication and user_photos or friends_photos permission for private photos.
	 *
	 * @param   string   $album   The album id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getPhotos($album, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($album, 'photos', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to add photos to an album. Note: check can_upload flag first. Requires authentication and publish_stream  permission.
	 *
	 * @param   string  $album    The album id.
	 * @param   string  $source   Path to photo.
	 * @param   string  $message  Photo description.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createPhoto($album, $source, $message = null)
	{
		// Set POST request parameters.
		$data = array();
		$data[basename($source)] = '@' . realpath($source);

		if ($message)
		{
			$data['message'] = $message;
		}

		return $this->createConnection($album, 'photos', $data, array('Content-Type' => 'multipart/form-data'));
	}

	/**
	 * Method to get an album's comments. Requires authentication and user_photos or friends_photos permission for private photos.
	 *
	 * @param   string   $album   The album id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getComments($album, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($album, 'comments', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to comment on an album. Requires authentication and publish_stream  permission.
	 *
	 * @param   string  $album    The album id.
	 * @param   string  $message  The comment's text.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function createComment($album, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return $this->createConnection($album, 'comments', $data);
	}

	/**
	 * Method to delete a comment. Requires authentication and publish_stream  permission.
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
	 * Method to get album's likes. Requires authentication and user_photos or friends_photos permission for private photos.
	 *
	 * @param   string   $album   The album id.
	 * @param   integer  $limit   The number of objects per page.
	 * @param   integer  $offset  The object's number on the page.
	 * @param   string   $until   A unix timestamp or any date accepted by strtotime.
	 * @param   string   $since   A unix timestamp or any date accepted by strtotime.
	 *
	 * @return  mixed   The decoded JSON response or false if the client is not authenticated.
	 *
	 * @since   13.1
	 */
	public function getLikes($album, $limit = 0, $offset = 0, $until = null, $since = null)
	{
		return $this->getConnection($album, 'likes', '', $limit, $offset, $until, $since);
	}

	/**
	 * Method to like an album. Requires authentication and publish_stream  permission.
	 *
	 * @param   string  $album  The album id.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function createLike($album)
	{
		return $this->createConnection($album, 'likes');
	}

	/**
	 * Method to unlike an album. Requires authentication and publish_stream  permission.
	 *
	 * @param   string  $album  The album id.
	 *
	 * @return  boolean Returns true if successful, and false otherwise.
	 *
	 * @since   13.1
	 */
	public function deleteLike($album)
	{
		return $this->deleteConnection($album, 'likes');
	}

	/**
	 * Method to get the album's cover photo, the first picture uploaded to an album becomes the cover photo for the album.
	 * Requires authentication and user_photos or friends_photos permission for private photos.
	 *
	 * @param   string   $album     The album id.
	 * @param   boolean  $redirect  If false this will return the URL of the picture without a 302 redirect.
	 *
	 * @return  string  URL of the picture.
	 *
	 * @since   13.1
	 */
	public function getPicture($album, $redirect = true)
	{
		$extra_fields = '';

		if ($redirect == false)
		{
			$extra_fields = '?redirect=false';
		}

		return $this->getConnection($album, 'picture', $extra_fields);
	}
}
