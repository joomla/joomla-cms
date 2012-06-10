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
 * Facebook API Note class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
class JFacebookNote extends JFacebookObject
{
/**
	 * Method to get a note.
	 * 
	 * @param   string  $note          The note id.
	 * @param   string  $access_token  The Facebook access token for public notes, user_notes or friends_notes permission for non-public notes.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getNote($note, $access_token)
	{
		return $this->get($note, $access_token);
	}

	/**
	 * Method to get a note's comments.
	 * 
	 * @param   string  $note          The note id.
	 * @param   string  $access_token  The Facebook access token for public notes, user_notes or friends_notes permission for non-public notes.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function getComments($note, $access_token)
	{
		return $this->getConnection($note, $access_token, 'comments');
	}

	/**
	 * Method to comment on a note.
	 * 
	 * @param   string  $note          The note id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream and user_notes or friends_notes permissions.
	 * @param   string  $message       The comment's text.
	 * 
	 * @return  array   The decoded JSON response.
	 * 
	 * @since   12.1
	 */
	public function createComment($note, $access_token, $message)
	{
		// Set POST request parameters.
		$data = array();
		$data['message'] = $message;

		return $this->createConnection($note, $access_token, 'comments', $data);
	}

	/**
	 * Method to delete a comment.
	 * 
	 * @param   string  $comment       The comment's id.
	 * @param   string  $access_token  The Facebook access token. 
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
	 * Method to get note's likes.
	 * 
	 * @param   string  $note          The note id.
	 * @param   string  $access_token  The Facebook access token with user_notes or friends_notes for non-public notes.
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function getLikes($note, $access_token)
	{
		return $this->getConnection($note, $access_token, 'likes');
	}

	/**
	 * Method to like a note.
	 * 
	 * @param   string  $note          The note id.
	 * @param   string  $access_token  The Facebook access token with the publish_stream and user_notes or friends_notes.
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function createLike($note, $access_token)
	{
		return $this->createConnection($note, $access_token, 'likes');
	}

	/**
	 * Method to unlike a note.
	 * 
	 * @param   string  $note          The note id.
	 * @param   string  $access_token  The Facebook access token. 
	 * 
	 * @return  boolean Returns true if successful, and false otherwise.
	 * 
	 * @since   12.1
	 */
	public function deleteLike($note, $access_token)
	{
		return $this->deleteConnection($note, $access_token, 'likes');
	}
}