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
 * Defines the GET operations on buckets
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3OperationsBucketsGet extends JAmazons3OperationsBuckets
{
	/**
	 * Creates the request for getting a bucket and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucket($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$authorization = $this->createAuthorization("GET", $url, $headers);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->get($url, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's acl and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketAcl($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?acl";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$authorization = $this->createAuthorization("GET", $url, $headers);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->get($url, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's cors configuration information set
	 * and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketCors($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?cors";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$authorization = $this->createAuthorization("GET", $url, $headers);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->get($url, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}
}
