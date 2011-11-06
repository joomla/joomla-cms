<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * GitHub API Issues class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub
 * @since       11.4
 */
class JGithubIssues extends JGithubObject
{
	/**
	 * @param unknown_type $parameters
	 * @param unknown_type $page
	 * @param unknown_type $per_page
	 */
	public function getAll($parameters = array(), $page = 0, $per_page = 0)
	{
		$url = '/issues';

		$queryString = '';

		foreach ($parameters as $parameter)
		{
			$queryString .= '';
		}
		if (isset($options['filter']))
		{
		}
		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	/**
	 * @param unknown_type $user
	 * @param unknown_type $page
	 * @param unknown_type $per_page
	 */
	public function getByUser($user, $page = 0, $per_page = 0)
	{
		$url = '/users/' . $user . '/gists';
		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	/**
	 * @param unknown_type $page
	 * @param unknown_type $per_page
	 */
	public function getPublic($page = 0, $per_page = 0)
	{
		$url = '/gists/public';
		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	/**
	 * @param unknown_type $page
	 * @param unknown_type $per_page
	 */
	public function getStarred($page = 0, $per_page = 0)
	{
		$url = '/gists/starred';
		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	/**
	 * @param unknown_type $gist_id
	 */
	public function get($gist_id)
	{
		return $this->client->get('/gists/' . (int) $gist_id)->body;
	}

	/**
	 * @param unknown_type $files
	 * @param unknown_type $public
	 * @param unknown_type $description
	 */
	public function create($files, $public = false, $description = null)
	{
		$gist = new stdClass();
		$gist->public = $public;
		$gist->files = $files;

		if (!empty($description))
		{
			$gist->description = $description;
		}

		return $this->client->post('/gists', $gist)->body;
	}

	/**
	 * @param unknown_type $gist_id
	 * @param unknown_type $files
	 * @param unknown_type $description
	 */
	public function edit($gist_id, $files, $description = null)
	{
		$gist = new stdClass();
		$gist->files = $files;

		if (!empty($description))
		{
			$gist->description = $description;
		}

		return $this->client->patch('/gists/' . (int) $gist_id, $gist)->body;
	}

	/**
	 * @param unknown_type $gist_id
	 */
	public function star($gist_id)
	{
		return $this->client->put('/gists/' . (int) $gist_id . '/star')->body;
	}

	/**
	 * @param unknown_type $gist_id
	 */
	public function unstar($gist_id)
	{
		return $this->client->delete('/gists/' . (int) $gist_id . '/star')->body;
	}

	/**
	 * @param unknown_type $gist_id
	 * @return boolean
	 */
	public function isStarred($gist_id)
	{
		$response = $this->client->get('/gists/' . (int) $gist_id . '/star');

		if ($response->code == '204')
		{
			return true;
		}
		else
		{ // the code should be 404
			return false;
		}
	}

	/**
	 * @param unknown_type $gist_id
	 */
	public function fork($gist_id)
	{
		return $this->client->put('/gists/' . (int) $gist_id . '/fork')->body;
	}

	/**
	 * @param unknown_type $gist_id
	 */
	public function delete($gist_id)
	{
		return $this->client->delete('/gists/' . (int) $gist_id)->body;
	}

	/**
	 * @param unknown_type $gist_id
	 */
	public function getComments($gist_id)
	{
		return $this->client->get('/gists/' . (int) $gist_id . '/comments')->body;
	}

	/**
	 * @param unknown_type $comment_id
	 */
	public function getComment($comment_id)
	{
		return $this->client->get('/gists/comments/' . (int) $comment_id)->body;
	}

	/**
	 * @param unknown_type $gist_id
	 * @param unknown_type $comment
	 */
	public function createComment($gist_id, $comment)
	{
		return $this->client->post('/gists/' . (int) $gist_id . '/comments', array('body' => $comment))->body;
	}

	/**
	 * @param unknown_type $comment_id
	 * @param unknown_type $comment
	 */
	public function editComment($comment_id, $comment)
	{
		return $this->client->patch('/gists/comments/' . (int) $comment_id, array('body' => $comment))->body;
	}

	/**
	 * @param unknown_type $comment_id
	 */
	public function deleteComment($comment_id)
	{
		return $this->client->delete('/gists/comments/' . (int) $comment_id)->body;
	}
}
