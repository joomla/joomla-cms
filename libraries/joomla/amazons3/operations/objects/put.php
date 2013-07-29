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
 * Defines the PUT operations on objects
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3OperationsObjectsPut extends JAmazons3OperationsObjects
{
	/**
	 * Adds an object to a bucket
	 *
	 * @param   string  $bucket          The bucket name
	 * @param   string  $object          The object to be added
	 * @param   string  $content         The content of the object
	 * @param   array   $requestHeaders  An array of request headers
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putObject($bucket, $object, $content = null, $requestHeaders = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/" . $object;
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);

		if (! is_null($requestHeaders))
		{
			foreach ($requestHeaders as $key => $value)
			{
				$headers[$key] = $value;
			}
		}

		// Set the content related headers
		if (! is_null($requestHeaders))
		{
			$headers["Content-type"] = "application/x-www-form-urlencoded; charset=utf-8";
		}

		$headers["Content-Length"] = strlen($content);
		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;
		unset($headers["Content-type"]);

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		if (! is_null($response))
		{
			// Process the response
			return $this->processResponse($response);
		}

		return null;
	}
}
