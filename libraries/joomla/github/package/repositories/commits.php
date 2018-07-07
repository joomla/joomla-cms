<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Repositories Commits class for the Joomla Platform.
 *
 * @documentation https://developer.github.com/v3/repos/commits
 *
 * @since       11.3
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageRepositoriesCommits extends JGithubPackage
{
	/**
	 * Method to list commits for a repository.
	 *
	 * A special note on pagination: Due to the way Git works, commits are paginated based on SHA
	 * instead of page number.
	 * Please follow the link headers as outlined in the pagination overview instead of constructing
	 * page links yourself.
	 *
	 * @param   string  $user    The name of the owner of the GitHub repository.
	 * @param   string  $repo    The name of the GitHub repository.
	 * @param   string  $sha     Sha or branch to start listing commits from.
	 * @param   string  $path    Only commits containing this file path will be returned.
	 * @param   string  $author  GitHub login, name, or email by which to filter by commit author.
	 * @param   JDate   $since   ISO 8601 Date - Only commits after this date will be returned.
	 * @param   JDate   $until   ISO 8601 Date - Only commits before this date will be returned.
	 *
	 * @throws DomainException
	 * @since    12.1
	 *
	 * @return  array
	 */
	public function getList($user, $repo, $sha = '', $path = '', $author = '', JDate $since = null, JDate $until = null)
	{
		// Build the request path.
		$rPath = '/repos/' . $user . '/' . $repo . '/commits?';

		$rPath .= ($sha) ? '&sha=' . $sha : '';
		$rPath .= ($path) ? '&path=' . $path : '';
		$rPath .= ($author) ? '&author=' . $author : '';
		$rPath .= ($since) ? '&since=' . $since->toISO8601() : '';
		$rPath .= ($until) ? '&until=' . $until->toISO8601() : '';

		// Send the request.
		$response = $this->client->get($this->fetchUrl($rPath));

		// Validate the response code.
		if ($response->code != 200)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}

	/**
	 * Method to get a single commit for a repository.
	 *
	 * @param   string  $user  The name of the owner of the GitHub repository.
	 * @param   string  $repo  The name of the GitHub repository.
	 * @param   string  $sha   The SHA of the commit to retrieve.
	 *
	 * @throws DomainException
	 * @since   12.1
	 *
	 * @return  array
	 */
	public function get($user, $repo, $sha)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/commits/' . $sha;

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		// Validate the response code.
		if ($response->code != 200)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}

	/**
	 * Method to get a diff for two commits.
	 *
	 * @param   string  $user  The name of the owner of the GitHub repository.
	 * @param   string  $repo  The name of the GitHub repository.
	 * @param   string  $base  The base of the diff, either a commit SHA or branch.
	 * @param   string  $head  The head of the diff, either a commit SHA or branch.
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	public function compare($user, $repo, $base, $head)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/compare/' . $base . '...' . $head;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}
}
