<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Account class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub
 * @since       12.3
 */
class JGithubAccount extends JGithubObject
{
	/**
	 * Method to create an authorisation.
	 *
	 * @param   array   $scopes  A list of scopes that this authorisation is in.
	 * @param   string  $note    A note to remind you what the OAuth token is for.
	 * @param   string  $url     A URL to remind you what app the OAuth token is for.
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @throws  DomainException
	 */
	public function createAuthorisation(array $scopes = array(), $note = '', $url = '')
	{
		// Build the request path.
		$path = '/authorizations';

		$data = json_encode(
			array('scopes' => $scopes, 'note' => $note, 'note_url' => $url)
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
	 * Method to delete an authorisation
	 *
	 * @param   integer  $id  ID of the authorisation to delete
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @throws  DomainException
	 */
	public function deleteAuthorisation($id)
	{
		// Build the request path.
		$path = '/authorizations/' . $id;

		// Send the request.
		$response = $this->client->delete($this->fetchUrl($path));

		// Validate the response code.
		if ($response->code != 204)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}

	/**
	 * Method to edit an authorisation.
	 *
	 * @param   integer  $id            ID of the authorisation to edit
	 * @param   array    $scopes        Replaces the authorisation scopes with these.
	 * @param   array    $addScopes     A list of scopes to add to this authorisation.
	 * @param   array    $removeScopes  A list of scopes to remove from this authorisation.
	 * @param   string   $note          A note to remind you what the OAuth token is for.
	 * @param   string   $url           A URL to remind you what app the OAuth token is for.
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @throws  DomainException
	 * @throws  RuntimeException
	 */
	public function editAuthorisation($id, array $scopes = array(), array $addScopes = array(), array $removeScopes = array(), $note = '', $url = '')
	{
		// Check if more than one scopes array contains data
		$scopesCount = 0;

		if (!empty($scopes))
		{
			$scope = 'scopes';
			$scopeData = $scopes;
			$scopesCount++;
		}
		if (!empty($addScopes))
		{
			$scope = 'add_scopes';
			$scopeData = $addScopes;
			$scopesCount++;
		}
		if (!empty($removeScopes))
		{
			$scope = 'remove_scopes';
			$scopeData = $removeScopes;
			$scopesCount++;
		}

		// Only allowed to send data for one scope parameter
		if ($scopesCount >= 2)
		{
			throw new RuntimeException('You can only send one scope key in this request.');
		}

		// Build the request path.
		$path = '/authorizations/' . $id;

		$data = json_encode(
			array(
				$scope => $scopeData,
				'note' => $note,
				'note_url' => $url
			)
		);

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
	 * Method to get details about an authorised application for the authenticated user.
	 *
	 * @param   integer  $id  ID of the authorisation to retrieve
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @note    This method will only accept Basic Authentication
	 * @throws  DomainException
	 */
	public function getAuthorisation($id)
	{
		// Build the request path.
		$path = '/authorizations/' . $id;

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
	 * Method to get the authorised applications for the authenticated user.
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @throws  DomainException
	 * @note    This method will only accept Basic Authentication
	 */
	public function getAuthorisations()
	{
		// Build the request path.
		$path = '/authorizations';

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
	 * Method to get the rate limit for the authenticated user.
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @throws  DomainException
	 */
	public function getRateLimit()
	{
		// Build the request path.
		$path = '/rate_limit';

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
