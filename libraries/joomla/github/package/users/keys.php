<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API References class for the Joomla Platform.
 *
 * @documentation https://developer.github.com/v3/users/keys
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageUsersKeys extends JGithubPackage
{
	/**
	 * List public keys for a user.
	 *
	 * Lists the verified public keys for a user. This is accessible by anyone.
	 *
	 * @param   string  $user  The name of the user.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function getListUser($user)
	{
		// Build the request path.
		$path = '/users/' . $user . '/keys';

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * List your public keys.
	 *
	 * Lists the current user’s keys.
	 * Management of public keys via the API requires that you are authenticated
	 * through basic auth, or OAuth with the ‘user’ scope.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function getList()
	{
		// Build the request path.
		$path = '/users/keys';

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Get a single public key.
	 *
	 * @param   integer  $id  The id of the key.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function get($id)
	{
		// Build the request path.
		$path = '/users/keys/' . $id;

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Create a public key
	 *
	 * @param   string  $title  The title of the key.
	 * @param   string  $key    The key.
	 *
	 * @since    3.3 (CMS)
	 *
	 * @return object
	 */
	public function create($title, $key)
	{
		// Build the request path.
		$path = '/users/keys';

		$data = array(
			'title' => $title,
			'key'   => $key,
		);

		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), json_encode($data)),
			201
		);
	}

	/**
	 * Update a public key.
	 *
	 * @param   integer  $id     The id of the key.
	 * @param   string   $title  The title of the key.
	 * @param   string   $key    The key.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function edit($id, $title, $key)
	{
		// Build the request path.
		$path = '/users/keys/' . $id;

		$data = array(
			'title' => $title,
			'key'   => $key,
		);

		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), json_encode($data))
		);
	}

	/**
	 * Delete a public key.
	 *
	 * @param   integer  $id  The id of the key.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function delete($id)
	{
		// Build the request path.
		$path = '/users/keys/' . (int) $id;

		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}
}
