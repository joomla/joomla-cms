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
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubStatuses extends JGithubObject
{
	/**
	 * Method to create a status.
	 *
	 * @param   string  $user         The name of the owner of the GitHub repository.
	 * @param   string  $repo         The name of the GitHub repository.
	 * @param   string  $sha          The SHA1 value for which to set the status.
	 * @param   string  $state        The state (pending, success, error or failure).
	 * @param   string  $targetUrl    Optional target URL.
	 * @param   string  $description  Optional description for the status.
	 *
	 * @deprecated  use repositories->statuses->create()
	 *
	 * @return  object
	 *
	 * @since   3.1.4
	 */
	public function create($user, $repo, $sha, $state, $targetUrl = null, $description = null)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/statuses/' . $sha;

		if (!in_array($state, array('pending', 'success', 'error', 'failure')))
		{
			throw new InvalidArgumentException('State must be one of pending, success, error or failure.');
		}

		// Build the request data.
		$data = array(
			'state' => $state,
		);

		if (!is_null($targetUrl))
		{
			$data['target_url'] = $targetUrl;
		}

		if (!is_null($description))
		{
			$data['description'] = $description;
		}

		// Send the request.
		$response = $this->client->post($this->fetchUrl($path), json_encode($data));

		// Validate the response code.
		if ($response->code != 201)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}

	/**
	 * Method to list statuses for an SHA.
	 *
	 * @param   string  $user  The name of the owner of the GitHub repository.
	 * @param   string  $repo  The name of the GitHub repository.
	 * @param   string  $sha   SHA1 for which to get the statuses.
	 *
	 * @deprecated  use repositories->statuses->getList()
	 *
	 * @return  array
	 *
	 * @since   3.1.4
	 */
	public function getList($user, $repo, $sha)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/statuses/' . $sha;

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
