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
 * Defines the GET operations on buckets
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3OperationsBucketsGet extends JAmazons3OperationsBuckets
{
	/**
	 * Creates the request for getting a bucket and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucket($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/";

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's acl and returns the response from Amazon
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

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's cors configuration information set
	 * and returns the response from Amazon
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

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's lifecycle and returns the response from Amazon
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

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's policy and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketPolicy($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?policy";

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's location and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketLocation($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?location";

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's logging and returns the response from Amazon
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

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's notification and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketNotification($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?notification";

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's tagging and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketTagging($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?tagging";

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting the versions of a bucket's objects
	 * and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketVersions($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?versions";

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's request payment configuration
	 * and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketRequestPayment($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?requestPayment";

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's versioning state and returns the response from Amazon
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

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for getting a bucket's website and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucketWebsite($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?website";

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}

	/**
	 * Creates the request for listing a bucket's multipart uploads
	 * and returns the response from Amazon
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function listMultipartUploads($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?uploads";

		// Send the request and process the response
		$response_body = $this->commonGetOperations($url);

		return $response_body;
	}
}
