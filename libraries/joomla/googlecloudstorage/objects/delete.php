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
 * Defines the DELETE operation on buckets
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorageObjectsDelete extends JGooglecloudstorageObjects
{
	/**
	 * Deletes an object
	 *
	 * @param   string  $bucket             The bucket name
	 * @param   string  $object             The object to be deleted
	 * @param   string  $generation         A query string parameter for a specific object generation
	 * @param   string  $ifGenerationMatch  x-goog-if-generation-match request header
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function deleteObject($bucket, $object, $generation = null, $ifGenerationMatch = null)
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

		if ($generation != null)
		{
			$headers["x-goog-if-generation-match"] = $ifGenerationMatch;
		}

		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.full-control")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->delete($url, $headers);

		// Process the response
		return $this->processResponse($response);
	}
}
