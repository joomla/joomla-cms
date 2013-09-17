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
	 * Creates the XML which will be sent in a put request with the acl query parameter
	 *
	 * @param   string  $acl  An array containing the ACL permissions
	 *
	 * @return string The XML
	 */
	public function createAclXml($acl)
	{
		$content = "<AccessControlList>\n";

		foreach ($acl as $aclKey => $aclValue)
		{
			if (strcmp($aclKey, "Owner") === 0)
			{
				$content .= "<Owner>\n<ID>" . $aclValue . "</ID>\n</Owner>\n";
			}
			else
			{
				$content .= "<Entries>\n";

				foreach ($aclValue as $entry)
				{
					$content .= "<Entry>\n";

					foreach ($entry as $entryKey => $entryValue)
					{
						if (is_array($entryValue))
						{
							$content .= "<Scope type=\"" . $entryValue["type"] . "\">\n";

							foreach ($entryValue as $scopeKey => $scopeValue)
							{
								if (strcmp($scopeKey, "type") !== 0)
								{
									$content .= "<" . $scopeKey . ">" . $scopeValue . "</" . $scopeKey . ">\n";
								}
							}

							$content .= "</Scope>\n";
						}
						else
						{
							// Permission
							$content .= "<" . $entryKey . ">" . $entryValue . "</" . $entryKey . ">\n";
						}
					}

					$content .= "</Entry>\n";
				}

				$content .= "</Entries>\n";
			}
		}

		$content .= "</AccessControlList>";

		return $content;
	}

	/**
	 * Creates the request for creating a bucket and returns the response
	 *
	 * @param   string  $bucket           The bucket name
	 * @param   string  $object           The object name
	 * @param   string  $acl              An array with the acl permissions
	 * @param   string  $parameters       An array of optional parameters that can be set
	 *                                    to filter the results. These should only be one of:
	 *                                    generation or metageneration
	 * @param   string  $optionalHeaders  An array of optional headers to be set (such as
	 *                                    x-goog-if-metageneration)
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
