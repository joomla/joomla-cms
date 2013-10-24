<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Dropbox
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Dropbox API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Dropbox
 * @since       ??.?
 */
abstract class JDropboxObject
{
	/**
	 * @var    JRegistry  Options for the Dropbox object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry  $options  Dropbox options object.
	 * @param   JHttp      $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JHttp($this->options);
	}

	/**
	 * Performs common actions for GET operations
	 *
	 * @param   string   $url           The URI used in the request
	 * @param   boolean  $noDecodeBody  Tells the method not to use json_decode
	 *                                  when returning the response body.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	public function commonGetOperations($url, $noDecodeBody = false)
	{
		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->get($url, $headers);

		// Process the response
		return $this->processResponse($response, $noDecodeBody);
	}

	/**
	 * Performs common actions for POST operations
	 *
	 * @param   string  $url   The URI used in the request
	 * @param   string  $data  The data used in the request
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	public function commonPostOperations($url, $data)
	{
		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->post($url, $data, $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Performs common actions for PUT operations
	 *
	 * @param   string  $url   The URI used in the request
	 * @param   string  $data  The data used in the request
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	public function commonPutOperations($url, $data)
	{
		// Creates an array with the default Host and Authorization headers
		$headers = $this->getDefaultHeaders();

		// Send the http request
		$response = $this->client->put($url, $data, $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Creates a string containing the given parameters which will be appended
	 * to an URI in order to send API requests.
	 *
	 * @param   array  $params  An array containing the request parameters
	 *
	 * @return string  The string containing the concatenated parameters
	 */
	public function createParamsString($params = array())
	{
		$paramsString = "";

		foreach ($params as $key => $param)
		{
			$paramsString .= "&" . $key . "=" . $param;
		}

		if (! empty($params))
		{
			$paramsString[0] = "?";
		}

		return $paramsString;
	}

	/**
	 * Process the response and decode it.
	 *
	 * @param   JHttpResponse  $response      The response.
	 * @param   boolean        $noDecodeBody  Tells the method not to use json_decode
	 *                                        when returning the response body.
	 * @param   integer        $expectedCode  The expected "good" code.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	protected function processResponse(
		JHttpResponse $response, $noDecodeBody = false, $expectedCode = 200)
	{
		// Validate the response code.
		if ($response->code != $expectedCode)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->error, $response->code);
		}

		if ($noDecodeBody)
		{
			return $response->body;
		}

		return json_decode($response->body);
	}

	/**
	 * Gets the authorization URI which can be used by the user to authenticate
	 * and allow permission for the app
	 *
	 * @return string  The URI
	 */
	public function getAuthorizationUri()
	{
		$url = $this->options->get("api.oauth2.authorize");
		$url .= "?response_type=code";
		$url .= "&client_id=" . $this->options->get("app.key");
		$state = $this->options->get("app.state");

		if (isset($state))
		{
			$url .= "&state=" . $this->options->get("app.state");
		}

		return $url;
	}

	/**
	 * Gets the oauth2 access token using the authorization code provided by the user
	 * and saves it in the 'app.access_token' option
	 *
	 * @return boolean  Returns true if it doesn't throw a DomainException
	 *
	 * @throws DomainException
	 */
	public function getOauth2Token()
	{
		$url = $this->options->get("api.oauth2.access_token");
		$url .= "?grant_type=authorization_code";
		$url .= "&code=" . $this->options->get("app.authorization_code");
		$url .= "&client_id=" . $this->options->get("app.key");
		$url .= "&client_secret=" . $this->options->get("app.secret");
		$data = "";

		// Return the result of the http POST request
		$response = $this->client->post($url, $data);
		$responseBody = $this->processResponse($response);

		if (isset($responseBody->access_token))
		{
			$this->options->set("app.access_token", $responseBody->access_token);
			$this->options->set("app.uid", $responseBody->uid);
		}

		return true;
	}

	/**
	 * Creates an array with the default Host and Authorization headers
	 *
	 * @return  array  The headers array
	 */
	public function getDefaultHeaders()
	{
		return array(
			"Host" => $this->options->get("api.url"),
			"Authorization" => "Bearer " . $this->options->get("app.access_token"),
		);
	}

	/**
	 * Gets the oauth1 token and token secret
	 *
	 * @param   string  &$oauth_token_secret  The variable in which the token secret will be stored
	 * @param   string  &$oauth_token         The variable in which the token will be stored
	 *
	 * @return boolean  Returns true if it doesn't throw a DomainException
	 *
	 * @throws DomainException
	 */
	protected function getOauth1TokenAndSecret(&$oauth_token_secret, &$oauth_token)
	{
		$url = $this->options->get("api.oauth1.request_token");
		$data = "";

		// Create the authorization header
		$headers = array(
			"Host" => $this->options->get("api.url"),
			"Authorization" => "OAuth oauth_version=\"2.0\""
				. ", oauth_signature_method=\"PLAINTEXT\""
				. ", oauth_consumer_key=\"" . $this->options->get("app.key") . "\""
				. ", oauth_signature=\"" . $this->options->get("app.secret") . "&\"",
		);

		// Return the result of the http POST request
		$response = $this->client->post($url, $data, $headers);

		// Validate the response code.
		if ($response->code != 200)
		{
			// Decode the error response and throw an exception.
			$error = json_decode($response->body);
			throw new DomainException($error->error, $response->code);
		}

		// Get the token and token secret
		$requestToken = $response->body;
		$pattern = "/oauth_token_secret=(.*)&oauth_token=(.*)/s";
		preg_match($pattern, $requestToken, $matches);
		$oauth_token_secret = $matches[1];
		$oauth_token = $matches[2];

		return true;
	}
}
