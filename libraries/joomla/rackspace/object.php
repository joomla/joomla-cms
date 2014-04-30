<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Rackspace API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
abstract class JRackspaceObject
{
	/**
	 * @var    JRegistry  Options for the Rackspace object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JRackspaceHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry       $options  Rackspace options object.
	 * @param   JRackspaceHttp  $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JRackspaceHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JRackspaceHttp($this->options);
	}

	/**
	 * Process the response and decode it.
	 *
	 * @param   JHttpResponse  $response      The response.
	 * @param   integer        $expectedCode  The expected "good" code.
	 *
	 * @throws DomainException
	 *
	 * @return mixed  The decoded response body
	 */
	public function processResponse(JHttpResponse $response, $expectedCode = 200)
	{
		// Validate the response code.
		if ($response->code != $expectedCode)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return json_decode($response->body);
	}

	/**
	 * Send an authentication request.
	 *
	 * @return string  The response headers
	 */
	public function getAuthTokenHeaders()
	{
		$host = $this->options->get("auth.host." . $this->options->get("api.location"));
		$url = "https://" . $host . "/v1.0";

		// Create the headers
		$headers = array(
			"Host" => $host,
			"X-Auth-User" => $this->options->get('api.authUser'),
			"X-Auth-Key" => $this->options->get('api.authKey'),
		);

		// Send the http request
		$response = $this->client->get($url, $headers);

		if ($response->code == 204)
		{
			return $response->headers;
		}

		return $this->displayResponseCodeAndHeaders($response);
	}

	/**
	 * Allows customization for displaying the return code and headers of a response
	 *
	 * @param   JHttpResponse  $response  The response object
	 *
	 * @return string A string containing the response code and headers
	 */
	public function displayResponseCodeAndHeaders($response)
	{
		// Convert the respnse headers to a string
		$headersArrayAsString = str_replace(
			"\",\"", "\",\n\t\"",
			str_replace(
				array("{","}",":"),
				array("Array(\n\t","\n)"," => "),
				json_encode($response->headers)
			)
		);

		return "Response code: " . $response->code . ".\n"
			. "Response headers: " . $headersArrayAsString . "\n";
	}
}
