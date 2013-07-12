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
 * Common Request Headers used by the Amazon S3 API
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3HeadersCommonRequestHeaders
{
	/**
	 * Method to create an authorization request header.
	 * Provides the information required for request authentication.
	 *
	 * @param   string   $value     The value of the request header.
	 *
	 * @return  JRequestHeader	An authorization request header
	 *
	 * @since   ??.?
	 */
	public static function getAuthorization($value)
	{
		return new JRequestHeader("Authorization", $value);
	}

	/**
	 * Method to create a content-length request header.
	 * Provides the length of the message (without the headers) according to RFC 2616.
	 *
	 * @param   string   $value     The value of the request header.
	 * @param   boolean  $required  Whether the header is required or not for the request.
	 *
	 * @return  JRequestHeader	A content-length request header
	 *
	 * @since   ??.?
	 */
	public static function getContentLength($value, $required = false)
	{
		return new JRequestHeader("Content-Length", $value, $required);
	}

	/**
	 * Method to create a content-type request header.
	 * Example: text/plain.
	 *
	 * @param   string   $value     The value of the request header.
	 * @param   boolean  $required  Whether the header is required or not for the request.
	 *
	 * @return  JRequestHeader	A content-type request header
	 *
	 * @since   ??.?
	 */
	public static function getContentType($value, $required = false)
	{
		return new JRequestHeader("Content-Type", $value, $required);
	}

	/**
	 * Method to create a content-MD5 request header.
	 * Provides the base64 encoded 128-bit MD5 digest of the message (without the headers) according to RFC 1864.
	 *
	 * @param   string   $value     The value of the request header.
	 * @param   boolean  $required  Whether the header is required or not for the request.
	 *
	 * @return  JRequestHeader	A content-MD5 request header
	 *
	 * @since   ??.?
	 */
	public static function getContentMD5($value, $required = false)
	{
		return new JRequestHeader("Content-MD5", $value, $required);
	}

	/**
	 * Method to create a date request header.
	 * Provides the current date and time according to the requester.
	 *
	 * @param   string   $value     The value of the request header.
	 * @param   boolean  $required  Whether the header is required or not for the request.
	 *
	 * @return  JRequestHeader	A date request header
	 *
	 * @since   ??.?
	 */
	public static function getDate($value, $required = false)
	{
		return new JRequestHeader("Date", $value, $required);
	}

	/**
	 * Method to create an expect request header.
	 * When your application uses 100-continue, it does not send the request body until it receives an acknowledgment.
	 *
	 * @param   boolean  $required  Whether the header is required or not for the request.
	 *
	 * @return  JRequestHeader	An expect request header
	 *
	 * @since   ??.?
	 */
	public static function getExpect($required = false)
	{
		return new JRequestHeader("Expect", "100-continue", $required);
	}

	/**
	 * Method to create a host request header.
	 *
	 * For path-style requests, the value is s3.amazonaws.com.
	 * For virtual-style requests, the value is BucketName.s3.amazonaws.com.
	 *
	 * This header is required for HTTP 1.1 (most toolkits add this header automatically);
	 * optional for HTTP/1.0 requests.
	 *
	 * @param   string   $value     The value of the request header.
	 * @param   boolean  $required  Whether the header is required or not for the request.
	 *
	 * @return  JRequestHeader	A host request header
	 *
	 * @since   ??.?
	 */
	public static function getHost($value, $required = false)
	{
		return new JRequestHeader("Host", $value, $required);
	}

	/**
	 * Method to create a x-amz-date request header.
	 * When you specify the Authorization header, you must specify either the x-amz-date or the Date header.
	 * If you specify both, the value specified for the x-amz-date header takes precedence.
	 *
	 * @param   string   $value     The value of the request header.
	 * @param   boolean  $required  Whether the header is required or not for the request.
	 *
	 * @return  JRequestHeader	A x-amz-date request header
	 *
	 * @since   ??.?
	 */
	public static function getXAmzDate($value, $required = false)
	{
		return new JRequestHeader("x-amz-date", $value, $required);
	}

	/**
	 * Method to create a x-amz-security-token request header.
	 * This header can be used in the following scenarios:
	 *  1. Provide security tokens for Amazon DevPay operations
	 *  2. Provide security token when using temporary security credentials
	 *
	 * This header is required for requests that use Amazon DevPay and requests
	 * that are signed using temporary security credentials.
	 *
	 * @param   string   $value     The value of the request header.
	 * @param   boolean  $required  Whether the header is required or not for the request.
	 *
	 * @return  JRequestHeader	A x-amz-security-token request header
	 *
	 * @since   ??.?
	 */
	public static function getXAmzSecurityToken($value, $required = false)
	{
		return new JRequestHeader("x-amz-security-token", $value, $required);
	}
}
