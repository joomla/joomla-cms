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
 * Defines the PUT operation on objects
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorageObjectsPut extends JGooglecloudstorageObjects
{
	/**
	 * Creates the request for creating a bucket and returns the response
	 *
	 * @param   string  $bucket           The bucket name
	 * @param   string  $object           The object name
	 * @param   string  $content          The content of the object
	 * @param   string  $parameters       An array of optional parameters that can be set
	 *                                    to filter the results. These should only be one of:
	 *                                    Expires, GoogleAccessId or Signature
	 * @param   string  $optionalHeaders  An array of optional headers to be set
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putObject($bucket, $object, $content = "", $parameters = null, $optionalHeaders = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/" . $object;
		$paramContent = "";

		if (is_array($parameters))
		{
			foreach ($parameters as $param => $paramValue)
			{
				$paramContent .= "&" . $param . "=" . $paramValue;
			}

			$paramContent[0] = "?";
			$url .= $paramContent;
		}

		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
			"Date" => date("D, d M Y H:i:s O"),
			"x-goog-api-version" => 2,
			"x-goog-project-id" => $this->options->get("project.id"),
		);

		if (is_array($optionalHeaders))
		{
			foreach ($optionalHeaders as $header => $value)
			{
				$headers[$header] = $value;
			}
		}

		$headers["Content-Length"] = strlen($content);
		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.full-control")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		return $this->processResponse($response);
	}
}
