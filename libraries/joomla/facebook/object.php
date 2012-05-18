<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */


defined('JPATH_PLATFORM') or die();


/**
 * Facebook API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 * 
 * @since       12.1
 */
abstract class JFacebookObject
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 * @since  12.1
	 */
	protected $options;

	/**
	 * @var    JFacebookHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  12.1
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry      $options  Facebook options object.
	 * @param   JFacebookHttp  $client   The HTTP client object.
	 * 
	 * @since   12.1
	 */
	public function __construct(JRegistry $options = null, JFacebookHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JFacebookHttp($this->options);
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

		return (string) $uri;
	}

	/**
	 * Method to send the request.
	 *
	 * @param   string  $path     The path of the request to make.
	 * @param   string  $method   The request method.
	 * @param   mixed   $data     Either an associative array or a string to be sent with the post request.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request
	 *
	 * @return   mixed  The request response.
	 * 
	 * @since    12.1
	 * @throws   DomainException
	 */
	public function sendRequest($path, $method='get', $data='', array $headers = null)
	{
		// Send the request.
		switch ($method)
		{
			case 'get':
				$response = $this->client->get($this->fetchUrl($path));
				break;
			case 'post':
				$response = $this->client->post($this->fetchUrl($path), $data, $headers);
				break;
			case 'delete':
				$response = $this->client->delete($this->fetchUrl($path));
				break;
		}

		// Validate the response.
		$response = json_decode($response->body);

		if (!is_object($response))
		{
			return $response;
		}

		if (property_exists($response, 'error'))
		{
			throw new DomainException($response->error->message);
		}

		return $response;
	}
}
