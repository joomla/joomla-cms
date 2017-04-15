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
	 * Creates a new object (via upload, copy, or compose), or applies object ACLs.
	 *
	 * @param   string  $bucket           The bucket name
	 * @param   string  $object           The object name
	 * @param   string  $content          The content of the object
	 * @param   string  $optionalHeaders  An array of optional headers to be set
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putObject($bucket, $object, $content = "", $optionalHeaders = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/" . $object;

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

	/**
	 * This implementation of the PUT operation creates a copy of an object
	 * that is already stored in Google Cloud Storage.
	 *
	 * @param   string  $bucket           The bucket name
	 * @param   string  $object           The object name
	 * @param   string  $copySource       The path to the file to be copied (bucket + object)
	 * @param   string  $optionalHeaders  An array of optional headers to be set
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putObjectCopy($bucket, $object, $copySource, $optionalHeaders = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/" . $object;

		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
			"Date" => date("D, d M Y H:i:s O"),
			"x-goog-api-version" => 2,
			"x-goog-project-id" => $this->options->get("project.id"),
			"Content-Length" => 0,
		);

		// Check for request headers
		if (is_array($optionalHeaders))
		{
			foreach ($optionalHeaders as $key => $value)
			{
				$headers[$key] = $value;
			}
		}

		$headers["x-goog-copy-source"] = $copySource;
		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.full-control")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->put($url, "", $headers);

		// Process the response
		return $this->processResponse($response);
	}

	/**
	 *  Creates a request for setting the ACL for an object
	 *
	 * @param   string  $bucket           The bucket name
	 * @param   string  $object           The object name
	 * @param   string  $acl              An array with the acl permissions
	 * @param   string  $parameters       An array of optional parameters that can be set
	 *                                    to filter the results. These should only be one of:
	 *                                    generation or metageneration
	 * @param   string  $optionalHeaders  An array of optional headers to be set (such as
	 *                                    x-goog-if-generation or x-goog-if-metageneration)
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putObjectAcl($bucket, $object, $acl, $parameters = null, $optionalHeaders = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/" . $object . "?acl";
		$content = $this->createAclXml($acl);
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
			"Content-Type" => "application/x-www-form-urlencoded; charset=utf-8",
			"Content-Length" => strlen($content),
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
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		return $this->processResponse($response);
	}
}
