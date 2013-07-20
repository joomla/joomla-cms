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
 * Defines the HEAD operations on buckets
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3OperationsBucketsHead extends JAmazons3OperationsBuckets
{
	/**
	 * Creates a request to determine if a bucket exists and you have permission to access it.
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function headBucket($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/";

		// Create the headers
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);
		$authorization = $this->createAuthorization("HEAD", $url, $headers);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->head($url, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}
}
