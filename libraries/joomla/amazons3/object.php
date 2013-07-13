<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Amazons3 API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
abstract class JAmazons3Object
{
	/**
	 * @var    JRegistry  Options for the Amazons3 object.
	 * @since  ??.?
	 */
	protected $options;

	/**
	 * @var    JAmazons3Http  The HTTP client object to use in sending HTTP requests.
	 * @since  ??.?
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry      $options  Amazons3 options object.
	 * @param   JAmazons3Http  $client   The HTTP client object.
	 *
	 * @since   ??.?
	 */
	public function __construct(JRegistry $options = null, JAmazons3Http $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client = isset($client) ? $client : new JAmazons3Http($this->options);
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
	protected function processResponse(JHttpResponse $response, $expectedCode = 200)
	{
		// Validate the response code.
		if ($response->code != $expectedCode)
		{
			// Decode the error response and throw an exception.
			$error = new SimpleXMLElement($response->body);
			throw new DomainException($error->message, $response->code);
		}

		return new SimpleXMLElement($response->body);
	}

	/**
	 * Creates the Authorization request header (which handles authentication).
	 *
	 * @param string $httpVerb  The HTTP Verb (GET, PUT, etc)
	 * @param string $url       The target url of the request
	 * @param string $headers   The headers of the request
	 *
	 * @return string The Authorization request header
	 *
	 * @since   ??.?
	 */
	protected function createAuthorization($httpVerb, $url, $headers) {
		$authorization = "AWS " . $this->options->get('api.accessKeyId') . ":";

		$contentMD5 = "";
		if (array_key_exists("Content-MD5", $headers)) {
			$contentMD5 = $headers["Content-MD5"];
		}
		$contentType = "";
		if (array_key_exists("Content-type", $headers)) {
			$contentType = $headers["Content-type"];
		}
		$date = "";
		if (array_key_exists("Date", $headers)) {
			$date = $headers["Date"];
		}

		$stringToSign = $httpVerb . "\n"
			. $contentMD5 . "\n"
			. $contentType . "\n"
			. $date . "\n"
			. $this->createCanonicalizedAmzHeaders($headers)
			. $this->createCanonicalizedResource($url, $headers);

		// Signature = Base64( HMAC-SHA1( YourSecretAccessKeyID, UTF-8-Encoding-Of( StringToSign ) ) );
		$signature = base64_encode(
			hash_hmac("sha1", utf8_encode($stringToSign), $this->options->get('api.secretAccessKey'), true)
		);

		$authorization .= $signature;
		return $authorization;
	}

	/**
	 * Creates the canonicalized amz headers used for calculating the signature
	 * in the Authorization header.
	 *
	 * @param string $headers  The headers of the request
	 *
	 * @return string The canonicalized amz headers
	 *
	 * @since   ??.?
	 */
	private function createCanonicalizedAmzHeaders($headers) {
		$xAmzHeaders = array();
		foreach (array_keys($headers) as $header_key) {
			// Convert each HTTP header name to lowercase. For example, 'X-Amz-Date' becomes 'x-amz-date'.
			$lowercaseHeader = strtolower($header_key);

			// Select all HTTP request headers that start with 'x-amz-' (using a case-insensitive comparison)
			if (strpos($lowercaseHeader, 'x-amz-') == 0) {
				// Combine header fields with the same name into one "header-name:comma-separated-value-list"
				//  pair as prescribed by RFC 2616, section 4.2, without any whitespace between values.
				// For example, the two metadata headers 'x-amz-meta-username: fred' and
				//  'x-amz-meta-username: barney' would be combined into the single header
				//  'x-amz-meta-username: fred,barney'.
				if (is_array($headers[$header_key])) {
					$commaSeparatedValues = implode(',', $headers[$header_key]);
					$values = rtrim($commaSeparatedValues, ',');
					$xAmzHeaders[$lowercaseHeader] = $values;
				} else {
					$xAmzHeaders[$lowercaseHeader] = $headers[$header_key];
				}
			}
		}

		// Sort the collection of headers lexicographically by header name.
		ksort($xAmzHeaders);
		return $xAmzHeaders;
	}


	/**
	 * Creates the canonicalized resource used for calculating the signature
	 * in the Authorization header.
	 *
	 * @param string $url      The target url of the request
	 * @param string $headers  The headers of the request
	 *
	 * @return string The canonicalized resource
	 *
	 * @since   ??.?
	 */
	private function createCanonicalizedResource($url, $headers) {
		// TODO extract the following from $request
		/**
			* @param string $httpRequestURI  The request URI
			* @param string $bucket		     The bucket name contained in the request
			* @param string $subresources	 In case of multiple subresources, they must be lexicographically sorted
			*                                 by subresource name and separated by '&', e.g., ?acl&versionId=value
			*                                 Accepted subresources:  acl, lifecycle, location, logging, notification,
			*                                 partNumber,policy, requestPayment, torrent, uploadId, uploads, versionId,
			*                                 versioning, versions, and website.
			* @param string $queryParameters  The query string parameters in a GET request include response-content-type,
			*                                  response-content-language, response-expires, response-cache-control,
			*                                  response-content-disposition, and response-content-encoding.
			*                                  The delete query string parameter must be included when you create the
			*                                  CanonicalizedResource for a multi-object Delete request.
		*/
		// TODO new function
		// Constructing the CanonicalizedResource Element
		/*
			CanonicalizedResource = [ "/" + Bucket ] +
			<HTTP-Request-URI, from the protocol name up to the query string> +
			[ subresource, if present. For example "?acl", "?location", "?logging", or "?torrent"];
		 */
		$httpRequestURI = "";
		$bucket = "";
		$subresources = "";
		$queryParameters = "";
		if ($bucket == NULL) {
			// For a request that does not address a bucket, such as GET Service, append "/".
			$canonicalizedResource = "/";
		} else {
			// For a virtual hosted-style request "https://johnsmith.s3.amazonaws.com/photos/puppy.jpg",
			// the CanonicalizedResource is "/johnsmith/photos/puppy.jpg".
			$canonicalizedResource = "/" . $bucket;

			// TODO check
			$canonicalizedResource .= substr(
				$httpRequestURI,
				strpos ($httpRequestURI, $this->options->get('api.url'))
				+ strlen($this->options->get('api.url'))
			);

			// Append existing subresources
			if ($subresources) {
				$canonicalizedResource .= $subresources;
			}

			// If the request specifies query string parameters overriding the response header values,
			// append the query string parameters and their values.
			if ($queryParameters != NULL) {
				$canonicalizedResource .= $queryParameters;
			}
		}

		return $canonicalizedResource;
	}
}
