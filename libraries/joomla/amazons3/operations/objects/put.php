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
	public function putObject($bucket, $object, $content = "", $requestHeaders = null)
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

	/**
	 * Creates the request for setting the permissions on an existing bucket
	 * using access control lists (ACL)
	 *
	 * @param   string  $bucket  The bucket name
	 * @param   string  $object  The object name
	 * @param   string  $acl     An array containing the ACL permissions
	 *                           (either canned or explicitly specified)
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putObjectAcl($bucket, $object, $acl = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/"
			. $object . "?acl";
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);

		// Check for ACL permissions
		if (is_array($acl))
		{
			// Check for canned ACL permission
			if (array_key_exists("acl", $acl))
			{
				$headers["x-amz-acl"] = $acl["acl"];
			}
			else
			{
				// Access permissions were specified explicitly
				foreach ($acl as $aclPermission => $aclGrantee)
				{
					$headers["x-amz-grant-" . $aclPermission] = $aclGrantee;
				}
			}
		}

		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->put($url, "", $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}

	/**
	 * This implementation of the PUT operation creates a copy of an object
	 * that is already stored in Amazon S3.
	 *
	 * @param   string  $bucket          The name of the bucket to copy in
	 * @param   string  $object          The name of the new file
	 * @param   string  $copySource      The path to the file to be copied (bucket + object)
	 * @param   string  $requestHeaders  An array containing request headers
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putObjectCopy($bucket, $object, $copySource, $requestHeaders = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/" . $object;
		$headers = array(
			"Date" => date("D, d M Y H:i:s O"),
		);

		// Check for request headers
		if (is_array($requestHeaders))
		{
			foreach ($requestHeaders as $aclPermission => $aclGrantee)
			{
				$headers[$aclPermission] = $aclGrantee;
			}
		}

		$headers["x-amz-copy-source"] = $copySource;
		$authorization = $this->createAuthorization("PUT", $url, $headers);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->put($url, "", $headers);

		if (! is_null($response))
		{
			// Process the response
			return $this->processResponse($response);
		}

		return null;
	}
}
