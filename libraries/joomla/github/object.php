<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * GitHub API object class for the Joomla Platform.
 *
 * @since       1.7.3
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
abstract class JGithubObject
{
	/**
	 * @var    Registry  Options for the GitHub object.
	 * @since  1.7.3
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  1.7.3
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   Registry     $options  GitHub options object.
	 * @param   JGithubHttp  $client   The HTTP client object.
	 *
	 * @since   1.7.3
	 */
	public function __construct(Registry $options = null, JGithubHttp $client = null)
	{
		$this->options = isset($options) ? $options : new Registry;
		$this->client  = isset($client) ? $client : new JGithubHttp($this->options);
	}

	/**
	 * Method to build and return a full request URL for the request.  This method will
	 * add appropriate pagination details if necessary and also prepend the API url
	 * to have a complete URL for the request.
	 *
	 * @param   string   $path   URL to inflect
	 * @param   integer  $page   Page to request
	 * @param   integer  $limit  Number of results to return per page
	 *
	 * @return  string   The request URL.
	 *
	 * @since   1.7.3
	 */
	protected function fetchUrl($path, $page = 0, $limit = 0)
	{
		// Get a new JUri object fousing the api url and given path.
		$uri = new JUri($this->options->get('api.url') . $path);

		if ($this->options->get('gh.token', false))
		{
			// Use oAuth authentication - @todo set in request header ?
			$uri->setVar('access_token', $this->options->get('gh.token'));
		}
		else
		{
			// Use basic authentication
			if ($this->options->get('api.username', false))
			{
				$username = $this->options->get('api.username');
				$username = str_replace('@', '%40', $username);
				$uri->setUser($username);
			}

			if ($this->options->get('api.password', false))
			{
				$password = $this->options->get('api.password');
				$password = str_replace('@', '%40', $password);
				$uri->setPass($password);
			}
		}

		// If we have a defined page number add it to the JUri object.
		if ($page > 0)
		{
			$uri->setVar('page', (int) $page);
		}

		// If we have a defined items per page add it to the JUri object.
		if ($limit > 0)
		{
			$uri->setVar('per_page', (int) $limit);
		}

		return (string) $uri;
	}

	/**
	 * Process the response and decode it.
	 *
	 * @param   JHttpResponse  $response      The response.
	 * @param   integer        $expectedCode  The expected "good" code.
	 * @param   boolean        $decode        If the should be response be JSON decoded.
	 *
	 * @throws DomainException
	 * @since  3.3.0
	 *
	 * @return mixed
	 */
	protected function processResponse(JHttpResponse $response, $expectedCode = 200, $decode = true)
	{
		// Validate the response code.
		if ($response->code == $expectedCode)
		{
			return ($decode) ? json_decode($response->body) : $response->body;
		}

		// Decode the error response and throw an exception.
		$error   = json_decode($response->body);
		$message = (isset($error->message)) ? $error->message : 'Error: ' . $response->code;

		throw new DomainException($message, $response->code);
	}
}
