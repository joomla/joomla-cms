<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Googlecloudstorage API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
abstract class JGooglecloudstorageObject
{
	/**
	 * @var    JRegistry  Options for the Googlecloudstorage object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JGooglecloudstorageHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry                $options  Googlecloudstorage options object.
	 * @param   JGooglecloudstorageHttp  $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JGooglecloudstorageHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JGooglecloudstorageHttp($this->options);
	}

	/**
	 * Process the response and decode it.
	 *
	 * @param   JHttpResponse  $response      The response.
	 * @param   integer        $expectedCode  The expected "good" code.
	 *
	 * @throws DomainException
	 *
	 * @return mixed
	 */
	public function processResponse(JHttpResponse $response, $expectedCode = 200)
	{
		// Validate the response code.
		if ($response->code != $expectedCode)
		{
			if ($response->body != null && $response->body[0] == '<')
			{
				// Decode the error response and throw an exception.
				$error = new SimpleXMLElement($response->body);

				// The PUT requests return <Message>[...]</Message> in their bodies
				if ($error->message == "")
				{
					$error->message = $error->Message;
				}

				throw new DomainException($error->message, $response->code);
			}
		}

		if ($response->body != null && $response->body[0] == '<')
		{
			return new SimpleXMLElement($response->body);
		}
		else
		{
			return "Response code: " . $response->code . ".\n";
		}
	}

	/**
	 * Creates the Authorization request header (which handles authentication).
	 * Service accounts are used for Google Cloud Storage and the mechanics require
	 * applications to create and cryptographically sign JSON Web Tokens (JWTs).
	 * A JWT is composed of three parts: a header, a claim set, and a signature.
	 *
	 * @param   string  $scope  A space-delimited list of the permissions the
	 *                          application requests
	 * @param   string  $prn    The email address of the user for which the
	 *                          application is requesting delegated access.
	 *
	 * @return string The Authorization request header
	 *
	 * @since   ??.?
	 */
	public function createAuthorization($scope, $prn = null)
	{
		// Standard header for Service Accounts
		$header = base64_encode(
			utf8_encode(
				json_encode(
					array(
						"alg" => "RS256",
						"typ" => "JWT"
					),
					JSON_UNESCAPED_SLASHES
				)
			)
		);

		// The JWT claim set contains information about the JWT
		$claimSetValues = array(
			"iss" => $this->options->get("client.email"),
			"scope" => $scope,
			"aud" => $this->options->get("api.oauth.assertionTarget"),
			"exp" => time() + 3600,
			"iat" => time()
		);

		if ($prn != null)
		{
			$claimSetValues["prn"] = $prn;
		}

		$claimSet = base64_encode(utf8_encode(json_encode($claimSetValues, JSON_UNESCAPED_SLASHES)));

		// Get the key needed to compute the signature
		$key = file_get_contents($this->options->get("client.keyFile"));

		// Sign the UTF-8 representation of the input using SHA256withRSA
		$signature = base64_encode(hash_hmac("sha256", utf8_encode($header . "." . $claimSet), $key, false));
		$requestBody = "grant-type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion="
			. $header . "." . $claimSet . "." . $signature;

		// Send the access token request
		$response = $this->client->post(
			$this->options->get('api.oauth.assertionTarget'),
			$requestBody,
			array(
				"Host" => $this->options->get("api.host"),
				"Content-Type" => "application/x-www-form-urlencoded",
			)
		);

		// TODO get authorization from response

		return $response;
	}
}
