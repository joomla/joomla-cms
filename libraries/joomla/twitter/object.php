<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Twitter API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
abstract class JTwitterObject
{
	/**
	 * @var    JRegistry  Options for the Twitter object.
	 * @since  12.1
	 */
	protected $options;

	/**
	 * @var    JTwitterHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  12.1
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry     &$options  Twitter options object.
	 * @param   JTwitterHttp  $client    The HTTP client object.
	 *
	 * @since   12.1
	 */
	public function __construct(JRegistry &$options = null, JTwitterHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JTwitterHttp($this->options);
	}

	/**
	 * Method to check the rate limit for the requesting IP address
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function checkRateLimit()
	{
		// Check the rate limit for remaining hits
		$rate_limit = $this->getRateLimit();

		if ($rate_limit->remaining_hits == 0)
		{
			// The IP has exceeded the Twitter API rate limit
			throw new RuntimeException('This server has exceed the Twitter API rate limit for the given period.  The limit will reset at '
						. $rate_limit->reset_time
			);
		}
	}

	/**
	 * Method to build and return a full request URL for the request.  This method will
	 * add appropriate pagination details if necessary and also prepend the API url
	 * to have a complete URL for the request.
	 *
	 * @param   string  $path  URL to inflect
	 *
	 * @return  string  The request URL.
	 *
	 * @since   12.1
	 */
	protected function fetchUrl($path)
	{
		// Get a new JUri object fousing the api url and given path.
		$uri = new JUri($this->options->get('api.url') . $path);

		if ($this->options->get('api.username', false))
		{
			$uri->setUser($this->options->get('api.username'));
		}

		if ($this->options->get('api.password', false))
		{
			$uri->setPass($this->options->get('api.password'));
		}

		return (string) $uri;
	}

	/**
	 * Method to retrieve the rate limit for the requesting IP address
	 *
	 * @return  array  The JSON response decoded
	 *
	 * @since   12.1
	 */
	public function getRateLimit()
	{
		// Build the request path.
		$path = '/1/account/rate_limit_status.json';

		// Send the request.
		return $this->sendRequest($path, 200);
	}

	/**
	 * Method to send the request.
	 *
	 * @param   string   $path  The path of the request to make
	 * @param   integer  $code  The expected response code
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  DomainException
	 */
	public function sendRequest($path, $code)
	{
		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		// Validate the response code.
		if ($response->code != $code)
		{
			$error = json_decode($response->body);

			throw new DomainException($error->error, $response->code);
		}

		return json_decode($response->body);
	}
}
