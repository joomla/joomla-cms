<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Repositories Comments class for the Joomla Platform.
 *
 * @documentation http://developer.github.com/v3/repos/comments
 *
 * @since  11.3
 */
class JGithubPackageRepositoriesComments extends JGithubPackage
{
	/**
	 * Method to get a list of commit comments for a repository.
	 *
	 * @param   string   $user   The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 * @param   integer  $page   Page to request
	 * @param   integer  $limit  Number of results to return per page
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	public function getListRepository($user, $repo, $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/comments';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path, $page, $limit))
		);
	}

	/**
	 * Method to get a list of comments for a single commit for a repository.
	 *
	 * @param   string   $user   The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 * @param   string   $sha    The SHA of the commit to retrieve.
	 * @param   integer  $page   Page to request
	 * @param   integer  $limit  Number of results to return per page
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	public function getList($user, $repo, $sha, $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/commits/' . $sha . '/comments';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path, $page, $limit))
		);
	}

	/**
	 * Method to get a single comment on a commit.
	 *
	 * @param   string   $user  The name of the owner of the GitHub repository.
	 * @param   string   $repo  The name of the GitHub repository.
	 * @param   integer  $id    ID of the comment to retrieve
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	public function get($user, $repo, $id)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/comments/' . (int) $id;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Method to edit a comment on a commit.
	 *
	 * @param   string  $user     The name of the owner of the GitHub repository.
	 * @param   string  $repo     The name of the GitHub repository.
	 * @param   string  $id       The ID of the comment to edit.
	 * @param   string  $comment  The text of the comment.
	 *
	 * @return  object
	 *
	 * @since   12.1
	 */
	public function edit($user, $repo, $id, $comment)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/comments/' . $id;

		$data = json_encode(
			array(
				'body' => $comment
			)
		);

		// Send the request.
		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), $data)
		);
	}

	/**
	 * Method to delete a comment on a commit.
	 *
	 * @param   string  $user  The name of the owner of the GitHub repository.
	 * @param   string  $repo  The name of the GitHub repository.
	 * @param   string  $id    The ID of the comment to edit.
	 *
	 * @return  object
	 *
	 * @since   12.1
	 */
	public function delete($user, $repo, $id)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/comments/' . $id;

		// Send the request.
		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}

	/**
	 * Method to create a comment on a commit.
	 *
	 * @param   string   $user      The name of the owner of the GitHub repository.
	 * @param   string   $repo      The name of the GitHub repository.
	 * @param   string   $sha       The SHA of the commit to comment on.
	 * @param   string   $comment   The text of the comment.
	 * @param   integer  $line      The line number of the commit to comment on.
	 * @param   string   $filepath  A relative path to the file to comment on within the commit.
	 * @param   integer  $position  Line index in the diff to comment on.
	 *
	 * @return  object
	 *
	 * @since   12.1
	 */
	public function create($user, $repo, $sha, $comment, $line, $filepath, $position)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/commits/' . $sha . '/comments';

		$data = json_encode(
			array(
				'body' => $comment,
				'path' => $filepath,
				'position' => (int) $position,
				'line' => (int) $line
			)
		);

		// Send the request.
		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), $data),
			201
		);
	}
}
