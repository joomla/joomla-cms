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
 * Defines the PUT operation on buckets
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorageBucketsPut extends JGooglecloudstorageBuckets
{
	/**
	 * Creates the request for creating a bucket and returns the response
	 *
	 * @param   string  $bucket          The bucket name
	 * @param   string  $bucketLocation  The bucket region (default: US Standard)
	 * @param   string  $predefinedAcl   The predefined ACL
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function putBucket($bucket, $bucketLocation = null, $predefinedAcl = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/";
		$content = "";
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
			"Date" => date("D, d M Y H:i:s O"),
			"x-goog-api-version" => 2,
			"x-goog-project-id" => $this->options->get("project.id"),
		);

		if ($predefinedAcl != null)
		{
			$headers["x-goog-acl"] = $predefinedAcl;
		}

		if ($bucketLocation != null)
		{
			$content = "<CreateBucketConfiguration>\n"
				. "<LocationConstraint>" . $bucketLocation . "</LocationConstraint>\n"
				. "</CreateBucketConfiguration>";

			$headers["Content-Type"] = "application/x-www-form-urlencoded; charset=utf-8";
		}

		$headers["Content-Length"] = strlen($content);
		$authorization = $this->getAuthorization(
			$this->options->get("api.oauth.scope.read-write")
		);
		$headers["Authorization"] = $authorization;

		// Send the http request
		$response = $this->client->put($url, $content, $headers);

		// Process the response
		$response_body = $this->processResponse($response);

		return $response_body;
	}
}
