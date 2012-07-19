<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 * @since       12.3
 */
abstract class JLinkedinObject
{
	/**
	* @const integer The error code in case of success.
	* @since 12.3
	*/
	const SUCCESS_CODE = 200;

	/**
	 * @var    JRegistry  Options for the Linkedin object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JLinkedinHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  12.3
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry      &$options  Linkedin options object.
	 * @param   JLinkedinHttp  $client    The HTTP client object.
	 *
	 * @since   12.3
	 */
	public function __construct(JRegistry &$options = null, JLinkedinHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JLinkedinHttp($this->options);
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
	 * @since   12.3
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
		$uri = new JUri($path);

		return (string) $uri;
	}

	/**
	 * Method to send the request.
	 *
	 * @param   string  $path        The path of the request to make
	 * @param   string  $method      The request method.
	 * @param   array   $parameters  The parameters passed in the URL.
	 * @param   mixed   $data        Either an associative array or a string to be sent with the post request.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  DomainException
	 */
	public function sendRequest($path, $method='get', $parameters = null, $data='')
	{
		// Send the request.
		switch ($method)
		{
			case 'get':
				$response = $this->client->get($this->fetchUrl($path, $parameters));
				break;
			case 'post':
				$response = $this->client->post($this->fetchUrl($path, $parameters), $data);
				break;
		}

		if (strpos($response->body, 'redirected') !== false)
		{
			return $response->headers['Location'];
		}

		// Validate the response code.
		if ($response->code != self::SUCCESS_CODE)
		{
			$error = json_decode($response->body);

			if (property_exists($error, 'error'))
			{
				throw new DomainException($error->error);
			}
			else
			{
				$error = $error->errors;
				throw new DomainException($error[0]->message, $error[0]->code);
			}
		}

		return json_decode($response->body);
	}

	/**
	 * Method to convert boolean to string.
	 *
	 * @param   boolean  $bool  The boolean value to convert.
	 *
	 * @return  string  String with the converted boolean.
	 *
	 * @since 12.3
	 */
	public function boolean_to_string($bool)
	{
		if ($bool)
		{
			return 'true';
		}
		else
		{
			return 'false';
		}
	}

	/**
	 * Get an option from the JLinkedinObject instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   12.3
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JLinkedinObject instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JLinkedinObject  This object for method chaining.
	 *
	 * @since   12.3
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);

		return $this;
	}
}
