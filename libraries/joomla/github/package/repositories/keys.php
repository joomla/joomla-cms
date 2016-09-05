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
 * GitHub API Forks class for the Joomla Platform.
 *
 * @documentation https://developer.github.com/v3/repos/keys
 *
 * @since       11.3
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageRepositoriesKeys extends JGithubPackage
{
	/**
	 * List keys in a repository.
	 *
	 * @param   string  $owner  The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 *
	 * @since 12.4
	 *
	 * @return object
	 */
	public function getList($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/keys';

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Get a key.
	 *
	 * @param   string   $owner  The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 * @param   integer  $id     The id of the key.
	 *
	 * @since 12.4
	 *
	 * @return object
	 */
	public function get($owner, $repo, $id)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/keys/' . (int) $id;

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Create a key.
	 *
	 * @param   string  $owner  The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $title  The key title.
	 * @param   string  $key    The key.
	 *
	 * @since 12.4
	 *
	 * @return object
	 */
	public function create($owner, $repo, $title, $key)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/keys';

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
	 * Edit a key.
	 *
	 * @param   string   $owner  The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 * @param   integer  $id     The id of the key.
	 * @param   string   $title  The key title.
	 * @param   string   $key    The key.
	 *
	 * @since 12.4
	 *
	 * @return object
	 */
	public function edit($owner, $repo, $id, $title, $key)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/keys/' . (int) $id;

		$data = array(
			'title' => $title,
			'key'   => $key,
		);

		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), json_encode($data))
		);
	}

	/**
	 * Delete a key.
	 *
	 * @param   string   $owner  The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 * @param   integer  $id     The id of the key.
	 *
	 * @since 12.4
	 *
	 * @return boolean
	 */
	public function delete($owner, $repo, $id)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/keys/' . (int) $id;

		$this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);

		return true;
	}
}
