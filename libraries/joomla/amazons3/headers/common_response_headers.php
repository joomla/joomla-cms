<?php
/**
 * @package     Joomla.Platform
 * @subpackage  AmazonS3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Common Response Headers used by the Amazon S3 API
 *
 * @package     Joomla.Platform
 * @subpackage  AmazonS3
 * @since       11.3
 */
class JCommonResponseHeaders
{
	/**
	 * Method to create a content-length response header.
	 * Provides the length in bytes of the body in the response.
	 *
	 * @param   string   $value	The value of the response header.
	 *
	 * @return  JRequestHeader	A content-length response header
	 *
	 * @since   ??.?
	 */
	public function getContentLength(String $value)
	{
		return new JResponseHeader("Content-Length", $value);
	}

	/**
	 * Method to create a connection response header.
	 * Specifies whether the connection to the server is open or closed.
	 *
	 * @param   string   $value	The value of the response header.
	 *
	 * @return  JRequestHeader	A connection response header
	 *
	 * @since   ??.?
	 */
	public function getConnection(String $value)
	{
		return new JResponseHeader("Connection", $value, array("open", "close"));
	}

	/**
	 * Method to create a date response header.
	 * Provides the date and time Amazon S3 responded.
	 * Example: Wed, 01 Mar 2009 12:00:00 GMT.
	 *
	 * @param   string   $value	The value of the response header.
	 *
	 * @return  JRequestHeader	A date response header
	 *
	 * @since   ??.?
	 */
	public function getDate(String $value)
	{
		return new JResponseHeader("Date", $value);
	}

	/**
	 * Method to create an ETag response header.
	 * The entity tag is a hash of the object.
	 *
	 * @param   string   $value	The value of the response header.
	 *
	 * @return  JRequestHeader	An ETag response header
	 *
	 * @since   ??.?
	 */
	public function getETag(String $value)
	{
		return new JResponseHeader("ETag", $value);
	}

	/**
	 * Method to create a server response header.
	 * Provides the name of the server that created the response.
	 *
	 * @param   string   $value	The value of the response header.
	 *
	 * @return  JRequestHeader	A server response header
	 *
	 * @since   ??.?
	 */
	public function getServer(String $value = "AmazonS3")
	{
		return new JResponseHeader("Server", $value);
	}

	/**
	 * Method to create a x-amz-delete-marker response header.
	 * Specifies whether the object returned was (true) or was not (false) a Delete Marker.
	 *
	 * @param   boolean   $value	The value of the response header.
	 *
	 * @return  JRequestHeader	A x-amz-delete-marker response header
	 *
	 * @since   ??.?
	 */
	public function getXAmzDeleteMarker(boolean $value = false)
	{
		return new JResponseHeader("x-amz-delete-marker", $value, array(true, false));
	}

	/**
	 * Method to create a x-amz-id-2 response header.
	 * Provides a special token that helps AWS troubleshoot problems.
	 *
	 * @param   string   $value	The value of the response header.
	 *
	 * @return  JRequestHeader	A x-amz-id-2 response header
	 *
	 * @since   ??.?
	 */
	public function getXAmzId2(String $value)
	{
		return new JResponseHeader("x-amz-id-2", $value);
	}

	/**
	 * Method to create a x-amz-request-id response header.
	 * A value created by Amazon S3 that uniquely identifies the request. In the unlikely event that
	 * you have problems with Amazon S3, AWS can use this value to troubleshoot the problem.
	 *
	 * @param   string   $value	The value of the response header.
	 *
	 * @return  JRequestHeader	A x-amz-request-idresponse header
	 *
	 * @since   ??.?
	 */
	public function getXAmzRequestId(String $value)
	{
		return new JResponseHeader("x-amz-request-id", $value);
	}

	/**
	 * Method to create a x-amz-version-id response header.
	 * Provides the version of the object.
	 *
	 * @param   string   $value	The value of the response header.
	 *
	 * @return  JRequestHeader	A x-amz-version-idresponse header
	 *
	 * @since   ??.?
	 */
	public function getXAmzVersionId(String $value = NULL)
	{
		return new JResponseHeader("x-amz-version-id", $value);
	}
}
