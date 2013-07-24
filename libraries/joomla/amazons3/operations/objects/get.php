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
 * Defines the GET operations on objects
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3OperationsObjectsGet extends JAmazons3OperationsObjects
{
	/**
	 * Creates the request for getting a bucket and returns the response from Amazon
	 *
	 * @param   string  $bucket      The bucket name
	 * @param   string  $objectName  The object name
	 * @param   string  $versionId   The version id
	 * @param   string  $range       The range of bytes to be returned
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getObject($bucket, $objectName, $versionId = null, $range = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/" . $objectName;

		if (! is_null($versionId))
		{
			$url .= "?versionId=" . $versionId;
		}

		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);

		if (! is_null($range))
		{
			$headers['Range'] = "bytes=" . $range;
		}

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url, $headers);

		return $response_body;
	}
}
