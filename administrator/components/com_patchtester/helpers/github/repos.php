<?php
/**
 * @package    PatchTester
 *
 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

/**
 * GitHub API Repos class.
 *
 * @package  PatchTester
 * @since    2.0
 */
class PTGithubRepos extends JGithubObject
{
	/**
	 * Retrieve information about the specified repository
	 *
	 * @param   string  $user  The username or organization name of the repository owner
	 * @param   string  $repo  The repository to retrieve
	 *
	 * @return  object
	 *
	 * @since   2.0
	 * @throws  DomainException
	 */
	public function get($user, $repo)
	{
		$path = '/repos/' . $user . '/' . $repo;

		// Send the request.
		return $this->processResponse($this->client->get($this->fetchUrl($path)));
	}

	/**
	 * List public repositories for the specified user.
	 *
	 * @param   string  $user  The username to retrieve repositories for
	 *
	 * @return  object
	 *
	 * @since   2.0
	 * @throws  DomainException
	 */
	public function getPublicRepos($user)
	{
		$path = '/users/' . $user . '/repos';

		// Send the request.
		return $this->processResponse($this->client->get($this->fetchUrl($path)));
	}
}
