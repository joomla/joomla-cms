<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API References class for the Joomla Platform.
 *
 * @documentation https://developer.github.com/v3/users
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageUsers extends JGithubPackage
{
	protected $name = 'Users';

	protected $packages = array('emails', 'followers', 'keys');

	/**
	 * Get a single user.
	 *
	 * @param   string  $user  The users login name.
	 *
	 * @throws DomainException
	 *
	 * @return object
	 */
	public function get($user)
	{
		// Build the request path.
		$path = '/users/' . $user;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
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
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Update a user.
	 *
	 * @param   string  $name      The full name
	 * @param   string  $email     The email
	 * @param   string  $blog      The blog
	 * @param   string  $company   The company
	 * @param   string  $location  The location
	 * @param   string  $hireable  If he is unemployed :P
	 * @param   string  $bio       The biometrical DNA fingerprint (or something...)
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	public function edit($name = '', $email = '', $blog = '', $company = '', $location = '', $hireable = '', $bio = '')
	{
		$data = array(
			'name'     => $name,
			'email'    => $email,
			'blog'     => $blog,
			'company'  => $company,
			'location' => $location,
			'hireable' => $hireable,
			'bio'      => $bio,
		);

		// Build the request path.
		$path = '/user';

		// Send the request.
		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), json_encode($data))
		);
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
	public function getList($since = 0)
	{
		// Build the request path.
		$path = '/users';

		$path .= ($since) ? '?since=' . $since : '';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/*
	 * Legacy methods
	 */

	/**
	 * Get a single user.
	 *
	 * @param   string  $user  The users login name.
	 *
	 * @deprecated use users->get()
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	public function getUser($user)
	{
		return $this->get($user);
	}

	/**
	 * Update a user.
	 *
	 * @param   string  $name      The full name
	 * @param   string  $email     The email
	 * @param   string  $blog      The blog
	 * @param   string  $company   The company
	 * @param   string  $location  The location
	 * @param   string  $hireable  If he is unemployed :P
	 * @param   string  $bio       The biometrical DNA fingerprint (or something...)
	 *
	 * @deprecated use users->edit()
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	public function updateUser($name = '', $email = '', $blog = '', $company = '', $location = '', $hireable = '', $bio = '')
	{
		return $this->edit($name = '', $email = '', $blog = '', $company = '', $location = '', $hireable = '', $bio = '');
	}

	/**
	 * Get all users.
	 *
	 * This provides a dump of every user, in the order that they signed up for GitHub.
	 *
	 * @param   integer  $since  The integer ID of the last User that you’ve seen.
	 *
	 * @deprecated use users->getList()
	 *
	 * @throws DomainException
	 * @return mixed
	 */
	public function getUsers($since = 0)
	{
		return $this->getList($since);
	}
}
