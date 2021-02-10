<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

use Joomla\Registry\Registry;

/**
 * Twitter API object class for the Joomla Platform.
 *
 * @since       3.1.4
 * @deprecated  4.0  Use the `joomla/twitter` package via Composer instead
 */
abstract class JTwitterObject
{
	/**
	 * @var    Registry  Options for the Twitter object.
	 * @since  3.1.4
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  3.1.4
	 */
	protected $client;

	/**
	 * @var    JTwitterOAuth The OAuth client.
	 * @since  3.1.4
	 */
	protected $oauth;

	/**
	 * Constructor.
	 *
	 * @param   Registry       &$options  Twitter options object.
	 * @param   JHttp          $client    The HTTP client object.
	 * @param   JTwitterOAuth  $oauth     The OAuth client.
	 *
	 * @since   3.1.4
	 */
	public function __construct(Registry &$options = null, JHttp $client = null, JTwitterOAuth $oauth = null)
	{
		$this->options = isset($options) ? $options : new Registry;
		$this->client = isset($client) ? $client : new JHttp($this->options);
		$this->oauth = $oauth;
	}

	/**
	 * Method to check the rate limit for the requesting IP address
	 *
	 * @param   string  $resource  A resource or a comma-separated list of resource families you want to know the current rate limit disposition for.
	 * @param   string  $action    An action for the specified resource, if only one resource is specified.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function checkRateLimit($resource = null, $action = null)
	{
		// Check the rate limit for remaining hits
		$rate_limit = $this->getRateLimit($resource);

		$property = '/' . $resource;

		if (!is_null($action))
		{
			$property .= '/' . $action;
		}

		if ($rate_limit->resources->$resource->$property->remaining == 0)
		{
			// The IP has exceeded the Twitter API rate limit
			throw new RuntimeException('This server has exceed the Twitter API rate limit for the given period.  The limit will reset at '
				. $rate_limit->resources->$resource->$property->reset
			);
		}
	}

	/**
	 * Method to build and return a full request URL for the request.  This method will
	 * add appropriate pagination details if necessary and also prepend the API url
	 * to have a complete URL for the request.
	 *
	 * @param   string  $path        URL to inflect
	 * @param   array   $parameters  The parameters passed in the URL.
	 *
	 * @return  string  The request URL.
	 *
	 * @since   3.1.4
	 */
	public function fetchUrl($path, $parameters = null)
	{
		if ($parameters)
		{
			foreach ($parameters as $key => $value)
			{
				if (strpos($path, '?') === false)
				{
					$path .= '?' . $key . '=' . $value;
				}
				else
				{
					$path .= '&' . $key . '=' . $value;
				}
			}
		}

		// Get a new JUri object focusing the api url and given path.
		if (strpos($path, 'http://search.twitter.com/search.json') === false)
		{
			$uri = new JUri($this->options->get('api.url') . $path);
		}
		else
		{
			$uri = new JUri($path);
		}

		return (string) $uri;
	}

	/**
	 * Method to retrieve the rate limit for the requesting IP address
	 *
	 * @param   string  $resource  A resource or a comma-separated list of resource families you want to know the current rate limit disposition for.
	 *
	 * @return  array  The JSON response decoded
	 *
	 * @since   3.1.4
	 */
	public function getRateLimit($resource)
	{
		// Build the request path.
		$path = '/application/rate_limit_status.json';

		if (!is_null($resource))
		{
			return $this->sendRequest($path, 'GET',  array('resources' => $resource));
		}

		return $this->sendRequest($path);
	}

	/**
	 * Method to send the request.
	 *
	 * @param   string  $path     The path of the request to make
	 * @param   string  $method   The request method.
	 * @param   mixed   $data     Either an associative array or a string to be sent with the post request.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   3.1.4
	 * @throws  RuntimeException
	 */
	public function sendRequest($path, $method = 'GET', $data = array(), $headers = array())
	{
		// Get the access token.
		$token = $this->oauth->getToken();

		// Set parameters.
		$parameters['oauth_token'] = $token['key'];

		// Send the request.
		$response = $this->oauth->oauthRequest($this->fetchUrl($path), $method, $parameters, $data, $headers);

		if (strpos($path, 'update_with_media') !== false)
		{
			// Check Media Rate Limit.
			$response_headers = $response->headers;

			if ($response_headers['x-mediaratelimit-remaining'] == 0)
			{
				// The IP has exceeded the Twitter API media rate limit
				throw new RuntimeException('This server has exceed the Twitter API media rate limit for the given period.  The limit will reset in '
					. $response_headers['x-mediaratelimit-reset'] . 'seconds.'
				);
			}
		}

		if (strpos($response->body, 'redirected') !== false)
		{
			return $response->headers['Location'];
		}

		return json_decode($response->body);
	}

	/**
	 * Get an option from the JTwitterObject instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   3.1.4
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JTwitterObject instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JTwitterObject  This object for method chaining.
	 *
	 * @since   3.1.4
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
