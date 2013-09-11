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
	 * Safe base 64 encode
	 *
	 * @param   mixed  $data  The input data
	 *
	 * @return mixed  The encoded data
	 */
	public  function urlSafeB64Encode($data)
	{
		$b64 = base64_encode($data);

		return str_replace(
			array('+', '/', '\r', '\n', '='),
			array('-', '_'),
			$b64
		);
	}

	/**
	 * Creates and sends a request for obtaining the access token required to
	 * create the Authorization header (which handles authentication).
	 * Service accounts are used for Google Cloud Storage and the mechanics require
	 * applications to create and cryptographically sign JSON Web Tokens (JWTs).
	 * A JWT is composed of three parts: a header, a claim set, and a signature.
	 *
	 * @param   string  $scope  A space-delimited list of the permissions the
	 *                          application requests
	 * @param   string  $prn    The email address of the user for which the
	 *                          application is requesting delegated access.
	 *
	 * @return string  The authorization request header
	 *
	 * @since   ??.?
	 */
	public function getAuthorization($scope, $prn = null)
	{
		// Standard header for Service Accounts
		$header = $this->urlSafeB64Encode(
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

		// Create the claim set for the JWT
		$claimSet = $this->urlSafeB64Encode(utf8_encode(json_encode($claimSetValues, JSON_UNESCAPED_SLASHES)));

		// Create the signature
		// Get the key needed to compute the signature
		$p12 = file_get_contents($this->options->get("client.keyFile"));
		$password = $this->options->get('api.oauth.privateKeyPassword');
		openssl_pkcs12_read($p12, $certs, $password);
		$privateKey = openssl_pkey_get_private($certs["pkey"]);

		// Sign the UTF-8 representation of the input using SHA256withRSA
		$signatureInput = utf8_encode($header . "." . $claimSet);
		openssl_sign($signatureInput, $signature, $privateKey, "sha256");
		$jws = $this->urlSafeB64Encode($signature);
		
		// Create the request body
		$requestBody = "grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion="
			. $header . "." . $claimSet . "." . $jws;

		// Send the access token request
		$response = $this->client->post(
			$this->options->get('api.oauth.assertionTarget'),
			$requestBody,
			array(
				"Host" => $this->options->get("api.host"),
				"Content-Type" => "application/x-www-form-urlencoded",
				"Content-Length" => strlen($requestBody),
			)
		);

		// Return the access token
		$responseBody = json_decode($response->body);

		return $responseBody->token_type . " " . $responseBody->access_token;
	}
}
