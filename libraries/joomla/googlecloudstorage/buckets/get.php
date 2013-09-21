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
 * Defines the GET operation on buckets
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorageBucketsGet extends JGooglecloudstorageBuckets
{
	/**
	 * Lists the objects that are in a bucket
	 *
	 * @param   string  $bucket      The bucket name
	 * @param   string  $parameters  An array of optional parameters that can be set
	 *                               to filter the results
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucket($bucket, $parameters = null)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/";
		$paramContent = "";

		if ($parameters != null)
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

		return $this->commonGetOperations($url, $headers);
	}

	/**
	 * Creates the request for getting a bucket's acl and returns the response
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketAcl($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?acl";

		// The headers may be optionally set in advance
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
		);

		return $this->commonGetOperations($url, $headers);
	}

	/**
	 * Creates the request for getting a bucket's cors configuration information set
	 * and returns the response
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketCors($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?cors";

		// The headers may be optionally set in advance
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
		);

		return $this->commonGetOperations($url, $headers);
	}

	/**
	 * Creates the request for getting a bucket's lifecycle and returns the response
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketLifecycle($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?lifecycle";

		// The headers may be optionally set in advance
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
		);

		return $this->commonGetOperations($url, $headers);
	}

	/**
	 * Creates the request for getting a bucket's logging and returns the response
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketLogging($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?logging";

		// The headers may be optionally set in advance
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
		);

		return $this->commonGetOperations($url, $headers);
	}

	/**
	 * Creates the request for getting a bucket's versioning state and returns the response
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketVersioning($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?versioning";

		// The headers may be optionally set in advance
		$headers = array(
			"Host" => $bucket . "." . $this->options->get("api.url"),
		);

		return $this->commonGetOperations($url, $headers);
	}
}
