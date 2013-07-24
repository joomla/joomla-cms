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
 * Defines the DELETE operations on buckets
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3OperationsBucketsDelete extends JAmazons3OperationsBuckets
{
	/**
	 * Deletes the bucket named in the URI
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function deleteBucket($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/";

		// Send the request and process the response
		$response_body = $this->commonDeleteOperations($url);

		return $response_body;
	}

	/**
	 * Deletes the cors configuration information set for the bucket.
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function deleteBucketCors($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?cors";

		// Send the request and process the response
		$response_body = $this->commonDeleteOperations($url);

		return $response_body;
	}

	/**
	 * Deletes the lifecycle configuration from the specified bucket
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function deleteBucketLifecycle($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?lifecycle";

		// Send the request and process the response
		$response_body = $this->commonDeleteOperations($url);

		return $response_body;
	}

	/**
	 * This implementation of the DELETE operation uses the policy subresource
	 * to delete the policy on a specified bucket.
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function deleteBucketPolicy($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?policy";

		// Send the request and process the response
		$response_body = $this->commonDeleteOperations($url);

		return $response_body;
	}

	/**
	 * This implementation of the DELETE operation uses the tagging
	 * subresource to remove a tag set from the specified bucket.
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function deleteBucketTagging($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?tagging";

		// Send the request and process the response
		$response_body = $this->commonDeleteOperations($url);

		return $response_body;
	}

	/**
	 * This operation removes the website configuration for a bucket.
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function deleteBucketWebsite($bucket)
	{
		$url = "https://" . $bucket . "." . $this->options->get("api.url") . "/?website";

		// Send the request and process the response
		$response_body = $this->commonDeleteOperations($url);

		return $response_body;
	}
}
