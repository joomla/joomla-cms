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
 * Facebook API Album class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookAlbum extends JFacebookObject
{
	/**
	 * Method to get an album.
	 * 
	 * @param   string  $album         The album id.
	 * @param   string  $access_token  The Facebook access token for public photos and user_photos or friends_photos permission for private photos.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getAlbum($album, $access_token)
	{
		return parent::get($album, $access_token);
	}

	/**
	 * Method to get the photos contained in this album.
	 * 
	 * @param   string  $album         The album id.
	 * @param   string  $access_token  The Facebook access token for public photos and user_photos or friends_photos permission for private photos.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getPhotos($album, $access_token)
	{
		return parent::getConnection($album, $access_token, 'photos');
	}

	/**
	 * Method to add photos to an album. Note: check can_upload flag first.
	 * 
	 * @param   string  $album         The album id.
	 * @param   string  $access_token  The Facebook access token with publish_stream  permission.
	 * @param   string  $source        Path to photo.
	 * @param   string  $message       Photo description.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createPhoto($album, $access_token, $source, $message=null)
	{
		// Set POST request parameters.
		$data = array();
		$data[basename($source)] = '@' . realpath($source);
		$data['message'] = $message;

		return parent::createConnection($album, $access_token, 'photos', $data, array('Content-type' => 'multipart/form-data'));
	}

	/**
	 * Method to get an album's comments.
	 * 
	 * @param   string  $album         The album id.
	 * @param   string  $access_token  The Facebook access token for public photos and user_photos or friends_photos permission for private photos.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getComments($album, $access_token)
	{
		return parent::getConnection($album, $access_token, 'comments');
	}

	/**
	 * Method to comment on an album.
	 * 
	 * @param   string  $album         The album id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * @param   string  $message       The comment's text.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createComment($album, $access_token, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return parent::createConnection($album, $access_token, 'comments', $data);
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
	 * Method to get album's likes.
	 * 
	 * @param   string  $album         The album id.
	 * @param   string  $access_token  The Facebook access token for public photos and user_photos or friends_photos permission for private photos.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getLikes($album, $access_token)
	{
		return parent::getConnection($album, $access_token, 'likes');
	}

	/**
	 * Method to like an album.
	 * 
	 * @param   string  $album         The album id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission.
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createLike($album, $access_token)
	{
		return parent::createConnection($album, $access_token, 'likes');
	}

	/**
	 * Method to unlike an album.
	 * 
	 * @param   string  $album         The album id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream permission. 
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function deleteLike($album, $access_token)
	{
		return parent::deleteConnection($album, $access_token, 'likes');
	}

	/**
	 * Method to get the album's cover photo, the first picture uploaded to an album becomes the cover photo for the album.
	 * 
	 * @param   string  $album         The album id.
	 * @param   string  $access_token  The Facebook access token for public photod and user_photos or friends_photos permission for private photos.
	 * 
	 * @return  string  URL of the picture.
	 * 
	 * @since   12.1
	 */
	public function getPicture($album, $access_token)
	{
		return parent::getConnection($album, $access_token, 'picture');
	}
}
