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
 * GitHub API References class for the Joomla Platform.
 *
 * @documentation https://developer.github.com/v3/git/refs/
 *
 * @since       11.3
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageDataRefs extends JGithubPackage
{
	/**
	 * Method to get a reference.
	 *
	 * @param   string  $user  The name of the owner of the GitHub repository.
	 * @param   string  $repo  The name of the GitHub repository.
	 * @param   string  $ref   The reference to get.
	 *
	 * @return  object
	 *
	 * @since   11.3
	 */
	public function get($user, $repo, $ref)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/git/refs/' . $ref;

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
	 * Method to list references for a repository.
	 *
	 * @param   string   $user       The name of the owner of the GitHub repository.
	 * @param   string   $repo       The name of the GitHub repository.
	 * @param   string   $namespace  Optional sub-namespace to limit the returned references.
	 * @param   integer  $page       Page to request
	 * @param   integer  $limit      Number of results to return per page
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function getList($user, $repo, $namespace = '', $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/git/refs' . $namespace;

		// Send the request.
		$response = $this->client->get($this->fetchUrl($path, $page, $limit));

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
	 * Method to create a ref.
	 *
	 * @param   string  $user  The name of the owner of the GitHub repository.
	 * @param   string  $repo  The name of the GitHub repository.
	 * @param   string  $ref   The name of the fully qualified reference.
	 * @param   string  $sha   The SHA1 value to set this reference to.
	 *
	 * @throws DomainException
	 * @since   11.3
	 *
	 * @return  object
	 */
	public function create($user, $repo, $ref, $sha)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/git/refs';

		// Build the request data.
		$data = json_encode(
			array(
				'ref' => $ref,
				'sha' => $sha,
			)
		);

		// Send the request.
		$response = $this->client->post($this->fetchUrl($path), $data);

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
	 * Method to update a reference.
	 *
	 * @param   string   $user   The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 * @param   string   $ref    The reference to update.
	 * @param   string   $sha    The SHA1 value to set the reference to.
	 * @param   boolean  $force  Whether the update should be forced. Default to false.
	 *
	 * @throws DomainException
	 * @since   11.3
	 *
	 * @return  object
	 */
	public function edit($user, $repo, $ref, $sha, $force = false)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/git/refs/' . $ref;

		// Craete the data object.
		$data = new stdClass;

		// If a title is set add it to the data object.
		if ($force)
		{
			$data->force = true;
		}

		$data->sha = $sha;

		// Encode the request data.
		$data = json_encode($data);

		// Send the request.
		$response = $this->client->patch($this->fetchUrl($path), $data);

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
	 * Delete a Reference
	 *
	 * @param   string  $owner  The name of the owner of the GitHub repository.
	 * @param   string  $repo   The name of the GitHub repository.
	 * @param   string  $ref    The reference to update.
	 *
	 * @since   3.3 (CMS)
	 * @return object
	 */
	public function delete($owner, $repo, $ref)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/git/refs/' . $ref;

		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}
}
