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
 * Defines the GET operation on objects
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorageObjectsGet extends JGooglecloudstorageObjects
{
	/**
	 * Creates the get request and returns the response
	 *
	 * @param   string  $bucket           The bucket name
	 * @param   string  $object           The object name
	 * @param   string  $parameters       An array of optional parameters that can be set
	 *                                    to filter the results
	 * @param   string  $optionalHeaders  An array of optional headers to be set
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getObject($bucket, $object, $parameters = null, $optionalHeaders = null)
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

		// The headers may be optionally set in advance
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
		);

		if (is_array($optionalHeaders))
		{
			foreach ($optionalHeaders as $header => $value)
			{
				$headers[$header] = $value;
			}
		}

		return $this->commonGetOperations($url, $headers);
	}
}
