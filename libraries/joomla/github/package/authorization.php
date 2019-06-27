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
 * GitHub API Authorization class for the Joomla Platform.
 *
 * @documentation https://developer.github.com/v3/oauth/
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageAuthorization extends JGithubPackage
{
	/**
	 * Method to create an authorization.
	 *
	 * @param   array   $scopes  A list of scopes that this authorization is in.
	 * @param   string  $note    A note to remind you what the OAuth token is for.
	 * @param   string  $url     A URL to remind you what app the OAuth token is for.
	 *
	 * @throws DomainException
	 * @since   3.1.4
	 *
	 * @return  object
	 */
	public function create(array $scopes = array(), $note = '', $url = '')
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
	 * Method to delete an authorization
	 *
	 * @param   integer  $id  ID of the authorization to delete
	 *
	 * @throws DomainException
	 * @since   3.1.4
	 *
	 * @return  object
	 */
	public function delete($id)
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
	 * Method to edit an authorization.
	 *
	 * @param   integer  $id            ID of the authorization to edit
	 * @param   array    $scopes        Replaces the authorization scopes with these.
	 * @param   array    $addScopes     A list of scopes to add to this authorization.
	 * @param   array    $removeScopes  A list of scopes to remove from this authorization.
	 * @param   string   $note          A note to remind you what the OAuth token is for.
	 * @param   string   $url           A URL to remind you what app the OAuth token is for.
	 *
	 * @throws RuntimeException
	 * @throws DomainException
	 * @since   3.1.4
	 *
	 * @return  object
	 */
	public function edit($id, array $scopes = array(), array $addScopes = array(), array $removeScopes = array(), $note = '', $url = '')
	{
		// Check if more than one scopes array contains data
		$scopesCount = 0;

		if (!empty($scopes))
		{
			$scope     = 'scopes';
			$scopeData = $scopes;
			$scopesCount++;
		}

		if (!empty($addScopes))
		{
			$scope     = 'add_scopes';
			$scopeData = $addScopes;
			$scopesCount++;
		}

		if (!empty($removeScopes))
		{
			$scope     = 'remove_scopes';
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
				$scope     => $scopeData,
				'note'     => $note,
				'note_url' => $url,
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
	 * @param   integer  $id  ID of the authorization to retrieve
	 *
	 * @throws DomainException
	 * @since   3.1.4
	 * @note    This method will only accept Basic Authentication
	 *
	 * @return  object
	 */
	public function get($id)
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
	 * @throws DomainException
	 * @since   3.1.4
	 * @note    This method will only accept Basic Authentication
	 *
	 * @return  object
	 */
	public function getList()
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
	 * @throws DomainException
	 * @since   3.1.4
	 *
	 * @return  object
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

	/**
	 * 1. Request authorization on GitHub.
	 *
	 * @param   string  $client_id     The client ID you received from GitHub when you registered.
	 * @param   string  $redirect_uri  URL in your app where users will be sent after authorization.
	 * @param   string  $scope         Comma separated list of scopes.
	 * @param   string  $state         An unguessable random string. It is used to protect against
	 *                                 cross-site request forgery attacks.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return JUri
	 */
	public function getAuthorizationLink($client_id, $redirect_uri = '', $scope = '', $state = '')
	{
		$uri = new JUri('https://github.com/login/oauth/authorize');

		$uri->setVar('client_id', $client_id);

		if ($redirect_uri)
		{
			$uri->setVar('redirect_uri', urlencode($redirect_uri));
		}

		if ($scope)
		{
			$uri->setVar('scope', $scope);
		}

		if ($state)
		{
			$uri->setVar('state', $state);
		}

		return (string) $uri;
	}

	/**
	 * 2. Request the access token.
	 *
	 * @param   string  $client_id      The client ID you received from GitHub when you registered.
	 * @param   string  $client_secret  The client secret you received from GitHub when you registered.
	 * @param   string  $code           The code you received as a response to Step 1.
	 * @param   string  $redirect_uri   URL in your app where users will be sent after authorization.
	 * @param   string  $format         The response format (json, xml, ).
	 *
	 * @throws UnexpectedValueException
	 * @since 3.3 (CMS)
	 *
	 * @return string
	 */
	public function requestToken($client_id, $client_secret, $code, $redirect_uri = '', $format = '')
	{
		$uri = 'https://github.com/login/oauth/access_token';

		$data = array(
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'code'          => $code,
		);

		if ($redirect_uri)
		{
			$data['redirect_uri'] = $redirect_uri;
		}

		$headers = array();

		switch ($format)
		{
			case 'json' :
				$headers['Accept'] = 'application/json';
				break;
			case 'xml' :
				$headers['Accept'] = 'application/xml';
				break;
			default :
				if ($format)
				{
					throw new UnexpectedValueException('Invalid format');
				}
				break;
		}

		// Send the request.
		return $this->processResponse(
			$this->client->post($uri, $data, $headers),
			200, false
		);
	}
}
