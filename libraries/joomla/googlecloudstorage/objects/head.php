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
 * Defines the HEAD operation on objects
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorageObjectsHead extends JGooglecloudstorageObjects
{
	/**
	 * Creates the head request and returns the response
	 *
	 * @param   string  $bucket           The bucket name
	 * @param   string  $object           The object name
	 * @param   string  $generation       Parameter which can be used if you want
	 *                                    to fetch a specific object generation.
	 * @param   string  $optionalHeaders  An array of optional headers to be set
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function headObject($bucket, $object, $generation = null, $optionalHeaders = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/" . $object;

		if ($generation != null)
		{
			$url .= "?generation=" . $generation;
		}

		// The headers may be optionally set in advance
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
			"Date" => date("D, d M Y H:i:s O"),
			"x-goog-api-version" => 2,
			"Content-Length" => 0,
		);

		if (is_array($optionalHeaders))
		{
			foreach ($optionalHeaders as $header => $value)
			{
				$headers[$header] = $value;
			}
		}

		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.full-control")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->head($url, $headers);

		// Process the response
		return $this->processResponse($response);
	}
}
