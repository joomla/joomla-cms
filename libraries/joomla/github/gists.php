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
 * GitHub API Gists class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub
 * @since       11.4
 */
class JGithubGists extends JGithubObject
{
	/**
	 * Gets list of gists
	 *
	 * @param   integer  $page      Page to request
	 * @param   integer  $per_page  Number of results to return per page
	 *
	 * @return  array    Array of gists
	 *
	 * @since   11.3
	 */
	public function getList($page = 0, $per_page = 0)
	{
		$url = '/gists';
		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	/**
	 * Gets list of a particular users gists
	 *
	 * @param   string   $user      Username for which to retrieve gists
	 * @param   integer  $page      Page to request
	 * @param   integer  $per_page  Number of results to return per page
	 *
	 * @return  array    Array of gists
	 *
	 * @since   11.3
	 */
	public function getListByUser($user, $page = 0, $per_page = 0)
	{
		$url = '/users/' . $user . '/gists';
		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	/**
	 * Gets list of all public gists
	 *
	 * @param   string   $user      Username for which to retrieve gists
	 * @param   integer  $page      Page to request
	 * @param   integer  $per_page  Number of results to return per page
	 *
	 * @return  array    Array of gists
	 *
	 * @since   11.3
	 */
	public function getListPublic($page = 0, $per_page = 0)
	{
		$url = '/gists/public';
		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	/**
	 * Get users starred gists
	 *
	 * @param   integer  $page      Page to request
	 * @param   integer  $per_page  Number of results to return per page
	 *
	 * @return  array    Array of gists
	 *
	 * @since   11.3
	 */
	public function getStarred($page = 0, $per_page = 0)
	{
		$url = '/gists/starred';
		return $this->client->get($this->paginate($url, $page, $per_page))->body;
	}

	public function get($gist_id)
	{
		return $this->client->get('/gists/' . (int) $gist_id)->body;
	}

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

	public function star($gist_id)
	{
		return $this->client->put('/gists/' . (int) $gist_id . '/star')->body;
	}

	public function unstar($gist_id)
	{
		return $this->client->delete('/gists/' . (int) $gist_id . '/star')->body;
	}

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

	public function fork($gist_id)
	{
		return $this->client->put('/gists/' . (int) $gist_id . '/fork')->body;
	}

	public function delete($gist_id)
	{
		return $this->client->delete('/gists/' . (int) $gist_id)->body;
	}

	public function getComments($gist_id)
	{
		return $this->client->get('/gists/' . (int) $gist_id . '/comments')->body;
	}

	public function getComment($comment_id)
	{
		return $this->client->get('/gists/comments/' . (int) $comment_id)->body;
	}

	public function createComment($gist_id, $comment)
	{
		return $this->client->post('/gists/' . (int) $gist_id . '/comments', array('body' => $comment))->body;
	}

	public function editComment($comment_id, $comment)
	{
		return $this->client->patch('/gists/comments/' . (int) $comment_id, array('body' => $comment))->body;
	}

	public function deleteComment($comment_id)
	{
		return $this->client->delete('/gists/comments/' . (int) $comment_id)->body;
	}
}
