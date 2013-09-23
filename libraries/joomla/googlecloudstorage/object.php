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
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry  $options  Googlecloudstorage options object.
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
	 * Common operations performed by all of the methods that send GET requests
	 *
	 * @param   string  $url      The url that is used in the request
	 * @param   string  $headers  An array of headers
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function commonGetOperations($url, $headers)
	{
		$headers["Date"] = date("D, d M Y H:i:s O");
		$headers["Content-Length"] = 0;
		$headers["x-goog-api-version"] = 2;
		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.full-control")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->get($url, $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 * Creates the XML which will be sent in a put request with the acl query parameter
	 *
	 * @param   string  $acl  An array containing the ACL permissions
	 *
	 * @return string The XML
	 */
	public function createAclXml($acl)
	{
		$content = "<AccessControlList>\n";

		foreach ($acl as $aclKey => $aclValue)
		{
			if (strcmp($aclKey, "Owner") === 0)
			{
				$content .= "<Owner>\n<ID>" . $aclValue . "</ID>\n</Owner>\n";
			}
			else
			{
				$content .= "<Entries>\n";

				foreach ($aclValue as $entry)
				{
					$content .= "<Entry>\n";

					foreach ($entry as $entryKey => $entryValue)
					{
						if (is_array($entryValue))
						{
							$content .= "<Scope type=\"" . $entryValue["type"] . "\">\n";

							foreach ($entryValue as $scopeKey => $scopeValue)
							{
								if (strcmp($scopeKey, "type") !== 0)
								{
									$content .= "<" . $scopeKey . ">" . $scopeValue . "</" . $scopeKey . ">\n";
								}
							}

							$content .= "</Scope>\n";
						}
						else
						{
							// Permission
							$content .= "<" . $entryKey . ">" . $entryValue . "</" . $entryKey . ">\n";
						}
					}

					$content .= "</Entry>\n";
				}

				$content .= "</Entries>\n";
			}
		}

		$content .= "</AccessControlList>";

		return $content;
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
			// Convert the respnse headers to a string
			$headersArrayAsString = str_replace(
				"\",\"", "\",\n\t\"",
				str_replace(
					array("{","}",":"),
					array("Array(\n\t","\n)","=>"),
					json_encode($response->headers)
				)
			);

			return "Response code: " . $response->code . ".\n"
				. "Response headers: " . $headersArrayAsString . "\n";
		}
	}

	/**
	 * Safe base 64 encode
	 *
	 * @param   mixed  $data  The input data
	 *
	 * @return mixed  The encoded data
	 */
	public function urlSafeB64Encode($data)
	{
		$b64 = base64_encode($data);

		return str_replace(
			array('+', '/', '\r', '\n', '='),
			array('-', '_'),
			$b64
		);
	}

	/**
	 * Gets the header part of the JSON Web Token
	 *
	 * @return string  The header
	 */
	public function getJwtHeader()
	{
		// Standard header for Service Accounts
		return $this->urlSafeB64Encode(
			utf8_encode(
				str_replace(
					"\\/",
					"/",
					json_encode(
						array(
							"alg" => "RS256",
							"typ" => "JWT"
						)
					)
				)
			)
		);
	}

	/**
	 * Gets the JWT claim set, which contains information about the JWT
	 *
	 * @param   string  $scope  A space-delimited list of the permissions the
	 *                          application requests
	 * @param   string  $prn    The email address of the user for which the
	 *                          application is requesting delegated access.
	 *
	 * @return string  The claim set
	 */
	public function getJwtClaimSet($scope, $prn)
	{
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

		// Create and return the claim set for the JWT
		return $this->urlSafeB64Encode(
			utf8_encode(
				str_replace(
					"\\/",
					"/",
					json_encode($claimSetValues)
				)
			)
		);
	}

	/**
	 * Reads the private key and creates the JSON Web signature
	 *
	 * @param   string  $header    The JWT claim header
	 * @param   string  $claimSet  The JWT claim set
	 *
	 * @return string  The signature for the JWT
	 */
	public function getJws($header, $claimSet)
	{
		// Get the key needed to compute the signature
		$p12 = file_get_contents($this->options->get("client.keyFile"));
		$password = $this->options->get('api.oauth.privateKeyPassword');
		openssl_pkcs12_read($p12, $certs, $password);
		$privateKey = openssl_pkey_get_private($certs["pkey"]);

		// Sign the UTF-8 representation of the input using SHA256withRSA
		$signatureInput = utf8_encode($header . "." . $claimSet);
		openssl_sign($signatureInput, $signature, $privateKey, "sha256");

		return $this->urlSafeB64Encode($signature);
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
		$header = $this->getJwtHeader();
		$claimSet = $this->getJwtClaimSet($scope, $prn);
		$signature = $this->getJws($header, $claimSet);

		// Create the request body
		$requestBody = "grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion="
			. $header . "." . $claimSet . "." . $signature;

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

		// Create and return the authorization header
		$responseBody = json_decode($response->body);

		return $responseBody->token_type . " " . $responseBody->access_token;
	}
}
