<?php

/**
 * @package     Joomla.Platform
 * @subpackage  Facebook
 */


defined('JPATH_PLATFORM') or die();


/**
 * Facebook API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Facebook
 */

abstract class JFacebookObject
{
	/**
	 * @var    JRegistry  Options for the Facebook object.
	 */
	protected $options;

	/**
	 * @var    JFacebookHttp  The HTTP client object to use in sending HTTP requests.
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry      $options  Facebook options object.
	 * 
	 * @param   JFacebookHttp  $client   The HTTP client object.
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
	 * @return  string   The request URL.
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
	 * @param   string  $path  The path of the request to make.
	 *
	 * @return   mixed  The response formatted based on specified format
	 * 
	 * @throws   DomainException
	 */
	public function sendRequest($path)
	{
		// Send the request.
		$response = $this->client->get($this->fetchUrl($path));

		// Validate the response.
		$response = json_decode($response->body, true);

		if (array_key_exists('error', $response))
		{
			throw new DomainException($response['error']['message']);
		}

		return $response;
	}
}
