<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API References class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub
 * @since       12.3
 */
class JGithubUsers extends JGithubObject
{
	/**
	 * Get a single user.
	 *
	 * @param   string  $user  The users login name.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	public function getUser($user)
	{
		// Build the request path.
		$path = '/users/' . $user;

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
	 * Get the current authenticated user.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	public function getAuthenticatedUser()
	{
		// Build the request path.
		$path = '/user';

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
	 * Update a user.
	 *
	 * @param   string  $name      The full name
	 * @param   string  $email     The email
	 * @param   string  $blog      The blog
	 * @param   string  $company   The company
	 * @param   string  $location  The location
	 * @param   string  $hireable  If he is unemplayed :P
	 * @param   string  $bio       The biometrical DNA fingerprint (or smthng...)
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	public function updateUser($name = '', $email = '', $blog = '', $company = '', $location = '', $hireable = '', $bio = '')
	{
		$data = array(
			'name'     => $name,
			'email'    => $email,
			'blog'     => $blog,
			'company'  => $company,
			'location' => $location,
			'hireable' => $hireable,
			'bio'      => $bio
		);

		// Build the request path.
		$path = '/user';

		// Send the request.
		$response = $this->client->patch($this->fetchUrl($path), json_encode($data));

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
	 * Get all users.
	 *
	 * This provides a dump of every user, in the order that they signed up for GitHub.
	 *
	 * @param   integer  $since  The integer ID of the last User that you’ve seen.
	 *
	 * @throws DomainException
	 * @return mixed
	 */
	public function getUsers($since = 0)
	{
		// Build the request path.
		$path = '/users';

		$path .= ($since) ? '?since=' . $since : '';

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

}
