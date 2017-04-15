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
 * Defines the DELETE operations on objects
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3OperationsObjectsDelete extends JAmazons3OperationsObjects
{
	/**
	 * Deletes an object from a bucket
	 *
	 * @param   string  $bucket     The bucket name
	 * @param   string  $object     The name of the object to be deleted
	 * @param   string  $versionId  The version id of the object to be deleted
	 * @param   string  $serialNr   The serial number is generated using either a hardware or
	 *                              a virtual MFA device. Required for MfaDelete
	 * @param   string  $tokenCode  Also required for MfaDelete
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function deleteObject($bucket, $object, $versionId = null, $serialNr = null, $tokenCode = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/" . $object;

		if (! is_null($versionId))
		{
			$url .= "?versionId=" . $versionId;
		}

		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
			"Content-Length" => "0",
		);

		if (! is_null($serialNr))
		{
			$headers["x-amz-mfa"] = $serialNr . " " . $tokenCode;
		}

		// Send the request and process the response
		$response_body = $this->commonDeleteOperations($url, $headers);

		return $response_body;
	}

	/**
	 * This operation aborts a multipart upload
	 *
	 * @param   string  $bucket    The bucket name
	 * @param   string  $object    The name of the object
	 * @param   string  $uploadId  The upload id
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function abortMultipartUpload($bucket, $object, $uploadId)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/"
			. $object . "?uploadId=" . $uploadId;

		// Send the request and process the response
		$response_body = $this->commonDeleteOperations($url);

		return $response_body;
	}
}
