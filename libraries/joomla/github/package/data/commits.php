<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Data Commits class for the Joomla Platform.
 *
 * @documentation http://developer.github.com/v3/git/commits/
 *
 * @since  11.3
 */
class JGithubPackageDataCommits extends JGithubPackage
{
	/**
	 * Get a single commit.
	 *
	 * @param   string  $owner  The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $sha    The commit SHA.
	 *
	 * @return object
	 */
	public function get($owner, $repo, $sha)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/git/commits/' . $sha;

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Method to create a commit.
	 *
	 * @param   string  $owner    The name of the owner of the GitHub repository.
	 * @param   string  $repo     The name of the GitHub repository.
	 * @param   string  $message  The commit message.
	 * @param   string  $tree     SHA of the tree object this commit points to.
	 * @param   array   $parents  Array of the SHAs of the commits that were the parents of this commit.
	 *                            If omitted or empty, the commit will be written as a root commit.
	 *                            For a single parent, an array of one SHA should be provided.
	 *                            For a merge commit, an array of more than one should be provided.
	 *
	 * @throws DomainException
	 * @since   12.1
	 *
	 * @return  object
	 */
	public function create($owner, $repo, $message, $tree, array $parents = array())
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/git/commits';

		$data = json_encode(
			array('message' => $message, 'tree' => $tree, 'parents' => $parents)
		);

		// Send the request.
		return $this->processResponse(
			$response = $this->client->post($this->fetchUrl($path), $data),
			201
		);
	}
}
