<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Comments class for the Joomla Platform.
 *
 * The Issue Comments API supports listing, viewing, editing, and creating comments
 * on issues and pull requests.
 *
 * @documentation http://developer.github.com/v3/issues/comments/
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub.Issues
 * @since       12.3
 */
class JGithubPackageIssuesComments extends JGithubPackage
{
	/**
	 * Method to get the list of comments on an issue.
	 *
	 * @param   string   $owner    The name of the owner of the GitHub repository.
	 * @param   string   $repo     The name of the GitHub repository.
	 * @param   integer  $issueId  The issue number.
	 * @param   integer  $page     The page number from which to get items.
	 * @param   integer  $limit    The number of items on a page.
	 *
	 * @throws DomainException
	 * @since   11.3
	 *
	 * @return  array
	 */
	public function getList($owner, $repo, $issueId, $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/issues/' . (int) $issueId . '/comments';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path, $page, $limit))
		);
	}

	/**
	 * Method to get the list of comments in a repository.
	 *
	 * @param   string  $owner      The name of the owner of the GitHub repository.
	 * @param   string  $repo       The name of the GitHub repository.
	 * @param   string  $sort       The sort field - created or updated.
	 * @param   string  $direction  The sort order- asc or desc. Ignored without sort parameter.
	 * @param   JDate   $since      A timestamp in ISO 8601 format.
	 *
	 * @throws UnexpectedValueException
	 * @throws DomainException
	 * @since   11.3
	 *
	 * @return  array
	 */
	public function getRepositoryList($owner, $repo, $sort = 'created', $direction = 'asc', JDate $since = null)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/issues/comments';

		if (false == in_array($sort, array('created', 'updated')))
		{
			throw new UnexpectedValueException(
				sprintf(
					'%1$s - sort field must be "created" or "updated"', __METHOD__
				)
			);
		}

		if (false == in_array($direction, array('asc', 'desc')))
		{
			throw new UnexpectedValueException(
				sprintf(
					'%1$s - direction field must be "asc" or "desc"', __METHOD__
				)
			);
		}

		$path .= '?sort=' . $sort;
		$path .= '&direction=' . $direction;

		if ($since)
		{
			$path .= '&since=' . $since->toISO8601();
		}

		// Send the request.
		return $this->processResponse($this->client->get($this->fetchUrl($path)));
	}

	/**
	 * Method to get a single comment.
	 *
	 * @param   string   $owner  The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 * @param   integer  $id     The comment id.
	 *
	 * @return mixed
	 */
	public function get($owner, $repo, $id)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/issues/comments/' . (int) $id;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Method to update a comment on an issue.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   integer  $commentId  The id of the comment to update.
	 * @param   string   $body       The new body text for the comment.
	 *
	 * @since   11.3
	 * @throws DomainException
	 *
	 * @return  object
	 */
	public function edit($user, $repo, $commentId, $body)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/issues/comments/' . (int) $commentId;

		// Build the request data.
		$data = json_encode(
			array(
				'body' => $body
			)
		);

		// Send the request.
		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), $data)
		);
	}

	/**
	 * Method to create a comment on an issue.
	 *
	 * @param   string   $user     The name of the owner of the GitHub repository.
	 * @param   string   $repo     The name of the GitHub repository.
	 * @param   integer  $issueId  The issue number.
	 * @param   string   $body     The comment body text.
	 *
	 * @throws DomainException
	 * @since   11.3
	 *
	 * @return  object
	 */
	public function create($user, $repo, $issueId, $body)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/issues/' . (int) $issueId . '/comments';

		// Build the request data.
		$data = json_encode(
			array(
				'body' => $body,
			)
		);

		// Send the request.
		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), $data),
			201
		);
	}

	/**
	 * Method to delete a comment on an issue.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   integer  $commentId  The id of the comment to delete.
	 *
	 * @throws DomainException
	 * @since   11.3
	 *
	 * @return  boolean
	 */
	public function delete($user, $repo, $commentId)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/issues/comments/' . (int) $commentId;

		// Send the request.
		$this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);

		return true;
	}
}
