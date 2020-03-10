<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Pulls Comments class for the Joomla Platform.
 *
 * @documentation https://developer.github.com/v3/pulls/comments/
 *
 * @since       3.3 (CMS)
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackagePullsComments extends JGithubPackage
{
	/**
	 * Method to create a comment on a pull request.
	 *
	 * @param   string   $user      The name of the owner of the GitHub repository.
	 * @param   string   $repo      The name of the GitHub repository.
	 * @param   integer  $pullId    The pull request number.
	 * @param   string   $body      The comment body text.
	 * @param   string   $commitId  The SHA1 hash of the commit to comment on.
	 * @param   string   $filePath  The Relative path of the file to comment on.
	 * @param   string   $position  The line index in the diff to comment on.
	 *
	 * @throws DomainException
	 * @since   1.7.3
	 *
	 * @return  object
	 */
	public function create($user, $repo, $pullId, $body, $commitId, $filePath, $position)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId . '/comments';

		// Build the request data.
		$data = json_encode(
			array(
				'body' => $body,
				'commit_id' => $commitId,
				'path' => $filePath,
				'position' => $position,
			)
		);

		// Send the request.
		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), $data),
			201
		);
	}

	/**
	 * Method to create a comment in reply to another comment.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   integer  $pullId     The pull request number.
	 * @param   string   $body       The comment body text.
	 * @param   integer  $inReplyTo  The id of the comment to reply to.
	 *
	 * @throws DomainException
	 * @since   1.7.3
	 *
	 * @return  object
	 */
	public function createReply($user, $repo, $pullId, $body, $inReplyTo)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId . '/comments';

		// Build the request data.
		$data = json_encode(
			array(
				'body' => $body,
				'in_reply_to' => (int) $inReplyTo,
			)
		);

		// Send the request.
		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), $data),
			201
		);
	}

	/**
	 * Method to delete a comment on a pull request.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   integer  $commentId  The id of the comment to delete.
	 *
	 * @throws DomainException
	 * @since   1.7.3
	 *
	 * @return  void
	 */
	public function delete($user, $repo, $commentId)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/comments/' . (int) $commentId;

		// Send the request.
		$this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}

	/**
	 * Method to update a comment on a pull request.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   integer  $commentId  The id of the comment to update.
	 * @param   string   $body       The new body text for the comment.
	 *
	 * @throws DomainException
	 * @since   1.7.3
	 *
	 * @return  object
	 */
	public function edit($user, $repo, $commentId, $body)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/comments/' . (int) $commentId;

		// Build the request data.
		$data = json_encode(
			array(
				'body' => $body,
			)
		);

		// Send the request.
		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), $data)
		);
	}

	/**
	 * Method to get a specific comment on a pull request.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   integer  $commentId  The comment id to get.
	 *
	 * @throws DomainException
	 * @since   1.7.3
	 *
	 * @return  object
	 */
	public function get($user, $repo, $commentId)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/comments/' . (int) $commentId;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Method to get the list of comments on a pull request.
	 *
	 * @param   string   $user    The name of the owner of the GitHub repository.
	 * @param   string   $repo    The name of the GitHub repository.
	 * @param   integer  $pullId  The pull request number.
	 * @param   integer  $page    The page number from which to get items.
	 * @param   integer  $limit   The number of items on a page.
	 *
	 * @throws DomainException
	 * @since   1.7.3
	 *
	 * @return  array
	 */
	public function getList($user, $repo, $pullId, $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/pulls/' . (int) $pullId . '/comments';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path, $page, $limit))
		);
	}
}
