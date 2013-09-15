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
	 * Creates the get request and returns the response
	 *
	 * @param   string  $bucket  The bucket name
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getBucket($bucket)
	{
		return $this->commonGetOperations($bucket);
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
		return $this->commonGetOperations($bucket, "acl");
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
		return $this->commonGetOperations($bucket, "cors");
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
		return $this->commonGetOperations($bucket, "lifecycle");
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
		return $this->commonGetOperations($bucket, "logging");
	}
}
